<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Post;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    public function show(Post $post): Response
    {
        $post->load([
            'tags',
            'comments' => function ($query) {
                $query->withoutParent()->withApproved()->withRecursiveComments();
            },
            'author:id,name'
        ])->loadCount('approvedComments');

        if (!$post->is_published || $post->status !== 'approved') {
            abort(404);
        }

        return Inertia::render('posts/show', [
            'post' => PostResource::make($post),
        ]);
    }
}
