<?php
require_once __DIR__ . '/../app/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;

Capsule::schema()->create('histories', function ($table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('user_id');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->date('date');
    $table->string('name');
    $table->string('symbol');
    $table->string('open');
    $table->string('high');
    $table->string('low');
    $table->string('close');
    $table->timestamps();
});