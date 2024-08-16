<?php

use App\Http\Controllers\KeywordController;
use App\Http\Controllers\LocalKeywordController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/websites', [WebsiteController::class, 'index']);
Route::get('/website/{id}', [WebsiteController::class, 'getWebsite']);
Route::get('/website/{id}/process', [WebsiteController::class, 'processAssignment']);

Route::get('/local-keywords', [LocalKeywordController::class, 'index']);
Route::get('/local-keywords/{keyword}/{decision}', [LocalKeywordController::class, 'reviewKeyword']);

Route::get('/keyword/flag/{id}', [KeywordController::class, 'flagAssignment']);
