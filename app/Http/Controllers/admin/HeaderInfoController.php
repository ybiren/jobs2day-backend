<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\HeaderInfo;
use Illuminate\Http\Request;

class HeaderInfoController extends Controller
{
    // Show the form for editing the specified resource
    public function edit()
    {
        $headerInfo = HeaderInfo::first();
        return view('admin.header_info', [
            'headerInfo'=>$headerInfo,
        ]);
    }

    public function update(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_phone' => 'required|string|max:255',
            'site_email' => 'required|email|max:255',
            'site_url' => 'required|url',
            'site_owner' => 'required|string|max:255',
            'site_address' => 'required|string',
            'logo1' => 'nullable|image|max:2048',
            'logo2' => 'nullable|image|max:2048',
            'logo1alt' => 'nullable|string|max:255',
            'logo2alt' => 'nullable|string|max:255',
        ]);

        // Find the record
        $headerInfo = HeaderInfo::first();
        if (!$headerInfo) {
            return redirect()->route('admin.hearderinfo')->with('error', 'HeaderInfo record not found.');
        }

        if ($request->file('logo1')) {
            $logo1 = $request->file('logo1');
            $logo1_folder = 'logo';
            $logo1_name = time() . '_' . $logo1->getClientOriginalName();
            $logo1->move(public_path($logo1_folder), $logo1_name);
            $headerInfo->update([
                'logo1' => $logo1_folder . '/' . $logo1_name,
            ]);
        }

        if ($request->file('logo2')) {
            $logo2 = $request->file('logo2');
            $logo2_folder = 'logo';
            $logo2_name = time() . '_' . $logo2->getClientOriginalName();
            $logo2->move(public_path($logo2_folder), $logo2_name);
            $headerInfo->update([
                'logo2' => $logo2_folder . '/' . $logo2_name,
            ]);
        }


        // Update the record
        $headerInfo->update([
            'site_name' => $request->site_name,
            'site_phone' => $request->site_phone,
            'site_email' => $request->site_email,
            'site_url' => $request->site_url,
            'site_owner' => $request->site_owner,
            'site_address' => $request->site_address,
            'logo1alt' => $request->logo1alt,
            'logo2alt' => $request->logo2alt,
        ]);

        return redirect()->route('admin.hearderinfo')->with('success', 'HeaderInfo updated successfully!');
    }


}
