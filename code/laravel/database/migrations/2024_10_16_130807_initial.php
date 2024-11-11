<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Administrator, Player
            $table->enum('type', ['A', 'P'])->default('P');

            // Nickname - must be unique
            $table->string('nickname', 20)->unique();

            // User access is blocked
            $table->boolean('blocked')->default(false);

            // User Photo/Avatar
            $table->string('photo_filename')->nullable();

            // Brain Coin Balance
            $table->integer('brain_coins_balance')->default(0);

            // custom data
            $table->json('custom')->nullable();

            // Users can be deleted with "soft deletes"
            $table->softDeletes();
        });

        Schema::create('boards', function (Blueprint $table) {
            $table->id();

            // Board size (columns x rows)
            $table->integer('board_cols');
            $table->integer('board_rows');

            // custom data
            $table->json('custom')->nullable();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();

            // User who created the game
            $table->unsignedBigInteger('created_user_id');
            $table->foreign('created_user_id')->references('id')->on('users');

            // User who won the game
            $table->unsignedBigInteger('winner_user_id')->nullable();
            $table->foreign('winner_user_id')->references('id')->on('users');

            // Type of the game
            // S - Single Player
            // M - Multiplayer
            $table->enum('type', ['S', 'M']);

            // Game status
            // PE - PEnding - Game is waiting for players
            // PL - PLaying - Game is in progress
            // E - Ended - Game is over
            // I - Interrupted - Game is interrupted (not finished; no winner)
            $table->enum('status', ['PE', 'PL', 'E', 'I']);

            // Moment when the game began (first click to discover the first tile)
            $table->dateTime('began_at')->nullable();
            // Moment when the game ended (last click that discovered the last tile)
            $table->dateTime('ended_at')->nullable();
            // Game total time (in seconds) = ended_at - began_at
            $table->decimal('total_time', 8, 2)->nullable();

            // Game board
            $table->unsignedBigInteger('board_id');
            $table->foreign('board_id')->references('id')->on('boards');

            // custom data
            $table->json('custom')->nullable();

            $table->timestamps();
        });

        Schema::create('multiplayer_games_played', function (Blueprint $table) {
            $table->id();

            // User who played the game
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // Game that was played
            $table->unsignedBigInteger('game_id');
            $table->foreign('game_id')->references('id')->on('games');

            // User has won the game (yes or no)?
            $table->boolean('player_won')->nullable();

            // Number of tile pairs discovered by user
            $table->integer('pairs_discovered')->nullable();

            // custom data
            $table->json('custom')->nullable();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Datetime when the transaction began (first click to discover the first tile)
            $table->dateTime('transaction_datetime');

            // User associated with the transaction
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // Game associated with the transaction (optional)
            $table->unsignedBigInteger('game_id')->nullable();
            $table->foreign('game_id')->references('id')->on('games');

            // Type of the transaction
            // B - Bonus,
            // P - Purchases,
            // I - Internal spending/earnings
            $table->enum('type', ['B', 'P', 'I']);

            // Amount of the transaction (real money in euros)
            $table->decimal('euros', 8, 2)->nullable();

            // Amount of the transaction (brain coins)
            // Positive -> increments the total amount of brain coins
            // Negative -> decrements the total amount of brain coins
            $table->integer('brain_coins');


            // Purchases will envolve a payment with a type and a reference
            // Other transactions will have null values
            // MBWAY -  Phone number with 9 digits
            // PAYPAL - eMail
            // IBAN - bank transfer (2 letters + 23 digits)
            // MB - Multibanco payment - entity number (5 digits) + Reference (9 digits))
            // VISA - Visa card number (16 digits)
            $table->enum('payment_type', ['MBWAY', 'PAYPAL', 'IBAN', 'MB', 'VISA'])->nullable();
            $table->string('payment_reference')->nullable();

            // custom data
            $table->json('custom')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
