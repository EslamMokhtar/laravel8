<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Posts;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionController;
use Illuminate\Validation\ValidationException;
use MailchimpMarketing\ApiClient;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('newsletter', function () {

    request()->validate(['email' => 'required|email']);
    $mailchimp = new ApiClient();

    $mailchimp->setConfig([
        'apiKey' => config('services.mailchimp.key'),
        'server' => 'us14'
    ]);
    try {
        $response = $mailchimp->lists->addListMember("88650b51ef", [
            'email_address' => request('email'),
            'status' => 'subscribed'
        ]);
    } catch (Exception $err) {
        throw ValidationException::withMessages([
            'email' => 'Your provided credentials could not be verified.'
        ]);
    }

    return redirect('/')->with('success', 'You are now signed up for our newsletter!');
});
//->load('category', 'author') to specific declare
Route::get('/', [Posts::class, 'index'])->name('home');
//Route::get('posts', [Posts::class, 'index']);
Route::get(
    '/categories/{category:slug}',
    fn (Category $category) => view('posts', ['posts' => $category->posts, 'categories' => Category::all(), 'currentCategory' => $category])
);
Route::get(
    '/authors/{author:username}',
    fn (User $author) => view('posts', ['posts' => $author->posts, 'categories' => Category::all(), 'currentCategory' => null])
);
Route::get('posts/{post:slug}', fn (Post $post) => view('post', ['post' => $post, 'commnets' => collect($post->comments)->sortByDesc('created_at')]))->where('id', '[0-9]+');

Route::get('register', [RegisterController::class, 'create'])->middleware('guest');
Route::post('register', [RegisterController::class, 'store'])->middleware('guest');

Route::post('logout', [SessionController::class, 'destroy'])->middleware('auth'); //auth and guest setting in Kernel file
Route::get('login', [SessionController::class, 'create'])->middleware('guest');
Route::post('login', [SessionController::class, 'store'])->middleware('guest');
Route::post('add', [Posts::class, 'storeComment'])->middleware('auth');
