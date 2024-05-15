<?php

namespace App\Http\Controllers;

use App\Models\Post;

use Illuminate\View\View;

use App\Http\Controllers\Controller;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * index
     * 
     * @return View
     */
    public function index(): View
    {
        $posts = Post::latest()->paginate(5);

        return view('posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        //validate form
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title' => 'required|min:5',
            'content' => 'required|min:10'
        ]);
        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post 
        Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content
        ]);
        //redirect to index
        return redirect()->route('posts.index')->with(['success' =>  'Berhasil Disimpan!']);
    }

    //menampilkan
    public function show(string $id): View
    {
        $post = Post::findOrFail($id);

        return view('posts.show', compact('post'));
    }

    //hapus
    public function destroy($id): RedirectResponse
    {
        $post = Post::findOrFail($id);

        Storage::delete('public/posts/' . $post->image);

        $post->delete();

        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Dihapus']);
    }

    //edit dan update
    public function edit(string $id): View
    {
        $post = Post::findOrFail($id);

        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'image' => 'image|mimes:jpeg,jpg,png|max:2048',
            'title' => 'required|min:5',
            'content' => 'required|min:10'
        ]);

        $post = Post::findOrFail($id);

        if ($request->hasFile('image')) {
            // Upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // Delete old image
            Storage::delete('public/posts/' . $post->image);

            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content
            ]);
        } else {
            $post->update([
                'title' => $request->title,
                'content' => $request->content
            ]);
        }
        // Redirect to index
        return redirect()->route('posts.index')->with(['success' =>  'Berhasil Disimpan!']);
    }
}