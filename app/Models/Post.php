<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class Post extends Model
{
    use HasFactory, Notifiable; // Add Notifiable trait here

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'status',
        'published_at',
        'featured_image_url',  // New field
        'video_url',  // New field
    ];

    // Ensure 'published_at' is treated as a date instance
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function meta()
    {
        return $this->hasMany(PostMeta::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
