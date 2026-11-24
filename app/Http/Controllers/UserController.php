<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::with('branch')->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('name', function($row){
                    $initial = e(mb_strtoupper(mb_substr($row->name, 0, 1)));
                    $you = $row->id === auth()->id()
                        ? ' <span class="ml-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 align-middle">You</span>'
                        : '';
                    return '<div class="flex items-center gap-3">'
                        . '<span class="w-9 h-9 shrink-0 rounded-full bg-gradient-to-br from-teal-400 to-emerald-600 text-white grid place-items-center text-sm font-semibold">'.$initial.'</span>'
                        . '<span class="font-medium text-slate-800 whitespace-nowrap">'.e($row->name).$you.'</span>'
                        . '</div>';
                })
                ->addColumn('branch', function($row){
                    return $row->branch
                        ? '<span class="inline-flex items-center gap-1.5 whitespace-nowrap text-slate-700"><i class="fas fa-code-branch text-[11px] text-slate-400"></i>'.e($row->branch->name).'</span>'
                        : '<span class="inline-flex items-center gap-1.5 whitespace-nowrap text-slate-500"><i class="fas fa-layer-group text-[11px] text-slate-400"></i>All branches</span>';
                })
                ->addColumn('role', function($row){
                    $tones = [
                        'admin' => 'bg-violet-100 text-violet-800',
                        'manager' => 'bg-sky-100 text-sky-800',
                        'pharmacist' => 'bg-teal-100 text-teal-800',
                        'cashier' => 'bg-amber-100 text-amber-800',
                    ];
                    $tone = $tones[$row->role] ?? 'bg-slate-100 text-slate-700';
                    return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full '.$tone.'">'.e(ucfirst(str_replace('_', ' ', $row->role))).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<div class="flex items-center gap-1.5">'
                        . '<a href="'.route('users.edit', $row->id).'" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-teal-300 hover:text-teal-700"><i class="fas fa-pen-to-square"></i> Edit</a>';
                    if ($row->id !== auth()->id() && $row->role !== 'super_admin') {
                        $btn .= '<form action="'.route('users.destroy', $row->id).'" method="POST" class="inline-block" onsubmit="return confirm(\'Are you sure you want to delete this user?\');">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50"><i class="fas fa-trash-can"></i> Delete</button>'
                            . '</form>';
                    }
                    return $btn . '</div>';
                })
                ->rawColumns(['name', 'role', 'branch', 'action'])
                ->make(true);
        }
        return view('users.index');
    }

    public function create()
    {
        $branches = Branch::all();
        $user = new User(); // empty user for form binding
        return view('users.form', compact('branches', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,cashier',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'branch_id' => $request->role === 'admin' ? null : $request->branch_id,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $branches = Branch::all();
        return view('users.form', compact('branches', 'user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,cashier',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'branch_id' => $request->role === 'admin' ? null : $request->branch_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete yourself.');
        }

        if ($user->role === 'super_admin') {
            return redirect()->route('users.index')->with('error', 'Super Admin cannot be deleted.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
