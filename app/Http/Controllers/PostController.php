<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'excerpt' => 'required',
            'description' => 'required',
            'image' => 'image|nullable',
            'keywords' => 'nullable',
            'meta_title' => 'nullable',
            'meta_description' => 'nullable',
            'published_at' => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images', 'public');
        }

        $blogPost = BlogPost::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        // Dispatch job to queue for email notification
        dispatch(new \App\Jobs\SendPostPublishedNotification($blogPost));

        return response()->json($blogPost, 201);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $this->authorize('update', $blogPost);

        $validated = $request->validate([
            'title' => 'required',
            'excerpt' => 'required',
            'description' => 'required',
            'image' => 'image|nullable',
            'keywords' => 'nullable',
            'meta_title' => 'nullable',
            'meta_description' => 'nullable',
            'published_at' => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($blogPost->image);
            $validated['image'] = $request->file('image')->store('images', 'public');
        }

        $blogPost->update($validated);
        return response()->json($blogPost, 200);
    }

    public function destroy(BlogPost $blogPost)
    {
        $this->authorize('delete', $blogPost);
        $blogPost->delete();
        return response()->json(null, 204);
    }
}
