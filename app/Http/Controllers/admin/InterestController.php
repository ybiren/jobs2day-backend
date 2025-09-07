<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;

class InterestController extends Controller
{
// Display a list of all interests
    public function index()
    {
        $interests = Topic::all();
        return view('admin.interests.list', compact('interests'));
    }

    // Show form to create a new interest
    public function create()
    {
        return view('admin.interests.add');
    }

    // Store a newly created interest
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'emoji' => 'nullable|string|max:255',
            'status' => 'nullable|in:0,1',
            'color' => 'nullable|string|max:255',
        ]);

        Topic::create($request->all());

        return redirect()->route('admin.interests.list')->with('success', 'Topic added successfully.');
    }

    // Show form to edit an interest
    public function edit($id)
    {
        $interest = Topic::findOrFail($id);
        return view('admin.interests.edit', compact('interest'));
    }



    public function update(Request $request, $id)
    {
        $interest = Topic::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'emoji' => 'required|string|max:255',
            'color' => 'required|string|max:7',  // Hex color validation (e.g., #FFFFFF)
            'status' => 'nullable|boolean',
        ]);

        // Update the interest
        $interest->update([
            'name' => $request->name,
            'emoji' => $request->emoji,
            'color' => $request->color,
            'status' => $request->has('status') ? '1' : '0',  // If the checkbox is checked, set status to 1
        ]);

        return redirect()->route('admin.interests.list')->with('success', 'Topic updated successfully');
    }


    // Delete an interest
    public function destroy($id)
    {
        $interest = Topic::findOrFail($id);
        $interest->delete();

        return redirect()->route('admin.interests.list')->with('success', 'Topic deleted successfully.');
    }

}
