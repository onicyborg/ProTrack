@extends('layouts.master')

@section('page_title', 'Manajemen Proyek')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Manajemen Proyek</h3>
        <button class="btn btn-primary" id="btnAddProject" data-bs-toggle="modal" data-bs-target="#projectModal">
            <i class="bi bi-plus-lg me-2"></i>Tambah Proyek
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="project_table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Nama Proyek</th>
                            <th>Klien</th>
                            <th>PM</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            @php
                                $pmNames = $project->projectManagers
                                    ->map(fn($pm) => $pm->pm?->employee_name)
                                    ->filter()
                                    ->values();

                                $pmIds = $project->projectManagers
                                    ->pluck('pm_id')
                                    ->filter()
                                    ->values();

                                $status = $project->status;
                                $statusClass = match ($status) {
                                    'active' => 'badge-light-success',
                                    'completed' => 'badge-light-primary',
                                    'on_hold' => 'badge-light-warning',
                                    default => 'badge-light-secondary',
                                };

                                $statusLabel = match ($status) {
                                    'active' => 'Active',
                                    'completed' => 'Completed',
                                    'on_hold' => 'On Hold',
                                    default => strtoupper($status),
                                };
                            @endphp
                            <tr>
                                <td>{{ $project->project_name }}</td>
                                <td>{{ $project->client?->client_name ?? '-' }}</td>
                                <td>
                                    @if ($pmNames->count() > 0)
                                        @foreach ($pmNames as $name)
                                            <span class="badge badge-light-primary me-1 mb-1">{{ $name }}</span>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-sm btn-light">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light-primary btnEditProject"
                                        data-id="{{ $project->id }}"
                                        data-project_name="{{ $project->project_name }}"
                                        data-client_id="{{ $project->client_id }}"
                                        data-account_code="{{ $project->account_code }}"
                                        data-budget_year="{{ $project->budget_year }}"
                                        data-start_date="{{ $project->start_date }}"
                                        data-end_date="{{ $project->end_date }}"
                                        data-status="{{ $project->status }}"
                                        data-pm_ids='@json($pmIds)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light-danger btnDeleteProject"
                                        data-id="{{ $project->id }}"
                                        data-name="{{ $project->project_name }}">
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

    <div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="projectForm" method="POST" action="{{ route('admin.projects.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="projectFormMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="projectModalTitle">Tambah Proyek</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label required">Nama Proyek</label>
                                <input type="text" name="project_name" id="project_name" class="form-control" required />
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label required">Klien</label>
                                <select name="client_id" id="project_client_id" class="form-select select2-client" required>
                                    <option value="">-- Pilih Klien --</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label">Kode Rekening</label>
                                <input type="text" name="account_code" id="project_account_code" class="form-control" />
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label">Tahun Anggaran</label>
                                <input type="text" name="budget_year" id="project_budget_year" class="form-control" />
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="project_start_date" class="form-control" />
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="project_end_date" class="form-control" />
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label required">Status</label>
                                <select name="status" id="project_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="on_hold">On Hold</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label required">Assign PM</label>
                                <select name="pm_ids[]" id="project_pm_ids" class="form-select select2-pm" multiple required>
                                    @foreach ($pms as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->employee_name }}</option>
                                    @endforeach
                                </select>
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
                        <h5 class="modal-title">Hapus Proyek</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus proyek <strong id="delete_name">-</strong>?</p>
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
        jQuery('#project_table').DataTable({
            pageLength: 10,
            ordering: true,
        });

        var projectForm = document.getElementById('projectForm');
        var projectFormMethod = document.getElementById('projectFormMethod');
        var projectModalTitle = document.getElementById('projectModalTitle');
        var projectModalEl = document.getElementById('projectModal');

        var storeUrl = "{{ route('admin.projects.store') }}";
        var baseUrl = "{{ url('admin/projects') }}";

        function initSelect2() {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('.select2-client').select2({
                    dropdownParent: window.jQuery('#projectModal'),
                    width: '100%'
                });

                window.jQuery('.select2-pm').select2({
                    dropdownParent: window.jQuery('#projectModal'),
                    width: '100%'
                });
            }
        }

        if (window.jQuery) {
            window.jQuery(projectModalEl).on('shown.bs.modal', function () {
                initSelect2();
            });
        }

        document.getElementById('btnAddProject').addEventListener('click', function () {
            projectForm.action = storeUrl;
            projectFormMethod.value = 'POST';
            projectModalTitle.textContent = 'Tambah Proyek';
            projectForm.reset();

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('#project_client_id').val('').trigger('change');
                window.jQuery('#project_pm_ids').val([]).trigger('change');
            }
        });

        jQuery('#project_table').on('click', '.btnEditProject', function () {
            var btn = this;
            projectForm.action = baseUrl + '/' + btn.dataset.id;
            projectFormMethod.value = 'PUT';
            projectModalTitle.textContent = 'Edit Proyek';

            document.getElementById('project_name').value = btn.dataset.project_name || '';
            document.getElementById('project_account_code').value = btn.dataset.account_code || '';
            document.getElementById('project_budget_year').value = btn.dataset.budget_year || '';
            document.getElementById('project_start_date').value = btn.dataset.start_date || '';
            document.getElementById('project_end_date').value = btn.dataset.end_date || '';
            document.getElementById('project_status').value = btn.dataset.status || 'active';

            var clientSelect = document.getElementById('project_client_id');
            clientSelect.value = btn.dataset.client_id || '';

            var pmIds = [];
            try {
                pmIds = JSON.parse(btn.dataset.pm_ids || '[]');
            } catch (e) {
                pmIds = [];
            }

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery(clientSelect).trigger('change');
                window.jQuery('#project_pm_ids').val(pmIds).trigger('change');
            } else {
                document.getElementById('project_pm_ids').value = pmIds;
            }

            var modal = bootstrap.Modal.getOrCreateInstance(projectModalEl);
            modal.show();
        });

        jQuery('#project_table').on('click', '.btnDeleteProject', function () {
            var btn = this;
            document.getElementById('deleteForm').action = baseUrl + '/' + btn.dataset.id;
            document.getElementById('delete_name').textContent = btn.dataset.name;

            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteModal'));
            modal.show();
        });
    });
</script>
@endpush
