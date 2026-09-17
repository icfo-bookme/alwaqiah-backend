<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\ContactInquiryController;
use Modules\Frontend\Http\Controllers\FaqController;
use Modules\Frontend\Http\Controllers\FlightController;
use Modules\Frontend\Http\Controllers\IconController;
use Modules\Frontend\Http\Controllers\PackageController;
use Modules\Frontend\Http\Controllers\SliderImageController;
use Modules\Frontend\Http\Controllers\YoutubeVideoController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('flights', FlightController::class)->except(['create', 'edit'])->names('flights');
    Route::get('/dataTable/flights', [FlightController::class, 'dataTable'])->name('flights.dataTable');
    Route::post('flights/reorder', [FlightController::class, 'reorder'])->name('flights.reorder');

    Route::resource('youtube-videos', YoutubeVideoController::class)->except(['create', 'edit'])->names('youtube-videos');
    Route::get('/dataTable/youtube-videos', [YoutubeVideoController::class, 'dataTable'])->name('youtube-videos.dataTable');
    Route::post('youtube-videos/reorder', [YoutubeVideoController::class, 'reorder'])->name('youtube-videos.reorder');

    Route::resource('slider-images', SliderImageController::class)->except(['create', 'edit'])->names('slider-images');
    Route::get('/dataTable/slider-images', [SliderImageController::class, 'dataTable'])->name('slider-images.dataTable');
    Route::post('slider-images/reorder', [SliderImageController::class, 'reorder'])->name('slider-images.reorder');

    Route::resource('packages', PackageController::class)->except(['create', 'edit'])->names('packages');
    Route::get('/dataTable/packages', [PackageController::class, 'dataTable'])->name('packages.dataTable');
    Route::post('packages/reorder', [PackageController::class, 'reorder'])->name('packages.reorder');
    Route::get('packages/{id}/features', [PackageController::class, 'features'])->name('packages.features');
    Route::post('packages/{id}/features', [PackageController::class, 'saveFeatures'])->name('packages.saveFeatures');

    Route::resource('icons', IconController::class)->except(['create', 'edit'])->names('icons');
    Route::get('/dataTable/icons', [IconController::class, 'dataTable'])->name('icons.dataTable');
    Route::post('icons/reorder', [IconController::class, 'reorder'])->name('icons.reorder');
    Route::get('icons/options/list', [IconController::class, 'options'])->name('icons.options');

    Route::resource('faqs', FaqController::class)->except(['create', 'edit'])->names('faqs');
    Route::get('/dataTable/faqs', [FaqController::class, 'dataTable'])->name('faqs.dataTable');
    Route::post('faqs/reorder', [FaqController::class, 'reorder'])->name('faqs.reorder');

    Route::resource('contact-inquiries', ContactInquiryController::class)->except(['create', 'edit'])->names('contact-inquiries');
    Route::get('/dataTable/contact-inquiries', [ContactInquiryController::class, 'dataTable'])->name('contact-inquiries.dataTable');
});
