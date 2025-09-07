<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = User::where('email', $request->email)
            ->where('type', 'admin')
            ->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        // Remember the email if the user checked "Remember Me"
        if ($request->has('remember')) {
            // Store email in cookie for a longer duration (e.g., 5 years)
            cookie()->queue('email', $request->email, 60 * 24 * 365 * 5); // 5 years
        }

        // Log the user in with remember functionality
        Auth::guard('admin')->login($admin, $request->has('remember'));

        return redirect()->route('admin.business.users.list');
    }


    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
