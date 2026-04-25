@extends('layouts.master')

@section('page_title', 'Ruang Kerja Proyek')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-1">{{ $project->project_name }}</h3>
            <div class="text-muted">{{ $project->client?->client_name ?? '-' }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.projects.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ route('pm.daily-reports.index', ['project_id' => $project->id]) }}" class="btn btn-primary">
                <i class="bi bi-journal-text me-2"></i>Laporan Harian
            </a>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="text-muted">Tanggal Mulai</div>
                    <div class="fw-semibold">{{ $project->start_date ?? '-' }}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-muted">Tanggal Selesai</div>
                    <div class="fw-semibold">{{ $project->end_date ?? '-' }}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-muted">Status</div>
                    <div class="fw-semibold">{{ $project->status ?? '-' }}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-muted">Kode Rekening</div>
                    <div class="fw-semibold">{{ $project->account_code ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab_material">Material</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab_equipment">Peralatan</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab_tasks">Manajemen Tugas (WBS)</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab_material" role="tabpanel">
            <div class="card mb-5">
                <div class="card-header align-items-center">
                    <h5 class="card-title mb-0">Katalog Material</h5>
                    <div class="card-toolbar">
                        <button class="btn btn-primary" id="btnAddMaterial" data-bs-toggle="modal" data-bs-target="#materialModal">
                            <i class="bi bi-plus-lg me-2"></i>Tambah Material
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="materials_table" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Nama Material</th>
                                    <th>Satuan</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($project->materials as $material)
                                    <tr>
                                        <td>{{ $material->material_name }}</td>
                                        <td>{{ $material->unit }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-primary btnEditMaterial"
                                                data-id="{{ $material->id }}"
                                                data-name="{{ $material->material_name }}"
                                                data-unit="{{ $material->unit }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light-danger btnDeleteMaterial"
                                                data-id="{{ $material->id }}"
                                                data-name="{{ $material->material_name }}">
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

        <div class="tab-pane fade" id="tab_equipment" role="tabpanel">
            <div class="card mb-5">
                <div class="card-header align-items-center">
                    <h5 class="card-title mb-0">Katalog Peralatan</h5>
                    <div class="card-toolbar">
                        <button class="btn btn-primary" id="btnAddEquipment" data-bs-toggle="modal" data-bs-target="#equipmentModal">
                            <i class="bi bi-plus-lg me-2"></i>Tambah Peralatan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="equipments_table" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Nama Alat</th>
                                    <th>Satuan</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($project->equipments as $equipment)
                                    <tr>
                                        <td>{{ $equipment->equipment_name }}</td>
                                        <td>{{ $equipment->unit }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-primary btnEditEquipment"
                                                data-id="{{ $equipment->id }}"
                                                data-name="{{ $equipment->equipment_name }}"
                                                data-unit="{{ $equipment->unit }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light-danger btnDeleteEquipment"
                                                data-id="{{ $equipment->id }}"
                                                data-name="{{ $equipment->equipment_name }}">
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
        <div class="tab-pane fade" id="tab_tasks" role="tabpanel">
            <div class="card mb-5">
                <div class="card-header align-items-center">
                    <h5 class="card-title mb-0">Manajemen Tugas (WBS)</h5>
                    <div class="card-toolbar">
                        <button class="btn btn-primary" id="btnAddTask" data-bs-toggle="modal" data-bs-target="#taskModal">
                            <i class="bi bi-plus-lg me-2"></i>Tambah Task
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tasks_table" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Nama Tugas</th>
                                    <th>Durasi</th>
                                    <th>Anggota Tim</th>
                                    <th>Sub Task</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($project->tasks as $task)
                                    @php
                                        $taskMembers = $task->assignments
                                            ->map(fn($a) => $a->employee)
                                            ->filter()
                                            ->values();
                                    @endphp
                                    <tr>
                                        <td>{{ $task->task_name }}</td>
                                        <td>{{ $task->start_date }} - {{ $task->end_date }}</td>
                                        <td>
                                            @if ($taskMembers->count() > 0)
                                                @foreach ($taskMembers as $m)
                                                    <span class="badge badge-light-primary me-1 mb-1">{{ $m->employee_name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif

                                            <button type="button" class="btn btn-icon btn-sm btn-light ms-2 btnManageTaskTeam"
                                                data-id="{{ $task->id }}"
                                                data-name="{{ $task->task_name }}"
                                                data-employee_ids='@json($taskMembers->pluck("id")->values())'>
                                                <i class="bi bi-people"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-light btnManageSubtasks"
                                                data-id="{{ $task->id }}"
                                                data-name="{{ $task->task_name }}">
                                                Kelola ({{ $task->subtasks->count() }})
                                            </button>
                                            <div class="d-none" id="subtasks_json_{{ $task->id }}">@json($task->subtasks->map(fn($s) => ['id' => $s->id, 'name' => $s->subtask_name])->values())</div>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-primary btnEditTask"
                                                data-id="{{ $task->id }}"
                                                data-name="{{ $task->task_name }}"
                                                data-start_date="{{ $task->start_date }}"
                                                data-end_date="{{ $task->end_date }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light-danger btnDeleteTask"
                                                data-id="{{ $task->id }}"
                                                data-name="{{ $task->task_name }}">
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
    </div>

    <div class="modal fade" id="materialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="materialForm" method="POST" action="{{ route('pm.projects.materials.store', $project->id) }}">
                    @csrf
                    <input type="hidden" name="_method" id="materialFormMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="materialModalTitle">Tambah Material</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label required">Nama Material</label>
                            <input type="text" name="material_name" id="material_name" class="form-control" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label required">Satuan</label>
                            <input type="text" name="unit" id="material_unit" class="form-control" required />
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

    <div class="modal fade" id="equipmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="equipmentForm" method="POST" action="{{ route('pm.projects.equipments.store', $project->id) }}">
                    @csrf
                    <input type="hidden" name="_method" id="equipmentFormMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="equipmentModalTitle">Tambah Peralatan</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label required">Nama Alat</label>
                            <input type="text" name="equipment_name" id="equipment_name" class="form-control" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label required">Satuan</label>
                            <input type="text" name="unit" id="equipment_unit" class="form-control" required />
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
                    <input type="hidden" name="_method" value="DELETE">
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Data</h5>
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

    <div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="taskForm" method="POST" action="{{ route('pm.projects.tasks.store', $project->id) }}">
                    @csrf
                    <input type="hidden" name="_method" id="taskFormMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="taskModalTitle">Tambah Task</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label required">Nama Task</label>
                            <input type="text" name="task_name" id="task_name" class="form-control" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label required">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="task_start_date" class="form-control" @if($project->start_date) min="{{ $project->start_date }}" @endif @if($project->end_date) max="{{ $project->end_date }}" @endif required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label required">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="task_end_date" class="form-control" @if($project->start_date) min="{{ $project->start_date }}" @endif @if($project->end_date) max="{{ $project->end_date }}" @endif required />
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

    <div class="modal fade" id="taskTeamModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="taskTeamForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Delegasi Tim - <span id="taskTeamTitle">-</span></h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label">Anggota Tim</label>
                            <select name="employee_ids[]" id="task_employee_ids" class="form-select" multiple>
                                @foreach ($teamMembers as $m)
                                    <option value="{{ $m->id }}">{{ $m->employee_name }}</option>
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

    <div class="modal fade" id="subtaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sub Task - <span id="subtaskTaskTitle">-</span></h5>
                    <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="subtaskForm" method="POST">
                        @csrf
                        <div class="row g-3 align-items-end mb-5">
                            <div class="col-md-10">
                                <label class="form-label required">Nama Sub Task</label>
                                <input type="text" name="subtask_name" id="subtask_name" class="form-control" required />
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Tambah</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Nama</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="subtaskListBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var projectId = "{{ $project->id }}";

        var baseProjectUrl = "{{ url('pm/projects') }}";

        var csrfToken = "{{ csrf_token() }}";

        var tabStorageKey = 'pm_project_active_tab_' + projectId;

        function showTab(href) {
            if (!href) return;

            var trigger = document.querySelector('a[data-bs-toggle="tab"][href="' + href + '"]');
            if (!trigger) return;

            bootstrap.Tab.getOrCreateInstance(trigger).show();
        }

        var initialTab = window.location.hash;
        if (initialTab !== '#tab_material' && initialTab !== '#tab_equipment' && initialTab !== '#tab_tasks') {
            initialTab = localStorage.getItem(tabStorageKey);
        }

        showTab(initialTab);

        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(function (el) {
            el.addEventListener('shown.bs.tab', function (event) {
                var href = event.target.getAttribute('href');
                if (!href) return;

                localStorage.setItem(tabStorageKey, href);
                window.history.replaceState(null, '', window.location.pathname + window.location.search + href);

                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
                    if (href === '#tab_material' && window.jQuery.fn.dataTable.isDataTable('#materials_table')) {
                        window.jQuery('#materials_table').DataTable().columns.adjust();
                    }

                    if (href === '#tab_equipment' && window.jQuery.fn.dataTable.isDataTable('#equipments_table')) {
                        window.jQuery('#equipments_table').DataTable().columns.adjust();
                    }

                    if (href === '#tab_tasks' && window.jQuery.fn.dataTable.isDataTable('#tasks_table')) {
                        window.jQuery('#tasks_table').DataTable().columns.adjust();
                    }
                }
            });
        });

        function initTables() {
            if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable)) return;

            if (window.jQuery('#materials_table').length) {
                window.jQuery('#materials_table').DataTable({ pageLength: 10, ordering: true });
            }

            if (window.jQuery('#equipments_table').length) {
                window.jQuery('#equipments_table').DataTable({ pageLength: 10, ordering: true });
            }

            if (window.jQuery('#tasks_table').length) {
                window.jQuery('#tasks_table').DataTable({ pageLength: 10, ordering: true });
            }
        }

        initTables();

        var materialForm = document.getElementById('materialForm');
        var materialFormMethod = document.getElementById('materialFormMethod');
        var materialModalTitle = document.getElementById('materialModalTitle');

        var equipmentForm = document.getElementById('equipmentForm');
        var equipmentFormMethod = document.getElementById('equipmentFormMethod');
        var equipmentModalTitle = document.getElementById('equipmentModalTitle');

        var deleteForm = document.getElementById('deleteForm');

        var taskForm = document.getElementById('taskForm');
        var taskFormMethod = document.getElementById('taskFormMethod');
        var taskModalTitle = document.getElementById('taskModalTitle');

        var taskTeamForm = document.getElementById('taskTeamForm');
        var taskTeamTitle = document.getElementById('taskTeamTitle');
        var taskEmployeeSelect = document.getElementById('task_employee_ids');

        var subtaskForm = document.getElementById('subtaskForm');
        var subtaskTaskTitle = document.getElementById('subtaskTaskTitle');
        var subtaskListBody = document.getElementById('subtaskListBody');
        var subtaskNameInput = document.getElementById('subtask_name');

        var currentSubtaskTaskId = null;

        function initTaskPickers() {
            if (typeof flatpickr === 'function') {
                var startEl = document.getElementById('task_start_date');
                var endEl = document.getElementById('task_end_date');

                if (!startEl || !endEl) return;
                if (startEl.type !== 'text' || endEl.type !== 'text') return;

                if (startEl && startEl._flatpickr) startEl._flatpickr.destroy();
                if (endEl && endEl._flatpickr) endEl._flatpickr.destroy();

                flatpickr('#task_start_date', { dateFormat: 'Y-m-d' });
                flatpickr('#task_end_date', { dateFormat: 'Y-m-d' });
            }
        }

        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            window.jQuery('#task_employee_ids').select2({
                dropdownParent: window.jQuery('#taskTeamModal'),
                width: '100%'
            });
        }

        initTaskPickers();

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderSubtasks(taskId, subtasks) {
            if (!subtaskListBody) return;
            subtaskListBody.innerHTML = '';

            if (!Array.isArray(subtasks) || subtasks.length === 0) {
                subtaskListBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Belum ada sub task</td></tr>';
                return;
            }

            subtasks.forEach(function (s) {
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + escapeHtml(s.name) + '</td>' +
                    '<td class="text-end">' +
                        '<button type="button" class="btn btn-sm btn-light-danger btnDeleteSubtask" data-task_id="' + taskId + '" data-id="' + s.id + '"><i class="bi bi-trash"></i></button>' +
                    '</td>';
                subtaskListBody.appendChild(tr);
            });
        }

        function updateSubtaskCache(taskId, subtasks) {
            var jsonEl = document.getElementById('subtasks_json_' + taskId);
            if (jsonEl) {
                jsonEl.textContent = JSON.stringify(subtasks);
            }

            var btn = document.querySelector('#tasks_table .btnManageSubtasks[data-id="' + taskId + '"]');
            if (btn) {
                btn.textContent = 'Kelola (' + (Array.isArray(subtasks) ? subtasks.length : 0) + ')';
            }
        }

        function swalConfirmDelete() {
            if (!(window.Swal && typeof window.Swal.fire === 'function')) {
                if (window.toastr && typeof window.toastr.error === 'function') {
                    window.toastr.error('SweetAlert belum tersedia.');
                }
                return Promise.resolve(false);
            }

            return window.Swal.fire({
                icon: 'warning',
                title: 'Hapus sub task?',
                text: 'Data yang dihapus tidak dapat dikembalikan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
            }).then(function (r) { return r.isConfirmed; });
        }

        function swalError(message) {
            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({ icon: 'error', title: 'Gagal', text: message || 'Terjadi kesalahan.' });
                return;
            }

            if (window.toastr && typeof window.toastr.error === 'function') {
                window.toastr.error(message || 'Terjadi kesalahan.');
            }
        }

        function swalSuccess(message) {
            if (window.toastr && typeof window.toastr.success === 'function') {
                window.toastr.success(message);
                return;
            }

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({ icon: 'success', title: 'Berhasil', text: message, timer: 1200, showConfirmButton: false });
            }
        }

        if (subtaskForm) {
            subtaskForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                if (!currentSubtaskTaskId) return;

                var url = baseProjectUrl + '/' + projectId + '/tasks/' + currentSubtaskTaskId + '/subtasks';

                try {
                    var resp = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            subtask_name: subtaskNameInput ? subtaskNameInput.value : '',
                        })
                    });

                    if (resp.status === 422) {
                        var data422 = await resp.json();
                        var msg = (data422 && data422.message) ? data422.message : 'Validasi gagal.';
                        swalError(msg);
                        return;
                    }

                    if (!resp.ok) {
                        swalError('Gagal menambahkan sub task.');
                        return;
                    }

                    var data = await resp.json();
                    renderSubtasks(currentSubtaskTaskId, data.subtasks || []);
                    updateSubtaskCache(currentSubtaskTaskId, data.subtasks || []);
                    if (subtaskNameInput) subtaskNameInput.value = '';
                    swalSuccess(data.message || 'Sub task berhasil ditambahkan.');
                } catch (err) {
                    swalError('Gagal menambahkan sub task.');
                }
            });
        }

        if (subtaskListBody) {
            subtaskListBody.addEventListener('click', async function (e) {
                var btn = e.target && e.target.closest ? e.target.closest('.btnDeleteSubtask') : null;
                if (!btn) return;

                var taskId = btn.dataset.task_id;
                var subtaskId = btn.dataset.id;
                if (!taskId || !subtaskId) return;

                var confirmed = await swalConfirmDelete();
                if (!confirmed) return;

                var url = baseProjectUrl + '/' + projectId + '/tasks/' + taskId + '/subtasks/' + subtaskId;
                try {
                    var resp = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    if (!resp.ok) {
                        swalError('Gagal menghapus sub task.');
                        return;
                    }

                    var data = await resp.json();
                    renderSubtasks(taskId, data.subtasks || []);
                    updateSubtaskCache(taskId, data.subtasks || []);
                    swalSuccess(data.message || 'Sub task berhasil dihapus.');
                } catch (err) {
                    swalError('Gagal menghapus sub task.');
                }
            });
        }

        document.getElementById('btnAddTask').addEventListener('click', function () {
            taskForm.action = "{{ route('pm.projects.tasks.store', $project->id) }}";
            taskFormMethod.value = 'POST';
            taskModalTitle.textContent = 'Tambah Task';
            taskForm.reset();
            initTaskPickers();
        });

        document.getElementById('btnAddMaterial').addEventListener('click', function () {
            materialForm.action = "{{ route('pm.projects.materials.store', $project->id) }}";
            materialFormMethod.value = 'POST';
            materialModalTitle.textContent = 'Tambah Material';
            materialForm.reset();
        });

        if (window.jQuery) {
            window.jQuery('#materials_table').on('click', '.btnEditMaterial', function () {
                var btn = this;
                materialForm.action = baseProjectUrl + '/' + projectId + '/materials/' + btn.dataset.id;
                materialFormMethod.value = 'PUT';
                materialModalTitle.textContent = 'Edit Material';

                document.getElementById('material_name').value = btn.dataset.name || '';
                document.getElementById('material_unit').value = btn.dataset.unit || '';

                bootstrap.Modal.getOrCreateInstance(document.getElementById('materialModal')).show();
            });

            window.jQuery('#materials_table').on('click', '.btnDeleteMaterial', function () {
                var btn = this;
                deleteForm.action = baseProjectUrl + '/' + projectId + '/materials/' + btn.dataset.id;
                document.getElementById('delete_name').textContent = btn.dataset.name || '-';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteModal')).show();
            });
        }

        document.getElementById('btnAddEquipment').addEventListener('click', function () {
            equipmentForm.action = "{{ route('pm.projects.equipments.store', $project->id) }}";
            equipmentFormMethod.value = 'POST';
            equipmentModalTitle.textContent = 'Tambah Peralatan';
            equipmentForm.reset();
        });

        if (window.jQuery) {
            window.jQuery('#equipments_table').on('click', '.btnEditEquipment', function () {
                var btn = this;
                equipmentForm.action = baseProjectUrl + '/' + projectId + '/equipments/' + btn.dataset.id;
                equipmentFormMethod.value = 'PUT';
                equipmentModalTitle.textContent = 'Edit Peralatan';

                document.getElementById('equipment_name').value = btn.dataset.name || '';
                document.getElementById('equipment_unit').value = btn.dataset.unit || '';

                bootstrap.Modal.getOrCreateInstance(document.getElementById('equipmentModal')).show();
            });

            window.jQuery('#equipments_table').on('click', '.btnDeleteEquipment', function () {
                var btn = this;
                deleteForm.action = baseProjectUrl + '/' + projectId + '/equipments/' + btn.dataset.id;
                document.getElementById('delete_name').textContent = btn.dataset.name || '-';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteModal')).show();
            });

            window.jQuery('#tasks_table').on('click', '.btnEditTask', function () {
                var btn = this;
                taskForm.action = baseProjectUrl + '/' + projectId + '/tasks/' + btn.dataset.id;
                taskFormMethod.value = 'PUT';
                taskModalTitle.textContent = 'Edit Task';

                document.getElementById('task_name').value = btn.dataset.name || '';
                document.getElementById('task_start_date').value = btn.dataset.start_date || '';
                document.getElementById('task_end_date').value = btn.dataset.end_date || '';

                initTaskPickers();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('taskModal')).show();
            });

            window.jQuery('#tasks_table').on('click', '.btnDeleteTask', function () {
                var btn = this;
                deleteForm.action = baseProjectUrl + '/' + projectId + '/tasks/' + btn.dataset.id;
                document.getElementById('delete_name').textContent = btn.dataset.name || '-';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteModal')).show();
            });

            window.jQuery('#tasks_table').on('click', '.btnManageTaskTeam', function () {
                var btn = this;
                taskTeamTitle.textContent = btn.dataset.name || '-';
                taskTeamForm.action = baseProjectUrl + '/' + projectId + '/tasks/' + btn.dataset.id + '/assignments';

                var employeeIds = [];
                try {
                    employeeIds = JSON.parse(btn.dataset.employee_ids || '[]');
                } catch (e) {
                    employeeIds = [];
                }

                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                    window.jQuery(taskEmployeeSelect).val(employeeIds).trigger('change');
                } else {
                    Array.from(taskEmployeeSelect.options).forEach(function (opt) {
                        opt.selected = employeeIds.indexOf(opt.value) !== -1;
                    });
                }

                bootstrap.Modal.getOrCreateInstance(document.getElementById('taskTeamModal')).show();
            });

            window.jQuery('#tasks_table').on('click', '.btnManageSubtasks', function () {
                var btn = this;
                var taskId = btn.dataset.id;

                if (!taskId) return;

                currentSubtaskTaskId = taskId;

                subtaskTaskTitle.textContent = btn.dataset.name || '-';
                subtaskForm.action = baseProjectUrl + '/' + projectId + '/tasks/' + taskId + '/subtasks';

                if (subtaskNameInput) {
                    subtaskNameInput.value = '';
                    subtaskNameInput.focus();
                }

                var jsonEl = document.getElementById('subtasks_json_' + taskId);
                var subtasks = [];
                try {
                    subtasks = JSON.parse(jsonEl ? jsonEl.textContent : '[]');
                } catch (e) {
                    subtasks = [];
                }

                renderSubtasks(taskId, subtasks);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('subtaskModal')).show();
            });
        }
    });
</script>
@endpush
