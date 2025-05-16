<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory, HasSlug;
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'content',
        'feature_image',
        'is_published',
        'status'
    ];

    protected $casts = [
        'feature_image' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($post) {
            $post->user_id = $post->user_id ?? Auth::id();
        });
    }

    public function scopeCurrentUserPost(Builder $query)
    {
        return $query->where('user_id', Auth::id());
    }

    public function scopeShowablePost(Builder $query)
    {
        return $query->where('status', 'approved')->where('is_published', true)->latest();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setFeatureImageAttribute($value): void
    {
        $this->attributes['feature_image'] = Str::replaceFirst('feature_images/', '', $value);
    }

    public function getFeatureImageAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        return config('app.feature_image_dir') . '/' . $value;
    }

    public function approvedComments()
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->where('status', 'Approved')
            ->whereNull('parent_comment_id');
    }   
}
