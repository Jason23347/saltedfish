<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //users
        Schema::create('users', function(Blueprint $table) {
            $table->increments('id');
            $table->char('account_number', 16)->nullable();
            //from Auth in laravel
            $table->char('name', 32);
            $table->string('email', 128);
            
            $table->char('password', 64);
            $table->text('info')->nullable();
            $table->char('info_hash', 64)->nullable();
            $table->string('account_header', 64)->nullable();
            $table->char('bbs_id', 32)->nullable();

            $table->timestamps();
            $table->rememberToken();
        });
        //goods
        Schema::create('goods', function(Blueprint $table) {
            $table->increments('id');
            $table->string('title', 128);
            $table->string('img', 256);
            $table->char('ststus', 32);
            $table->char('type', 32);
            $table->char('single_cost', 64);
            $table->integer('remain');
            $table->char('owner', 16);
            $table->dateTime('ttm');
            $table->integer('delivery_fee');
            $table->text('search_summary')->nullable();
            $table->text('info')->nullable();
            $table->text('comments')->nullable();
            $table->string('tags', 128)->nullable();
            $table->string('cl_lv_1', 128)->nullable();
            $table->string('cl_lv_2', 128)->nullable();
            $table->string('cl_lv_3', 128)->nullable();
            $table->integer('heat')->default(0);
            //what is the three below for?
            $table->integer('sv')->default(0);
            $table->integer('pv')->default(0);
            $table->integer('tu')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('users');
        Schema::drop('goods');
    }
}
