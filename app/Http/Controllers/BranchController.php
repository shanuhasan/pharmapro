<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Branch::with('manager')->select('branches.*')->latest();
            return \Yajra\DataTables\Facades\DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('manager', function($row){
                        return $row->manager ? $row->manager->name : 'N/A';
                    })
                    ->addColumn('status', function($row){
                        return $row->is_active ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>' : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>';
                    })
                    ->addColumn('action', function($row){
                           $editUrl = route('branches.edit', $row->id);
                           $deleteUrl = route('branches.destroy', $row->id);
                           $btn = '<a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>';
                           $btn .= '<form action="'.$deleteUrl.'" method="POST" class="inline-block" onsubmit="return confirm(\'Are you sure you want to delete this branch?\');">
                                        '.csrf_field().'
                                        '.method_field("DELETE").'
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>';
                            return $btn;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
        }
        
        return view('branches.index');
    }

    public function create()
    {
        $managers = User::whereIn('role', ['admin', 'manager'])->get();
        return view('branches.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch)
    {
        $managers = User::whereIn('role', ['admin', 'manager'])->get();
        return view('branches.edit', compact('branch', 'managers'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
    }
}
