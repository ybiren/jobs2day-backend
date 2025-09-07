<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\Topic;
use Illuminate\Http\Request;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;

class PostReportController extends Controller
{

    public function list()
    {
        $reposts = PostReport::with(['post' => function($query) {
            $query->withTrashed(); // Include soft-deleted posts
        }, 'post.user', 'user', 'reportType'])->orderBy('id', 'desc')
            ->get();
        return view('admin.reports.post.list', compact('reposts'));
    }


    public function postDelete($id)
    {
        try {
            $report = PostReport::findOrFail($id);
            $reportedPost = Post::findOrFail($report->post_id);
//            $reportedPost->postAttachments()->delete();
            $reportedPost->delete();

            return redirect()->back()->with('success', 'Post deleted successfully');
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', 'An error occurred: ' . $exception->getMessage());
        }
    }





}
