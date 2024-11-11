<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GamesSeeder extends Seeder
{
    private $ratioSingleplayerToMultiplayer = 20;


    private function calculateRandomSeconds($filteredCollection)
    {
        $totalPlayers = $filteredCollection->count() + 1;
        return (12 * 60 * 60) / $totalPlayers + rand(0, 2000);
    }

    public function run(): void
    {
        $this->command->info("Games seeder - Start");
        $currentTime = new Carbon();

        $faker = \Faker\Factory::create(DatabaseSeeder::$seedLanguage);

        $start = DB::table('users')->where('type', 'P')->min('created_at');

        $allPlayers = DB::table('users')->where('type', 'P')->get();
        $sortedPlayers = $allPlayers->sortBy('created_at')->values();

        $d = new \Carbon\Carbon($start);
        $d = $d->addDay();
        $now = \Carbon\Carbon::now();
        $this->command->info("Starting to create games");

        $games = [];

        $i = 0;

        $filteredPlayers = null;
        $filteredPlayersIds = null;
        $nextCreatedAt = null;

        while ($d->lte($now)) {
            $i++;
            if ( ($filteredPlayers === null) || ($nextCreatedAt === null) ||
                ($d->gte($nextCreatedAt))) {
                    $filteredPlayers = $allPlayers->filter(function ($value) use ($d) {
                        return $d->gt($value->created_at);
                    });
                    $nextCreatedAtPlayer = $sortedPlayers->first(function ($value) use ($d) {
                        return $d->lte($value->created_at);
                    });
                    $nextCreatedAt = $nextCreatedAtPlayer ? $nextCreatedAtPlayer->created_at : new \Carbon\Carbon('9999-12-31');
                    $filteredPlayersIds = $filteredPlayers->pluck('id')->toArray();
                }
            $userIdForCurrentGame = $filteredPlayersIds[array_rand($filteredPlayersIds)];
            $userIdForMultiplayer = rand(1,$this->ratioSingleplayerToMultiplayer) == 1 ? $filteredPlayersIds[array_rand($filteredPlayersIds)] : null;
            if ($userIdForMultiplayer == $userIdForCurrentGame) {
                $userIdForMultiplayer = $filteredPlayersIds[rand(0,count($filteredPlayersIds)-1)];
                if ($userIdForMultiplayer == $userIdForCurrentGame) {
                    $userIdForMultiplayer = null;
                }
            }
            $games[] = $this->newGame($currentTime, $d, $userIdForCurrentGame, $userIdForMultiplayer);

            if ($i >= 100) {
                DB::table('games')->insert($games);
                $this->command->info("Saved 100 games at date " . $d->format('Y-m-d H:i:s'));
                $i = 0;
                $games = [];
            }
            $deltaSegundos = $this->calculateRandomSeconds($filteredPlayers);
            $d->addSeconds($deltaSegundos);
        }
        if (!empty($games)) {
            DB::table('games')->insert($games);
        }
        $this->fillAllMultiplayerGamesPlayed();
        $this->command->info("Setting winner to null on ALL uneded games or single player games");
        // DB::table('games')->whereNot('status', 'E')->update(['winner_user_id' => null]);
        // $this->command->info("Clearing winner_user_id for non ended multiplayer games");
        DB::update('update games set winner_user_id = null where type = "S" OR (type = "M" and status <> "E")');
        $this->command->info("Games seeder - End");
    }

    private function fillAllMultiplayerGamesPlayed()
    {
        $multiplayerGames = DB::table('games')
            ->where('type', 'M')
            ->select('id', 'status', 'board_id', 'created_user_id', 'winner_user_id')
            ->get();
        $multiplayerGamesPlayed = [];
        $updateGamesWinner = [];
        $i = 0;
        foreach ($multiplayerGames as $game) {
            $i++;
            $winnerId = null;
            $total1 = 0;
            $total1 = 2;
            if ($game->status != 'E') {
                $multiplayerGamesPlayed[] = [
                    'user_id' => $game->created_user_id,
                    'game_id' => $game->id,
                    'player_won' => null,
                    'pairs_discovered' => null
                ];
                $multiplayerGamesPlayed[] = [
                    'user_id' => $game->winner_user_id,
                    'game_id' => $game->id,
                    'player_won' => null,
                    'pairs_discovered' => null
                ];
                $updateGamesWinner[] = [
                    'id' => $game->id,
                    'winner_user_id' => null
                ];
            } else {
                $this->getWinnerAndTotal($game->board_id, $game->created_user_id, $game->winner_user_id, $winnerId, $total1, $total2);
                $multiplayerGamesPlayed[] = [
                    'user_id' => $game->created_user_id,
                    'game_id' => $game->id,
                    'player_won' => $winnerId == $game->created_user_id,
                    'pairs_discovered' => $total1
                ];
                $multiplayerGamesPlayed[] = [
                    'user_id' => $game->winner_user_id,
                    'game_id' => $game->id,
                    'player_won' => $winnerId == $game->winner_user_id,
                    'pairs_discovered' => $total2
                ];
                $updateGamesWinner[] = [
                    'id' => $game->id,
                    'winner_user_id' => $winnerId
                ];
            }
            if ($i >= 100) {
                DB::table('multiplayer_games_played')->insert($multiplayerGamesPlayed);
                foreach ($updateGamesWinner as $updateGameWinner) {
                    DB::table('games')->where('id', $updateGameWinner['id'])->update($updateGameWinner);
                }
                $multiplayerGamesPlayed = [];
                $updateGamesWinner = [];
                $i = 0;
                $this->command->info("Saved 100 multiplayer games played");
            }
        }
        if (!empty($multiplayerGamesPlayed)) {
            DB::table('multiplayer_games_played')->insert($multiplayerGamesPlayed);
            $this->command->info("Saved last multiplayer games played");
        }
        if (!empty($updateGamesWinner)) {
            foreach($updateGamesWinner as $updateGameWinner) {
                DB::table('games')->where('id', $updateGameWinner['id'])->update($updateGameWinner);
            }
            $this->command->info("Saved last multiplayer games played");
        }
    }

    private function getWinnerAndTotal($board, $user1, $user2, &$winnerId, &$total1, &$total2 )
    {
        $totalBoardPairs = [
            1 => 6,
            2 => 8,
            3 => 16
        ];
        $total1 = rand(1, $totalBoardPairs[$board]);
        $total2 = $totalBoardPairs[$board] - $total1;
        if ($total1 == $total2) {
            $winnerId = rand(1, 2) == 1 ? $user1 : $user2;
        } else {
            $winnerId = $total1 > $total2 ? $user1 : $user2;
        }
    }

    private function newGame($currentTime, $d, $userId1, $userId2)
    {
        $board = rand(1,3);
        $totalCentSeconds = $board * 3000 + rand(0, $board*10000);
        $status = rand(1, 50) == 20 ? 'I' : 'E';
        $began_at = $d->copy()->addSeconds(rand(2, 500));
        $ended_at = null;
        if ($began_at->gt($currentTime)) {
            $began_at = $currentTime->copy();
            $status = 'PL';
            $totalCentSeconds = null;
        } else {
            $a = $totalCentSeconds / 100;
            //$ended_at = $began_at->copy()->addSeconds(intval($totalCentSeconds / 100) + 1);
            $ended_at = $began_at->copy()->addSeconds($totalCentSeconds / 100);
            if ($ended_at->gt($currentTime)) {
                $ended_at = null;
                $status = 'PL';
                $totalCentSeconds = null;
            }
        }
        // Original
        //$winnerId = $status == 'E' ? ($userId2 ? (rand(1, 2) == 1 ? $userId1 : $userId2) : $userId1) : null;

        // Tempoorarly, winnerId is always the second player.
        // This will alows us to use that information to fill the "multiplayer_games_played"
        // And then, at the end we can randomly change the winner.
        $winnerId = $userId2;
        return [
            'created_user_id' => $userId1,
            'winner_user_id' => $winnerId,
            'type' => $userId2 ? 'M' : 'S',
            'status' => $status,
            'began_at' => $began_at,
            'ended_at' => $status == 'E' ? $ended_at : null,
            'total_time' => $status == 'E' ? $totalCentSeconds / 100 : null,
            'board_id' => $board,
            'created_at' => $d,
            'updated_at' => $status == 'E' ? $ended_at : $d,
        ];
    }
}
