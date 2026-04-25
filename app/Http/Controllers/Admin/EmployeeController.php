<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::query()
            ->with('user')
            ->latest()
            ->get();

        return view('admin.employee.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['pm', 'employee'])],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'username' => $validated['username'],
                'password' => $validated['password'],
                'role' => $validated['role'],
            ]);

            Employee::create([
                'user_id' => $user->id,
                'employee_name' => $validated['employee_name'],
                'position' => $validated['position'] ?? null,
            ]);
        });

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::query()->with('user')->findOrFail($id);

        $validated = $request->validate([
            'employee_name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($employee->user_id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['pm', 'employee'])],
        ]);

        DB::transaction(function () use ($employee, $validated) {
            $employee->update([
                'employee_name' => $validated['employee_name'],
                'position' => $validated['position'] ?? null,
            ]);

            $userPayload = [
                'username' => $validated['username'],
                'role' => $validated['role'],
            ];

            if (!empty($validated['password'])) {
                $userPayload['password'] = $validated['password'];
            }

            $employee->user()->update($userPayload);
        });

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $employee = Employee::query()->with('user')->findOrFail($id);

        DB::transaction(function () use ($employee) {
            $employee->delete();
            $employee->user()->delete();
        });

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil dihapus.');
    }
}
