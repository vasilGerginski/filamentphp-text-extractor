<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FilamentPHP Text Extractor Demo Routes
|--------------------------------------------------------------------------
|
| Add these routes to your routes/web.php file to run the translation demos
|
*/

// Manual Translation Demo (Traditional __() approach)
Route::get('/translation-demo', function () {
    // Handle locale switching from URL parameter
    if (request('lang') && in_array(request('lang'), ['en', 'es', 'fr', 'de'])) {
        app()->setLocale(request('lang'));
    }
    
    $posts = App\Models\BlogPost::all();
    return view('translation-demo', compact('posts'));
});

// Translatable Cast Demo (Automatic {{ $property }} approach)
Route::get('/cast-demo', function () {
    // Handle locale switching from URL parameter
    if (request('lang') && in_array(request('lang'), ['en', 'es', 'fr', 'de'])) {
        app()->setLocale(request('lang'));
    }
    
    $posts = App\Models\BlogPost::take(3)->get();
    return view('cast-demo', compact('posts'));
});

// JSON Translation Demo (JsonTranslatable cast for UI components)
Route::get('/json-demo', function () {
    // Handle locale switching from URL parameter
    if (request('lang') && in_array(request('lang'), ['en', 'es', 'fr', 'de'])) {
        app()->setLocale(request('lang'));
    }
    
    return view('json-demo');
});