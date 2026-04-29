<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Rule;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rules = Rule::with('permissions')->get();
        $permissions = Permission::all();
        return view('content.apps.app-access-rules', compact('rules', 'permissions'));
    }
 public function getRules(Request $request)
    {
        $rules = Rule::all();
        return response()->json([
            'data' => $rules,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:rules,name',
            'permissions' => 'array',
        ]);

        $rule = Rule::create(['name' => $request->name]);
        if ($request->has('permissions')) {
            $rule->permissions()->sync($request->permissions);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Rule created successfully.', 'rule' => $rule->load('permissions')]);
        }

        return redirect()->route('app-access-rules.index')->with('success', 'Rule created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rule $rule)
    {
        $rule->load('permissions');
        $permissions = Permission::all();

        if (request()->ajax()) {
            return response()->json([
                'rule' => $rule,
                'permissions' => $permissions,
            ]);
        }

        return view('content.apps.app-access-rules-edit', compact('rule', 'permissions'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rule $rule)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:rules,name,' . $rule->id,
            'permissions' => 'array',
        ]);

        $rule->update(['name' => $request->name]);
        if ($request->has('permissions')) {
            $rule->permissions()->sync($request->permissions);
        } else {
            $rule->permissions()->sync([]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Rule updated successfully.', 'rule' => $rule->load('permissions')]);
        }

        return redirect()->route('app-access-rules.index')->with('success', 'Rule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Rule $rule)
    {
        $rule->delete();
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Rule deleted successfully.']);
        }
        return redirect()->route('app-access-rules.index')->with('success', 'Rule deleted successfully.');
    }
}
