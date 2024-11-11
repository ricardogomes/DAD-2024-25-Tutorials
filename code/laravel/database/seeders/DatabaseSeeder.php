<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    // public static $seedType = "small";
    //public static $seedType = "full";
    //public static $seedLanguage = "pt_PT";
    public static $seedLanguage = "en_US";

    public function run(): void
    {
        $this->command->info("-----------------------------------------------");
        $this->command->info("START of database seeder");
        $this->command->info("-----------------------------------------------");

        //DatabaseSeeder::$seedType = $this->command->choice('What is the size of seed data (choose "full" for publishing)?', ['small', 'full'], 0);

        DatabaseSeeder::$seedLanguage = $this->command->choice('What is the language for users\' names?', ['en_US', 'pt_PT'], 0);


        DB::statement("SET foreign_key_checks=0");

        DB::table('users')->delete();
        DB::table('boards')->delete();
        DB::table('games')->delete();
        DB::table('transactions')->delete();
        DB::table('multiplayer_games_played')->delete();

        DB::statement('ALTER TABLE users AUTO_INCREMENT = 0');
        DB::statement('ALTER TABLE boards AUTO_INCREMENT = 0');
        DB::statement('ALTER TABLE games AUTO_INCREMENT = 0');
        DB::statement('ALTER TABLE transactions AUTO_INCREMENT = 0');
        DB::statement('ALTER TABLE multiplayer_games_played AUTO_INCREMENT = 0');

        DB::statement("SET foreign_key_checks=1");

        $this->command->info("-----------------------------------------------");

        // No permissions to change global setting. Change the session setting only
        //DB::statement("SET @@global.time_zone = '+00:00'");
        DB::statement("SET time_zone = '+00:00'");

        $this->call(BoardsSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(InitialTransactionsSeeder::class);
        $this->call(GamesSeeder::class);
        $this->call(GamesTransactionsSeeder::class);


        $this->command->info("-----------------------------------------------");
        $this->command->info("END of database seeder");
        $this->command->info("-----------------------------------------------");
    }
}
