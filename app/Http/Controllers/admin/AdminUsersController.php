<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AdminUsersController extends Controller
{


    public function businessList(){
        $all_users = User::Where('is_onboarding_business', '1')->get();
        $title = 'Business';
        return view('admin.users.list', [
        'all_users'=>$all_users,
        'title'=>$title,
        ]);
    }
    public function candidateList(){
        $all_users = User::Where('is_onboarding_person', '1')->get();
        $title = 'Candidate';
        return view('admin.users.list', [
        'all_users'=>$all_users,
            'title'=>$title,
        ]);
    }

    public function edit($id)
    {
        try {
            $user = User::with(['posts', 'companyDetails'])->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'User not found.');
        }

        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        try {
            // Find the user
            $user = User::findOrFail($id);

            // Prepare data array
            $data = $request->only([
                'email',
                'phone',
                'first_name',
                'last_name',
                'date_of_birth',
                'city',
                'gender',
                'description',
            ]);



            // Update user attributes
            $user->update(array_filter($data)); // Use array_filter to remove null values

            return redirect()->back()->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }




    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->back()->with('success', 'User deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'User not found.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'There was an issue deleting the user.');
        }
    }
    public function inactive($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->blocked_by_admin = '1';
            $user->save();
            return redirect()->back()->with('success', 'User Blocked successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'User not found.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'There was an issue while Blocked the user.');
        }
    }
    public function acitve($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->blocked_by_admin = '0';
            $user->save();
            return redirect()->back()->with('success', 'User Unblocked successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'User not found.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'There was an issue while Unblocked the user.');
        }
    }




}
