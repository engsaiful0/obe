<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\User as UserModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class User extends Controller
{
  public function index()
  {
    return view('content.settings.user');
  }

  public function getUsers(Request $request)
  {
    $users = UserModel::with('rule')->get();
    return response()->json([
      'data' => $users,
    ]);
  }

  public function store(Request $request)
  {
    $rules = [
      'name' => 'required|string|max:255',
      'rule_id' => 'required|exists:rules,id',
      'email' => [
        'required',
        'email',
        Rule::unique('users'),
      ],
      'password' => 'required|min:6',
      'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ];

    $request->validate($rules);

    $data = [
      'name' => $request->name,
      'email' => $request->email,
      'password' => bcrypt($request->password),
      'rule_id' => $request->rule_id,
    ];

    // Handle profile picture
    if ($request->hasFile('profile_picture')) {
      $file = $request->file('profile_picture');
      $fileName = time() . '_' . $file->getClientOriginalName();

      // Save directly to public/profile_pictures
      $file->move(public_path('profile_pictures'), $fileName);

      $data['profile_picture'] = $fileName;
    }

    $user = UserModel::create($data);

    return response()->json([
      'message' => 'Created successfully',
      'user' => $user,
    ]);
  }

  public function update(Request $request, $id)
  {
    $user = UserModel::findOrFail($id);

    $rules = [
      'name' => 'required|string|max:255',
      'rule_id' => 'required|exists:rules,id',
      'email' => [
        'required',
        'email',
        Rule::unique('users')->ignore($id),
      ],
      'password' => 'nullable|min:6',
      'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ];

    $request->validate($rules);

    $data = [
      'name' => $request->name,
      'email' => $request->email,
      'rule_id' => $request->rule_id,
    ];

    if ($request->filled('password')) {
      $data['password'] = bcrypt($request->password);
    }

    // Handle profile picture
    if ($request->hasFile('profile_picture')) {
      $file = $request->file('profile_picture');
      $fileName = time() . '_' . $file->getClientOriginalName();

      // Save directly to public/profile_pictures
      $file->move(public_path('profile_pictures'), $fileName);

      $data['profile_picture'] = $fileName;

      // Delete old picture if exists
      if ($user->profile_picture) {
        $oldPath = public_path('profile_pictures/' . $user->profile_picture);
        if (file_exists($oldPath)) {
          unlink($oldPath);
        }
      }
    }

    $user->update($data);

    return response()->json(['message' => 'Updated successfully']);
  }
  public function edit($id)
  {
    $user = UserModel::findOrFail($id);
    return response()->json($user);
  }

  public function destroy($id)
  {
    $user = UserModel::findOrFail($id);
    if ($user->profile_picture) {
      Storage::delete('public/profile_pictures/' . $user->profile_picture);
    }
    $user->delete();

    return response()->json(null, Response::HTTP_NO_CONTENT);
  }
}
