<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $payments = Transaction::with('jobApplication')->where('status', 'success')->get();
        return view('admin.dashboard',[
            'payments'=>$payments,
            'successPaymentCount'=>$payments->count(),
        ]);
    }
    public function postViews($id)
    {
        $posts = Post::with(['user', 'jobApplications'])->find($id);
        if (!$posts){
            return redirect()->back()->with('error', 'Post not found.');
        }
        return view('admin.posts.view', compact('posts'));
    }
}
