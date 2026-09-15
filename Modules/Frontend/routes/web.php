<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\IconController;
use Modules\Frontend\Http\Controllers\PackageController;
use Modules\Frontend\Http\Controllers\SliderImageController;
use Modules\Frontend\Http\Controllers\YoutubeVideoController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('youtube-videos', YoutubeVideoController::class)->names('youtube-videos');
    Route::get('/dataTable/youtube-videos', [YoutubeVideoController::class, 'dataTable'])->name('youtube-videos.dataTable');
    Route::post('youtube-videos/reorder', [YoutubeVideoController::class, 'reorder'])->name('youtube-videos.reorder');

    Route::resource('slider-images', SliderImageController::class)->names('slider-images');
    Route::get('/dataTable/slider-images', [SliderImageController::class, 'dataTable'])->name('slider-images.dataTable');
    Route::post('slider-images/reorder', [SliderImageController::class, 'reorder'])->name('slider-images.reorder');

    Route::resource('packages', PackageController::class)->names('packages');
    Route::get('/dataTable/packages', [PackageController::class, 'dataTable'])->name('packages.dataTable');
    Route::post('packages/reorder', [PackageController::class, 'reorder'])->name('packages.reorder');
    Route::get('packages/{id}/features', [PackageController::class, 'features'])->name('packages.features');
    Route::post('packages/{id}/features', [PackageController::class, 'saveFeatures'])->name('packages.saveFeatures');

    Route::resource('icons', IconController::class)->names('icons');
    Route::get('/dataTable/icons', [IconController::class, 'dataTable'])->name('icons.dataTable');
    Route::post('icons/reorder', [IconController::class, 'reorder'])->name('icons.reorder');
    Route::get('icons/options/list', [IconController::class, 'options'])->name('icons.options');
});
