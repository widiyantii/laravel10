<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    /**
     * index
     *
     * @return View
     */
    public function index(): View
    {
        // Get posts
        $posts = Post::latest()->paginate(5);

        // Render view with posts
        return view('posts.index', compact('posts'));
    }

    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        return view('posts.create');
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate form data
        $request->validate([
            'image'     => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'     => 'required|min:5',
            'content'   => 'required|min:10'
        ]);

        // Handle image upload
        $image = $request->file('image');
        $imagePath = $image->storeAs('public/posts', $image->hashName());

        // Create new post record
        Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content
        ]);

        // Redirect to index with success message
        return redirect()->route('posts.index')->with('success', 'Data Berhasil Disimpan!');
    }

    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        // Get post by ID
        $post = Post::findOrFail($id);

        // Render view with post
        return view('posts.show', compact('post'));
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        // Get post by ID
        $post = Post::findOrFail($id);

        // Render view with post
        return view('posts.edit', compact('post'));
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        // Validate form data
        $request->validate([
            'image'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',  // Image is optional in update
            'title'     => 'required|min:5',
            'content'   => 'required|min:10'
        ]);

        // Get post by ID
        $post = Post::findOrFail($id);

        // Check if image is uploaded
        if ($request->hasFile('image')) {
            // Upload new image
            $image = $request->file('image');
            $imagePath = $image->storeAs('public/posts', $image->hashName());

            // Delete old image from storage
            Storage::delete('public/posts/' . $post->image);

            // Update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        } else {
            // Update post without changing the image
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        }

        // Redirect to index with success message
        return redirect()->route('posts.index')->with('success', 'Data Berhasil Diubah!');
    }

    /**
     * destroy
     *
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function destroy(string $id): RedirectResponse
    {
        // Get post by ID
        $post = Post::findOrFail($id);

        // Delete image from storage
        Storage::delete('public/posts/' . $post->image);

        // Delete post record
        $post->delete();

        // Redirect to index with success message
        return redirect()->route('posts.index')->with('success', 'Data Berhasil Dihapus!');
    }
}
