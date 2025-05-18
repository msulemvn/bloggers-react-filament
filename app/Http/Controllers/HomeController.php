<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Validation\ValidationException;
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
            'auth' => request()->user(),
        ]);
    }

    public function search()
    {
        $searchTerm = request('q');
        $posts = Post::query()->showablePost()->search($searchTerm)->paginate(config('app.per_page'));

        return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }
}
