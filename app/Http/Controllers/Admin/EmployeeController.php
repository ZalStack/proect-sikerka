<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['employee', 'division'])
            ->where('role', 'employee');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('enroll_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by division
        if ($request->has('division') && $request->division) {
            $query->where('division_id', $request->division);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $employees = $query->latest()->paginate(10);
        $divisions = Division::where('is_active', true)->get();

        return view('admin.employees.index', compact('employees', 'divisions'));
    }

    public function create()
    {
        $divisions = Division::where('is_active', true)->get();
        return view('admin.employees.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'enroll_number' => 'required|string|unique:users,enroll_number',
            'division_id' => 'required|exists:divisions,id',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'enroll_number' => $request->enroll_number,
            'division_id' => $request->division_id,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'is_active' => $request->is_active ?? true,
        ]);

        Employee::create([
            'user_id' => $user->id,
            'full_name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'join_date' => $request->join_date,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(User $employee)
    {
        $divisions = Division::where('is_active', true)->get();
        $employee->load('employee');
        return view('admin.employees.edit', compact('employee', 'divisions'));
    }

    public function update(Request $request, User $employee)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $employee->id,
            'enroll_number' => 'required|string|unique:users,enroll_number,' . $employee->id,
            'division_id' => 'required|exists:divisions,id',
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'enroll_number' => $request->enroll_number,
            'division_id' => $request->division_id,
            'is_active' => $request->is_active ?? true,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        $employee->employee()->updateOrCreate(
            ['user_id' => $employee->id],
            [
                'full_name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'join_date' => $request->join_date,
                'is_active' => $request->is_active ?? true,
            ]
        );

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(User $employee)
    {
        // Delete related data
        $employee->employee()->delete();
        $employee->delete();

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Import logic here
        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil diimport.');
    }

    public function export()
    {
        // Export logic here
        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil diexport.');
    }
}
