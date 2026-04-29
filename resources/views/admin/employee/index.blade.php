@extends('layouts.master')

@section('page_title', 'Data Karyawan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Data Karyawan</h3>
        <button class="btn btn-primary" id="btnAddEmployee" data-bs-toggle="modal" data-bs-target="#employeeModal">
            <i class="bi bi-plus-lg me-2"></i>Tambah Karyawan
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="employee_table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Nama Karyawan</th>
                            <th>Posisi</th>
                            <th>NIK</th>
                            <th>No. HP</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td>{{ $employee->employee_name }}</td>
                                <td>{{ $employee->position ?? '-' }}</td>
                                <td>{{ $employee->nik ?? '-' }}</td>
                                <td>{{ $employee->phone_number ?? '-' }}</td>
                                <td>{{ $employee->user?->email ?? '-' }}</td>
                                <td>{{ $employee->user?->username ?? '-' }}</td>
                                <td>
                                    @php
                                        $role = $employee->user?->role ?? 'employee';
                                        $badgeClass = match ($role) {
                                            'admin' => 'badge-light-danger',
                                            'pm' => 'badge-light-primary',
                                            default => 'badge-light-success',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ strtoupper($role) }}</span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-light-primary btnEditEmployee"
                                        data-id="{{ $employee->id }}"
                                        data-employee_name="{{ $employee->employee_name }}"
                                        data-position="{{ $employee->position }}"
                                        data-nik="{{ $employee->nik }}"
                                        data-phone_number="{{ $employee->phone_number }}"
                                        data-birth_date="{{ $employee->birth_date }}"
                                        data-gender="{{ $employee->gender }}"
                                        data-address="{{ $employee->address }}"
                                        data-email="{{ $employee->user?->email }}"
                                        data-username="{{ $employee->user?->username }}"
                                        data-role="{{ $employee->user?->role ?? 'employee' }}"
                                        data-has_user="{{ $employee->user_id ? '1' : '0' }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light-danger btnDeleteEmployee"
                                        data-id="{{ $employee->id }}"
                                        data-name="{{ $employee->employee_name }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="employeeForm" method="POST" action="{{ route('admin.employees.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="employeeFormMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="employeeModalTitle">Tambah Karyawan</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label required">Nama Karyawan</label>
                            <input type="text" name="employee_name" id="employee_name" class="form-control" required />
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Posisi / Jabatan</label>
                            <input type="text" name="position" id="employee_position" class="form-control" />
                        </div>

                        <div class="mb-4">
                            <label class="form-label">NIK</label>
                            <input type="text" name="nik" id="employee_nik" class="form-control" />
                        </div>

                        <div class="mb-4">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="phone_number" id="employee_phone_number" class="form-control" />
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="birth_date" id="employee_birth_date" class="form-control" />
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="gender" id="employee_gender" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="L">L</option>
                                <option value="P">P</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" id="employee_address" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label required">Role</label>
                            <select name="role" id="employee_role" class="form-select select2-role" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="admin">Admin</option>
                                <option value="pm">PM</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>

                        <div id="account_fields" style="display:none;">
                            <div class="mb-4">
                                <label class="form-label" id="username_label">Username</label>
                                <input type="text" name="username" id="employee_username" class="form-control" />
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="employee_email" class="form-control" />
                            </div>

                            <div class="mb-4">
                                <label class="form-label" id="password_label">Password</label>
                                <input type="password" name="password" id="employee_password" class="form-control" autocomplete="off" />
                                <div class="form-text" id="password_help">Kosongkan untuk menggunakan password default: Qwerty123*.</div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Karyawan</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus karyawan <strong id="delete_name">-</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var dt = jQuery('#employee_table').DataTable({
            pageLength: 10,
            ordering: true,
        });

        var employeeForm = document.getElementById('employeeForm');
        var employeeFormMethod = document.getElementById('employeeFormMethod');
        var employeeModalTitle = document.getElementById('employeeModalTitle');
        var employeeModalEl = document.getElementById('employeeModal');

        var storeUrl = "{{ route('admin.employees.store') }}";
        var baseUrl = "{{ url('admin/employees') }}";

        var formMode = 'create';
        var hasUser = false;

        function setPasswordHelpText(role) {
            var help = document.getElementById('password_help');
            if (!help) return;

            var needsAccount = (role === 'admin' || role === 'pm');
            if (!needsAccount) return;

            if (formMode === 'create' || !hasUser) {
                help.textContent = 'Kosongkan untuk menggunakan password default: Qwerty123*.';
                return;
            }

            help.textContent = 'Kosongkan jika tidak ingin mengubah password.';
        }

        function toggleAccountFields(role) {
            var wrap = document.getElementById('account_fields');
            var username = document.getElementById('employee_username');
            var password = document.getElementById('employee_password');
            var email = document.getElementById('employee_email');
            var usernameLabel = document.getElementById('username_label');

            var needsAccount = (role === 'admin' || role === 'pm');

            if (wrap) wrap.style.display = needsAccount ? 'block' : 'none';
            if (username) {
                username.required = needsAccount;
                if (!needsAccount) username.value = '';
            }
            if (password) {
                password.required = false;
                if (!needsAccount) password.value = '';
            }
            if (email) {
                if (!needsAccount) email.value = '';
            }
            if (usernameLabel) {
                if (needsAccount) usernameLabel.classList.add('required');
                else usernameLabel.classList.remove('required');
            }

            setPasswordHelpText(role);
        }

        function initSelect2() {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('.select2-role').select2({
                    dropdownParent: window.jQuery('#employeeModal'),
                    width: '100%'
                });
            }
        }

        toggleAccountFields('');

        if (window.jQuery) {
            window.jQuery(employeeModalEl).on('shown.bs.modal', function () {
                initSelect2();
            });
        }

        document.getElementById('btnAddEmployee').addEventListener('click', function () {
            employeeForm.action = storeUrl;
            employeeFormMethod.value = 'POST';
            employeeModalTitle.textContent = 'Tambah Karyawan';
            employeeForm.reset();

            formMode = 'create';
            hasUser = false;

            toggleAccountFields(document.getElementById('employee_role').value);
        });

        document.getElementById('employee_role').addEventListener('change', function () {
            toggleAccountFields(this.value);
        });

        if (window.jQuery) {
            window.jQuery('#employee_role').on('select2:select', function () {
                toggleAccountFields(this.value);
            });
        }

        jQuery('#employee_table').on('click', '.btnEditEmployee', function () {
            var btn = this;
            employeeForm.action = baseUrl + '/' + btn.dataset.id;
            employeeFormMethod.value = 'PUT';
            employeeModalTitle.textContent = 'Edit Karyawan';

            formMode = 'edit';
            hasUser = btn.dataset.has_user === '1';

            document.getElementById('employee_name').value = btn.dataset.employee_name || '';
            document.getElementById('employee_position').value = btn.dataset.position || '';
            document.getElementById('employee_nik').value = btn.dataset.nik || '';
            document.getElementById('employee_phone_number').value = btn.dataset.phone_number || '';
            document.getElementById('employee_birth_date').value = btn.dataset.birth_date || '';
            document.getElementById('employee_gender').value = btn.dataset.gender || '';
            document.getElementById('employee_address').value = btn.dataset.address || '';
            document.getElementById('employee_username').value = btn.dataset.username || '';
            document.getElementById('employee_email').value = btn.dataset.email || '';

            document.getElementById('employee_password').value = '';

            var roleSelect = document.getElementById('employee_role');
            roleSelect.value = btn.dataset.role || '';
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery(roleSelect).trigger('change');
            }

            toggleAccountFields(roleSelect.value);

            var modal = bootstrap.Modal.getOrCreateInstance(employeeModalEl);
            modal.show();
        });

        jQuery('#employee_table').on('click', '.btnDeleteEmployee', function () {
            var btn = this;
            document.getElementById('deleteForm').action = baseUrl + '/' + btn.dataset.id;
            document.getElementById('delete_name').textContent = btn.dataset.name;

            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteModal'));
            modal.show();
        });
    });
</script>
@endpush
