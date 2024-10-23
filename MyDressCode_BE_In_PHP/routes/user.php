<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\Authenticate; // Middleware for JWT authentication

Route::post('/signupUser', [UserController::class, 'signupUser'])->name('signup.user');
Route::post('/loginUser', [UserController::class, 'loginUser'])->name('login.user');
Route::put('/{user_id}/updateRole', [UserController::class, 'updateUserRole'])->name('update.user.role');
Route::get('/getUsers', [UserController::class, 'getUsers'])->name('get.users');
Route::get('/current-user', [Authenticate::class, 'handle'], [UserController::class, 'getCurrentUser'])->name('get.current.user');
Route::post('/{user_id}/toggleWishlistItem', [Authenticate::class, 'handle'], [UserController::class, 'toggleWishlistItem'])->name('toggle.wishlist.item');
Route::get('/{user_id}/getUserWishlist', [Authenticate::class, 'handle'], [UserController::class, 'getUserWishlist'])->name('get.user.wishlist');
Route::post('/{user_id}/toggleCartItem', [Authenticate::class, 'handle'], [UserController::class, 'toggleCartItem'])->name('toggle.cart.item');
Route::get('/{user_id}/getUserCart', [Authenticate::class, 'handle'], [UserController::class, 'getUserCart'])->name('get.user.cart');

