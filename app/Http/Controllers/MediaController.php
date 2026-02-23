<?php

namespace App\Http\Controllers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    public function show(Media $media)
{
    $user = Auth::user();
    
    if ($media->disk === 'public') {
        return response()->file($media->getPath());
    }

    if ($media->model->user_id === $user->id || $user->hasRole('admin')) {
        return response()->file($media->getPath());
    }

    abort(403, 'Unauthorized access to this file.');
}
}