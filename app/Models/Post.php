<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

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
