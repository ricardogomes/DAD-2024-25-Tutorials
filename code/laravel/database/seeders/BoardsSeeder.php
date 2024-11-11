<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("Create game boards");
        DB::table('boards')->insert([
            [
                'board_cols' => 3,
                'board_rows' => 4
            ],
            [
                'board_cols' => 4,
                'board_rows' => 4
            ],
            [
                'board_cols' => 6,
                'board_rows' => 6
            ]
        ]);
    }
}
