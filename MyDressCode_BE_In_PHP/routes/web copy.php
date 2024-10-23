Route::get('/uploads/{filename}', function ($filename) {
    $path = storage_path('app/uploads/' . $filename);
    if (!File::exists($path)) {
        abort(404);
    }
    return response()->file($path);
});
