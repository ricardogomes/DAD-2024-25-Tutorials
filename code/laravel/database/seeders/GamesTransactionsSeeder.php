<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class GamesTransactionsSeeder extends Seeder
{
    private $allPlayersBalances;
    private $sizeOfSaveBlock = 100;
    private $faker = null;
    public function run(): void
    {
        $this->command->info("Create game related Transactions");
        $this->faker = \Faker\Factory::create(DatabaseSeeder::$seedLanguage);

        $this->allPlayersBalances = DB::table('users')
            ->select('id', 'email', 'brain_coins_balance')
            ->where('type', 'P')
            ->get();

        // $sqlGamesAndPlayers = 'select g.id, g.created_user_id, g.winner_user_id, g.type, g.began_at, g.ended_at, m.user_id, m.player_won
        //                         from games as g
        //                         left join multiplayer_games_played as m
        //                         on g.id = m.game_id
        //                         where g.status = "E" ';

        DB::table('games')
            ->leftJoin('multiplayer_games_played', 'games.id', '=', 'multiplayer_games_played.game_id')
            ->select('games.id', 'games.created_user_id', 'games.winner_user_id', 'games.type', 'games.began_at', 'games.ended_at',
                    'multiplayer_games_played.user_id',
                    'multiplayer_games_played.player_won')
            ->orderBy('games.ended_at', 'asc')
            ->where('games.status', 'E')
            ->chunk(
                $this->sizeOfSaveBlock,
                function (Collection $games) {
                    $blockOfTransactions = [];
                    foreach ($games as $game) {
                        $this->addTransactionsForGamesRow($blockOfTransactions, $game->id, $game->created_user_id, $game->type, $game->began_at, $game->ended_at, $game->user_id, $game->player_won);
                    }
                    $this->saveTransactionBlock($blockOfTransactions);
            });
        $this->updateAllPlayerBalances();
    }

    private function addTransactionsForGamesRow(&$arrayWithTransaction, $gameId, $created_user_id, $type, $began_at, $ended_at, $mp_user_id, $mp_player_won) : void
    {
        $userIdForTransaction = $type == 'S' ? $created_user_id : $mp_user_id;
        $userForTransaction = $this->allPlayersBalances->firstWhere('id', $userIdForTransaction);

        $costToPlay = $type == 'S' ? 1 : 5;
        // Since all multiplayer games on the seed only have 2 players, the winner will always gain 7 coins
        // total coins of players ( 2 * 5 ) minus the commision (3)
        $totalForWinner = $type == 'S' ? 0 : ($mp_player_won ? 7 : 0) ;

        if ($userForTransaction->brain_coins_balance < $costToPlay) {
            $purchaseEuro = rand(1, 20);
            $totalCoins = $purchaseEuro * 10;
            $userForTransaction->brain_coins_balance += $totalCoins;
            $email = $userForTransaction->email;
            $type = 'PAYPAL';
            $ref= $email;
            $this->getRandomPayment($email, $type, $ref);
            $d = new \Carbon\Carbon($began_at);
            $d = $d->subSeconds(rand(30, 1000));
            $arrayWithTransaction[] = [
                'transaction_datetime' => $d,
                'user_id' => $userIdForTransaction,
                'game_id' => null,
                'type' => 'P',
                'euros' => $purchaseEuro,
                'brain_coins' => $totalCoins,
                'payment_type' => $type,
                'payment_reference' => $ref,
            ];
        }
        $userForTransaction->brain_coins_balance -= $costToPlay;
        $arrayWithTransaction[] = [
            'transaction_datetime' => $ended_at,
            'user_id' => $userIdForTransaction,
            'game_id' => $gameId,
            'type' => 'I',
            'euros' => null,
            'brain_coins' => -1 * $costToPlay,
            'payment_type' => null,
            'payment_reference' => null,
        ];

        if ($totalForWinner > 0) {
            $userForTransaction->brain_coins_balance += $totalForWinner;
            $arrayWithTransaction[] = [
                'transaction_datetime' => $ended_at,
                'user_id' => $userIdForTransaction,
                'game_id' => $gameId,
                'type' => 'I',
                'euros' => null,
                'brain_coins' => $totalForWinner,
                'payment_type' => null,
                'payment_reference' => null,
            ];
        }
    }

    private function saveTransactionBlock($transactionBlock) : void
    {
        $size = count($transactionBlock);
        DB::table('transactions')->insert($transactionBlock);
        $this->command->info("Saved $size game transactions");
    }

    private function getRandomPayment($email, &$type, &$ref): void
    {
        $type = $this->faker->randomElement(['MBWAY', 'PAYPAL', 'IBAN', 'MB', 'VISA']);
        switch ($type) {
            case 'MBWAY':
                $ref = rand(910000000, 999999999);
                break;
            case 'PAYPAL':
                $ref = $email;
                break;
            case 'IBAN':
                $ref = 'PT' . rand(5000000000000, 9999999999999) . rand(1000000000, 9999999999);
                break;
            case 'MB':
                $ref = rand(10000, 99999) . '-' . rand(100000000, 999999999);
                break;
            case 'VISA':
                $ref = rand(40000000, 49999999) . rand(10000000, 99999999);
                break;
        }
    }

    private function updateAllPlayerBalances(): void
    {
        $this->command->info("Updating all players final Balance");
        DB::table('transactions')->groupBy('user_id')
            ->selectRaw('user_id, sum(brain_coins) as total')
            ->get()
            ->each(function ($item) {
                DB::table('users')->where('id', $item->user_id)->update(['brain_coins_balance' => $item->total]);
            });
        $this->command->info("Update all players final Balance - comleted");
    }
}
