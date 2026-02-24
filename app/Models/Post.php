<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;          


class Post extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasTranslations;
    use LogsActivity; 

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
        'writer',
        'visibility',
        'status',
        'published_at',
    ];
    
    protected static $logAttributes = ['title', 'content', 'category_id', 'visibility', 'status'];
    protected static $logOnlyDirty = true; // only log changes
    protected static $submitEmptyLogs = false; // avoid logging if nothing changed
    protected static $logName = 'post'; // optional

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile();

        $this->addMediaCollection('gallery');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['title', 'category_id'])      
        ->logOnlyDirty()                          
        ->dontSubmitEmptyLogs();        
        }
}