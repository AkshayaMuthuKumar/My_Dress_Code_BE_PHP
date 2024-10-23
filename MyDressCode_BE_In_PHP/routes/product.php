<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;

// Middleware for handling file uploads can be applied if necessary

Route::post('/addCategory', [ProductController::class, 'addCategory'])->name('add.category');
Route::post('/addProduct', [ProductController::class, 'addProduct'])->name('add.product');

Route::get('/getCategory', [ProductController::class, 'getCategory'])->name('get.category');
Route::get('/getTopCategories', [ProductController::class, 'getTopCategories'])->name('get.top.categories');
Route::get('/getProductById/{productId}', [ProductController::class, 'getProductById'])->name('get.product.by.id');
Route::get('/getUniqueFilters', [ProductController::class, 'getUniqueFilters'])->name('get.unique.filters');
Route::get('/getCategoryIdBySubcategory', [ProductController::class, 'getCategoryIdBySubcategory'])->name('get.category.id.by.subcategory');
Route::get('/getProductsbySelectedCategory', [ProductController::class, 'getProductsbySelectedCategory'])->name('get.products.by.selected.category');
Route::get('/getReviewsByProductId/{productId}', [ProductController::class, 'getReviewsByProductId'])->name('get.reviews.by.product.id');
Route::post('/addReview', [ProductController::class, 'addReview'])->name('add.review');
Route::get('/getProduct', [ProductController::class, 'getProduct'])->name('get.product');
Route::get('/just-arrived', [ProductController::class, 'getJustArrivedProducts'])->name('just.arrived');

