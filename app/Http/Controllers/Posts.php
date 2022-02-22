<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;

class Posts extends Controller
{

    public function index()
    {
        //Post::latest('created_at')->with('category', 'author')->get() to specific declare
        //->without('category', 'author') to specific clear
        $posts = Post::latest('created_at')->filter(request(['search', 'category', 'author']))->paginate(6)->withQueryString();
        return view(
            'posts',
            [
                'posts' => $posts, 'categories' => Category::all(),
                'currentCategory' => Category::firstWhere('slug', request('category'))
            ]
        );
    }

    public static function getPosts($id)
    {
        // $posts = collect(Post::all());
        // $post = $posts->firstWhere('id', $id);
        return Post::find($id);
        // ddd($posts->sortBy('id')->values()->all());
        // ddd(now()->addHour());
    }
    public static function storeComment()
    {   
        request()->validate([
            'body'=>'required'
        ]);
        Comment::create(['body' => request()->body, 'user_id' => auth()->user()->id, 'post_id' => request()->pid]);
        return back();
    }
    public static function findOrFail($id)
    {
        $post = static::getPosts($id);

        if (!$post) {
            abort(404);
            // return response()->json([
            //     'message' => 'Record not found.'
            // ], 404);
        }
        return (view('post', ['post' => $post]));
    }
}
