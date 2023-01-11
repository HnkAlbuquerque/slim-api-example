<?php
require_once __DIR__ . '/../app/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;

Capsule::schema()->create('users', function ($table) {
    $table->bigIncrements("id");
    $table->string("name");
    $table->string("username");
    $table->string("email");
    $table->string("password");
    $table->timestamps();
});