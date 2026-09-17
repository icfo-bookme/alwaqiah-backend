<?php

// Frontend-facing (public) API endpoints for the Frontend module.

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\Api\ContactInquiryApiController;
use Modules\Frontend\Http\Controllers\Api\FaqApiController;
use Modules\Frontend\Http\Controllers\Api\FlightApiController;
use Modules\Frontend\Http\Controllers\Api\PackageApiController;
use Modules\Frontend\Http\Controllers\Api\SliderImageApiController;
use Modules\Frontend\Http\Controllers\Api\YoutubeVideoApiController;

// Public API — no auth middleware, used by the frontend to show sliders.
Route::prefix('sliders')->name('sliders.')->group(function () {
    Route::get('/', [SliderImageApiController::class, 'index'])->name('index');
});

// Public API — no auth middleware, used by the frontend to show packages.
Route::prefix('packages')->name('packages.')->group(function () {
    Route::get('/', [PackageApiController::class, 'index'])->name('index');
});

// Public API — no auth middleware, used by the frontend to show flights.
Route::prefix('flights')->name('flights.')->group(function () {
    Route::get('/', [FlightApiController::class, 'index'])->name('index');
});

// Public API — no auth middleware, used by the frontend to show FAQs.
Route::prefix('faqs')->name('faqs.')->group(function () {
    Route::get('/', [FaqApiController::class, 'index'])->name('index');
});

// Public API — no auth middleware, used by the frontend to show videos.
Route::prefix('youtube-videos')->name('youtube-videos.')->group(function () {
    Route::get('/', [YoutubeVideoApiController::class, 'index'])->name('index');
});

// Public API — no auth middleware, used by the frontend "contact us" form.
Route::prefix('contact-inquiries')->name('contact-inquiries.')->group(function () {
    Route::post('/', [ContactInquiryApiController::class, 'store'])->name('store');
});
