<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, $postId)
    {
        // Validate the input
        $request->validate([
            'content' => 'required|string',
        ]);

        // Find the post the comment belongs to
        $post = Post::findOrFail($postId);

        // Create a new comment
        $comment = new Comment([
            'content' => $request->input('content'),
            'status' => 'pending',
        ]);

        // Associate the comment with the post and the user (if logged in)
        $comment->post()->associate($post);
        if ($request->user()) {
            $comment->user()->associate($request->user());
        }

        // Save the comment
        $comment->save();

        // Redirect back to the post with a success message
        return redirect()->route('posts.show', $postId)->with('success', 'Comment submitted successfully and is awaiting approval.');
    }

    public function index()
    {
        // List all comments
        $comments = Comment::with('post', 'user')->get();
        return view('comments.index', compact('comments'));
    }

    public function approve($id)
    {
        // Approve a comment
        $comment = Comment::findOrFail($id);
        $comment->status = 'approved';
        $comment->save();

        // Redirect to the comment list with a success message
        return redirect()->route('comments.index')->with('success', 'Comment approved successfully.');
    }

    public function destroy($id)
    {
        // Delete a comment
        $comment = Comment::findOrFail($id);
        $comment->delete();

        // Redirect to the comment list with a success message
        return redirect()->route('comments.index')->with('success', 'Comment deleted successfully.');
    }
}
