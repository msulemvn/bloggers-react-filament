<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "slug" => $this->slug,
            'author' => $this->whenLoaded('author', fn() => UserResource::make($this->author)),
            "content" => $this->content,
            "feature_image" => when($this->feature_image, fn() => asset(config('app.feature_image_prefix') . '/' . $this->feature_image)),
            "is_published" => $this->is_published,
            "status" => $this->status,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->when(isset($this->approved_comments_count), $this->approved_comments_count),
        ];
    }
}
