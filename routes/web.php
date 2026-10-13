<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['status' => 'ok'];
});

require __DIR__.'/auth.php';
