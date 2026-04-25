@extends('layouts.master')

@section('page_title', 'Detail Proyek')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-1">{{ $project->project_name }}</h3>
            <div class="text-muted">{{ $project->client?->client_name ?? '-' }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.projects.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    @php
        $statusClass = match ($project->status) {
            'active' => 'badge-light-success',
            'completed' => 'badge-light-primary',
            'on_hold' => 'badge-light-warning',
            default => 'badge-light-secondary',
        };
        $statusLabel = match ($project->status) {
            'active' => 'Active',
            'completed' => 'Completed',
            'on_hold' => 'On Hold',
            default => strtoupper((string) $project->status),
        };
    @endphp

    <div class="card mb-5">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="text-muted">Tanggal Mulai</div>
                    <div class="fw-semibold">{{ $project->start_date ?? '-' }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-muted">Tanggal Selesai</div>
                    <div class="fw-semibold">{{ $project->end_date ?? '-' }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-muted">Status</div>
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header align-items-center">
            <h5 class="card-title mb-0">Project Roles</h5>
            <div class="card-toolbar">
                <button class="btn btn-primary" id="btnAddRole" data-bs-toggle="modal" data-bs-target="#roleModal">
                    <i class="bi bi-plus-lg me-2"></i>Tambah Role
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="roles_table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Role</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td>{{ $role->role_name }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-light-primary btnEditRole"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->role_name }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light-danger btnDeleteRole"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->role_name }}">
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

    <div class="row">
        @foreach ($project->projectManagers as $pmLink)
            @php
                $pmName = $pmLink->employee?->employee_name ?? '-';
            @endphp
            <div class="col-lg-6 mb-5">
                <div class="card h-100">
                    <div class="card-header align-items-center">
                        <h5 class="card-title mb-0">PM: {{ $pmName }}</h5>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-primary btnAddMember"
                                data-project_manager_id="{{ $pmLink->id }}"
                                data-pm_name="{{ $pmName }}"
                                data-bs-toggle="modal"
                                data-bs-target="#memberModal">
                                <i class="bi bi-plus-lg me-2"></i>Tambah Anggota
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 team_table">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th>Nama</th>
                                        <th>Role</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pmLink->projectEmployees as $pe)
                                        <tr>
                                            <td>{{ $pe->employee?->employee_name ?? '-' }}</td>
                                            <td>{{ $pe->projectRole?->role_name ?? '-' }}</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-light-danger btnDeleteMember"
                                                    data-id="{{ $pe->id }}"
                                                    data-name="{{ $pe->employee?->employee_name ?? '-' }}">
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
            </div>
        @endforeach
    </div>

    <div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="roleForm" method="POST" action="{{ route('admin.projects.roles.store', $project->id) }}">
                    @csrf
                    <input type="hidden" name="_method" id="roleFormMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="roleModalTitle">Tambah Role</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label required">Nama Role</label>
                            <input type="text" name="role_name" id="role_name" class="form-control" required />
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

    <div class="modal fade" id="memberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="memberForm" method="POST" action="{{ route('admin.projects.assign-employee', $project->id) }}">
                    @csrf
                    <input type="hidden" name="project_manager_id" id="member_project_manager_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="memberModalTitle">Tambah Anggota</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label required">Karyawan</label>
                            <select name="employee_id" id="member_employee_id" class="form-select select2-employee" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label required">Role</label>
                            <select name="project_role_id" id="member_project_role_id" class="form-select select2-role" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                @endforeach
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
                    <input type="hidden" name="_method" id="deleteMethod" value="DELETE">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalTitle">Hapus</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus <strong id="delete_name">-</strong>?</p>
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
        jQuery('#roles_table').DataTable({
            pageLength: 10,
            ordering: true,
        });

        jQuery('.team_table').each(function () {
            jQuery(this).DataTable({
                pageLength: 5,
                ordering: true,
            });
        });

        function initSelect2() {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('.select2-employee').select2({
                    dropdownParent: window.jQuery('#memberModal'),
                    width: '100%'
                });

                window.jQuery('.select2-role').select2({
                    dropdownParent: window.jQuery('#memberModal'),
                    width: '100%'
                });
            }
        }

        if (window.jQuery) {
            window.jQuery('#memberModal').on('shown.bs.modal', function () {
                initSelect2();
            });
        }

        var roleForm = document.getElementById('roleForm');
        var roleFormMethod = document.getElementById('roleFormMethod');
        var roleModalTitle = document.getElementById('roleModalTitle');
        var roleName = document.getElementById('role_name');

        var roleStoreUrl = "{{ route('admin.projects.roles.store', $project->id) }}";
        var roleBaseUrl = "{{ url('admin/projects/' . $project->id . '/roles') }}";

        document.getElementById('btnAddRole').addEventListener('click', function () {
            roleForm.action = roleStoreUrl;
            roleFormMethod.value = 'POST';
            roleModalTitle.textContent = 'Tambah Role';
            roleName.value = '';
        });

        jQuery('#roles_table').on('click', '.btnEditRole', function () {
            var btn = this;
            roleForm.action = roleBaseUrl + '/' + btn.dataset.id;
            roleFormMethod.value = 'PUT';
            roleModalTitle.textContent = 'Edit Role';
            roleName.value = btn.dataset.name || '';

            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('roleModal'));
            modal.show();
        });

        jQuery('#roles_table').on('click', '.btnDeleteRole', function () {
            var btn = this;
            document.getElementById('deleteForm').action = roleBaseUrl + '/' + btn.dataset.id;
            document.getElementById('delete_name').textContent = btn.dataset.name;
            document.getElementById('deleteModalTitle').textContent = 'Hapus Role';
            document.getElementById('deleteMethod').value = 'DELETE';

            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteModal'));
            modal.show();
        });

        jQuery('.btnAddMember').on('click', function () {
            var btn = this;
            document.getElementById('member_project_manager_id').value = btn.dataset.project_manager_id;
            document.getElementById('memberModalTitle').textContent = 'Tambah Anggota - ' + (btn.dataset.pm_name || 'PM');

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('#member_employee_id').val('').trigger('change');
                window.jQuery('#member_project_role_id').val('').trigger('change');
            } else {
                document.getElementById('member_employee_id').value = '';
                document.getElementById('member_project_role_id').value = '';
            }
        });

        var memberBaseUrl = "{{ url('admin/projects/' . $project->id . '/team') }}";
        jQuery(document).on('click', '.btnDeleteMember', function () {
            var btn = this;
            document.getElementById('deleteForm').action = memberBaseUrl + '/' + btn.dataset.id;
            document.getElementById('delete_name').textContent = btn.dataset.name;
            document.getElementById('deleteModalTitle').textContent = 'Hapus Anggota';
            document.getElementById('deleteMethod').value = 'DELETE';

            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteModal'));
            modal.show();
        });
    });
</script>
@endpush
