<?php

use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('frontend.index');
})->name('home');
Route::get('/about-us', function () {
    return view('frontend.about');
})->name('about');

Route::get('/product/{slug}',function($slug){
    view('frontend.products.'.$slug);
})->name('product');

Route::get('/services', function () {
    return view('frontend.services');
})->name('services');

Route::get('/pricing', function () {
    return view('frontend.pricing');
})->name('pricing');

Route::get('/faqs', function () {
    return view('frontend.faqs');
})->name('faqs');

Route::get('/contact-us', function () {
    return view('frontend.contact');
})->name('contact');

Route::get('/terms-condition', function () {
    return view('frontend.terms-condition');
})->name('terms-condition');

Route::get('/privacy-policy', function () {
    return view('frontend.privacy-policy');
})->name('privacy-policy');

Route::get('/refund-policy', function () {
    return view('frontend.refund-policy');
})->name('refund-policy');

Route::get('/product/{slug}',[ProductController::class, 'index'])->name('product');
Route::get('/our-product',[ProductController::class, 'show'])->name('all-product');
Route::get('/save-product/{id}',[ProductController::class, 'save'])->name('save-product');
Route::post('/checkout',[ProductController::class, 'checkoutProceed'])->name('checkout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
