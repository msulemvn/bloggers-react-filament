<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'body',
        'commentable_type',
        'commentable_id',
        'parent_comment_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    protected static function booted(): void
    {
        static::creating(fn(self $comment) => $comment->user_id = Auth::id());
    }

    public function setCommentableTypeAttribute($value): void
    {
        $this->attributes['commentable_type'] = class_exists($value) ? $value : "App\\Models\\" . Str::studly(str_replace('/', '\\', trim($value)));
    }

    public function getCommentableTypeAttribute($value): string
    {
        return class_basename($value);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_comment_id')->with(['comments', 'user']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWithRecursiveComments(Builder $query): void
    {
        $query->with(['user', 'comments' => fn($subQuery) => $subQuery->withRecursiveComments()]);
    }

    public function scopeWithoutParent(Builder $query): void
    {
        $query->whereNull('parent_comment_id');
    }

    public function scopeWithApproved(Builder $query): Builder
    {
        return $query->where('status', 'Approved');
    }
}
