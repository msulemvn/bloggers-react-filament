<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'message' => $this->body,
            'from' => $this->whenLoaded('user', fn() => new UserResource($this->user)),
            'created_at' => $this->created_at->diffForHumans(),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
