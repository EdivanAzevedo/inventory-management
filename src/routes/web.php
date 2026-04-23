<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', fn () => view('auth.login'))->name('login');

Route::redirect('/', '/products');

Route::get('/products',      fn ()           => view('products.index'));
Route::get('/products/{id}', fn (string $id) => view('products.show', ['id' => $id]));
Route::get('/stock',         fn ()           => view('stock.index'));
