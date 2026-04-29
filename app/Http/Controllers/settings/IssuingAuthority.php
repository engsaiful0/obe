<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IssuingAuthority as IssuingAuthorityModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class IssuingAuthority extends Controller
{
    public function index()
    {
        return view('content.settings.issuing-authority');
    }

    public function getIssuingAuthority(Request $request)
    {
        $issuingAuthorities = IssuingAuthorityModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $issuingAuthorities,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:issuing_authorities,authority_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $issuingAuthority = IssuingAuthorityModel::create([
            'authority_name' => $request->name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Issuing Authority created successfully.', 'data' => $issuingAuthority], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:issuing_authorities,authority_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $issuingAuthority = IssuingAuthorityModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $issuingAuthority->update([
            'authority_name' => $request->name,
        ]);

        return response()->json(['message' => 'Issuing Authority updated successfully.', 'data' => $issuingAuthority]);
    }

    public function destroy($id)
    {
        $issuingAuthority = IssuingAuthorityModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $issuingAuthority->delete();

        return response()->json(['message' => 'Issuing Authority deleted successfully.']);
    }
}
