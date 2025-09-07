<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Transaction;

class PaymentsController extends Controller
{
    public function postLists()
    {
        $posts = Post::whereHas('jobApplications', function ($query) {
            $query->whereHas('transactions'); // Ensure job applications have at least one transaction
        })->with(['jobApplications', 'jobApplications.transactions'])->get();
        return view('admin.payments.postList',[
            'posts'=>$posts,
        ]);
    }
    public function PostTransactionList($id)
    {
        $posts = Post::where('id', $id)->with(['jobApplications', 'jobApplications.transactions'])->first();
        return view('admin.payments.view',[
            'posts'=>$posts,
        ]);
    }

}
