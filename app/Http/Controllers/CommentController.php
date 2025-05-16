<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    public function store(CommentRequest $request)
    {
        $comment = Comment::create($request->validated());
        $comment->load('user');

        return response()->json([
            'data' => new CommentResource($comment)
        ], 201);
    }

    public function update(Request $request, Comment $comment)
    {
        $comment->update([
            'body' => $request->input('body'),
        ]);

        $comment->load('user');

        return response()->json([
            'data' => new CommentResource($comment)
        ]);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return response()->noContent();
    }
}
