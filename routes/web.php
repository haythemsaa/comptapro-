<?php

use Illuminate\Support\Facades\Route;

/**
 * Routes Web - ComptaPro
 */

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
});

// Charger les routes admin
require __DIR__.'/admin.php';

// Charger les routes Tunisia
require __DIR__.'/tunisia.php';

// Charger les routes Belgium
require __DIR__.'/belgium.php';

// Charger les routes automation
require __DIR__.'/automation.php';
