<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Display the home page with paginated posts.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $posts = Post::query()->showablePost();
        return Inertia::render('welcome', [
            'posts' => PostResource::collection($posts->paginate(config('app.per_page'))),
        ]);
    }

    public function search()
    {
        $q = request()->validate(['q' => 'nullable|string'])['q'] ?? '';
        $posts = Post::query()->showablePost()->search($q)->paginate(config('app.per_page'));

        return Inertia::render('welcome', [
            'posts' => PostResource::collection($posts),
        ]);
    }
}
