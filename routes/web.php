<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Modules\Frontend\Models\Flight;
use Modules\Frontend\Models\Icon;
use Modules\Frontend\Models\Package;
use Modules\Frontend\Models\SliderImage;
use Modules\Frontend\Models\YoutubeVideo;

Route::get('/dashboard', function () {
    return view('dashboard', [
        'stats' => [
            'packages' => Package::count(),
            'flights'  => Flight::count(),
            'sliders'  => SliderImage::count(),
            'videos'   => YoutubeVideo::count(),
            'icons'    => Icon::count(),
        ],
        'recentFlights'  => Flight::latest()->limit(5)->get(),
        'recentPackages' => Package::latest()->limit(5)->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
