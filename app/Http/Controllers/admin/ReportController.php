<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;

class ReportController extends Controller
{
    public function list()
    {
        $reports = ContactUs::orderBy('id', 'desc')->get();
        return view('admin.report.view', compact('reports'));
    }
}
