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
            'role' => ['required', Rule::in(['admin', 'pm', 'employee'])],
        ]);

        $role = $validated['role'];

        if (in_array($role, ['admin', 'pm'], true)) {
            $accountValidated = $request->validate([
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users', 'username')->whereNull('deleted_at'),
                ],
                'password' => ['nullable', 'string', 'min:6'],
            ]);

            DB::transaction(function () use ($validated, $accountValidated, $role) {
                $plainPassword = $accountValidated['password'] ?: 'Qwerty123*';

                $user = User::withTrashed()
                    ->where('username', $accountValidated['username'])
                    ->first();

                if ($user) {
                    if ($user->trashed()) {
                        $user->restore();
                    }

                    $user->update([
                        'password' => $plainPassword,
                        'role' => $role,
                    ]);
                } else {
                    $user = User::create([
                        'username' => $accountValidated['username'],
                        'password' => $plainPassword,
                        'role' => $role,
                    ]);
                }

                Employee::create([
                    'user_id' => $user->id,
                    'employee_name' => $validated['employee_name'],
                    'position' => $validated['position'] ?? null,
                ]);
            });

            return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
        }

        Employee::create([
            'user_id' => null,
            'employee_name' => $validated['employee_name'],
            'position' => $validated['position'] ?? null,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::query()->with('user')->findOrFail($id);

        $validated = $request->validate([
            'employee_name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'pm', 'employee'])],
        ]);

        $role = $validated['role'];

        if (in_array($role, ['admin', 'pm'], true)) {
            if ($employee->user) {
                $accountValidated = $request->validate([
                    'username' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('users', 'username')->ignore($employee->user_id),
                    ],
                    'password' => ['nullable', 'string', 'min:6'],
                ]);
            } else {
                $accountValidated = $request->validate([
                    'username' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('users', 'username')->whereNull('deleted_at'),
                    ],
                    'password' => ['nullable', 'string', 'min:6'],
                ]);
            }

            DB::transaction(function () use ($employee, $validated, $accountValidated, $role) {
                $employee->update([
                    'employee_name' => $validated['employee_name'],
                    'position' => $validated['position'] ?? null,
                ]);

                if ($employee->user) {
                    $payload = [
                        'username' => $accountValidated['username'],
                        'role' => $role,
                    ];

                    if (!empty($accountValidated['password'])) {
                        $payload['password'] = $accountValidated['password'];
                    }

                    $employee->user()->update($payload);
                    return;
                }

                $plainPassword = $accountValidated['password'] ?: 'Qwerty123*';

                $user = User::withTrashed()
                    ->where('username', $accountValidated['username'])
                    ->first();

                if ($user) {
                    if ($user->trashed()) {
                        $user->restore();
                    }

                    $user->update([
                        'password' => $plainPassword,
                        'role' => $role,
                    ]);
                } else {
                    $user = User::create([
                        'username' => $accountValidated['username'],
                        'password' => $plainPassword,
                        'role' => $role,
                    ]);
                }

                $employee->update([
                    'user_id' => $user->id,
                ]);
            });

            return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil diperbarui.');
        }

        DB::transaction(function () use ($employee, $validated) {
            $userToDelete = $employee->user;

            $employee->update([
                'employee_name' => $validated['employee_name'],
                'position' => $validated['position'] ?? null,
                'user_id' => null,
            ]);

            if ($userToDelete) {
                $userToDelete->delete();
            }
        });

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $employee = Employee::query()->with('user')->findOrFail($id);

        DB::transaction(function () use ($employee) {
            $userToDelete = $employee->user;
            $employee->delete();
            if ($employee->user) {
                $userToDelete?->delete();
            }
        });

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil dihapus.');
    }
}
