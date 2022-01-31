<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * 
     * The up() method will be executed when we run the php artisan migrate command
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tel', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * 
     * the down() method will be executed when we run the php artisan migrate:rollback command.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tel')->change();
        });
    }
}
