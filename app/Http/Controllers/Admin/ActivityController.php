<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    // Return the Vue page
    public function index()
{
    $activities = Activity::latest()
        ->take(50)
        ->get()
        ->map(fn($activity) => [
            'id'          => $activity->id,
            'description' => $activity->description,
            'causer_name' => $activity->causer?->name ?? 'System',
            'created_at'  => $activity->created_at,
        ]);

    return inertia('Admin/Activity', [
        'activities' => $activities,
    ]);
}

    // Return JSON data for the activity table
    public function data()
    {
        $activities = Activity::latest()
            ->take(50)
            ->get()
            ->map(fn($activity) => [
                'id' => $activity->id,
                'description' => $activity->description,
                'causer_name' => $activity->causer?->name ?? 'System',
                'created_at' => $activity->created_at,
            ]);

        return response()->json($activities);
    }
}