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
                                <td>{{ $employee->user?->username ?? '-' }}</td>
                                <td>{{ $employee->user?->role ?? '-' }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-light-primary btnEditEmployee"
                                        data-id="{{ $employee->id }}"
                                        data-employee_name="{{ $employee->employee_name }}"
                                        data-position="{{ $employee->position }}"
                                        data-username="{{ $employee->user?->username }}"
                                        data-role="{{ $employee->user?->role }}">
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
                            <label class="form-label required">Username</label>
                            <input type="text" name="username" id="employee_username" class="form-control" required />
                        </div>

                        <div class="mb-4">
                            <label class="form-label" id="password_label">Password</label>
                            <input type="password" name="password" id="employee_password" class="form-control" autocomplete="off" />
                            <div class="form-text" id="password_help" style="display:none;">Kosongkan jika tidak ingin mengubah password.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label required">Role</label>
                            <select name="role" id="employee_role" class="form-select select2-role" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="pm">PM</option>
                                <option value="employee">Employee</option>
                            </select>
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

        function initSelect2() {
            if (window.$ && window.$.fn && window.$.fn.select2) {
                window.$('.select2-role').select2({
                    dropdownParent: window.$('#employeeModal'),
                    width: '100%'
                });
            }
        }

        window.$(employeeModalEl).on('shown.bs.modal', function () {
            initSelect2();
        });

        document.getElementById('btnAddEmployee').addEventListener('click', function () {
            employeeForm.action = storeUrl;
            employeeFormMethod.value = 'POST';
            employeeModalTitle.textContent = 'Tambah Karyawan';
            employeeForm.reset();

            document.getElementById('employee_password').required = true;
            document.getElementById('password_label').classList.add('required');
            document.getElementById('password_help').style.display = 'none';
        });

        jQuery('#employee_table').on('click', '.btnEditEmployee', function () {
            var btn = this;
            employeeForm.action = baseUrl + '/' + btn.dataset.id;
            employeeFormMethod.value = 'PUT';
            employeeModalTitle.textContent = 'Edit Karyawan';

            document.getElementById('employee_name').value = btn.dataset.employee_name || '';
            document.getElementById('employee_position').value = btn.dataset.position || '';
            document.getElementById('employee_username').value = btn.dataset.username || '';

            document.getElementById('employee_password').value = '';
            document.getElementById('employee_password').required = false;
            document.getElementById('password_label').classList.remove('required');
            document.getElementById('password_help').style.display = 'block';

            var roleSelect = document.getElementById('employee_role');
            roleSelect.value = btn.dataset.role || '';
            if (window.$ && window.$.fn && window.$.fn.select2) {
                window.$(roleSelect).trigger('change');
            }

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
