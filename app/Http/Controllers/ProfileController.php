<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user()->load('employee');

        return view('auth.profile', [
            'user' => $user,
            'employee' => $user->employee,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user()->load('employee');

        $shouldRequireEmployeeName = $user->employee
            || in_array($user->role, ['pm', 'employee'], true)
            || $request->anyFilled(['position', 'nik', 'phone_number', 'birth_date', 'gender', 'address']);

        $employeeNameRules = [
            $shouldRequireEmployeeName ? 'required' : 'nullable',
            'string',
            'max:255',
        ];

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'avatar' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],

            'employee_name' => $employeeNameRules,
            'position' => ['nullable', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $user, $validated) {
            $payload = [
                'username' => $validated['username'],
                'email' => $validated['email'] ?? null,
            ];

            if (!empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
            }

            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 'public');

                if (!empty($user->avatar_path)) {
                    Storage::disk('public')->delete($user->avatar_path);
                }

                $payload['avatar_path'] = $path;
            }

            $user->update($payload);

            $shouldSaveEmployee = $user->employee
                || !empty($validated['employee_name'])
                || !empty($validated['position'])
                || !empty($validated['nik'])
                || !empty($validated['phone_number'])
                || !empty($validated['birth_date'])
                || !empty($validated['gender'])
                || !empty($validated['address']);

            if ($shouldSaveEmployee) {
                $employee = $user->employee ?: new Employee();
                $employee->user_id = $user->id;
                $employee->employee_name = $validated['employee_name'];
                $employee->position = $validated['position'] ?? null;
                $employee->nik = $validated['nik'] ?? null;
                $employee->phone_number = $validated['phone_number'] ?? null;
                $employee->birth_date = $validated['birth_date'] ?? null;
                $employee->gender = $validated['gender'] ?? null;
                $employee->address = $validated['address'] ?? null;
                $employee->save();
            }
        });

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
