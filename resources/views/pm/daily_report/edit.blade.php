@extends('layouts.master')

@section('page_title', 'Edit Laporan Harian')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-1">Edit Laporan Harian</h3>
            <div class="text-muted">{{ $project->project_name }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.daily-reports.show', $dailyReport->id) }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('pm.daily-reports.update', $dailyReport->id) }}">
        @csrf
        @method('PUT')

        <div class="card mb-5">
            <div class="card-header">
                <h5 class="card-title mb-0">Header</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label required">Tanggal</label>
                        <input type="date" name="report_date" class="form-control" value="{{ old('report_date', $dailyReport->report_date) }}" @if($project->start_date) min="{{ $project->start_date }}" @endif @if($project->end_date) max="{{ $project->end_date }}" @endif required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required" for="weather_condition">Cuaca</label>
                        <select name="weather_condition" id="weather_condition" class="form-select select2-header" required>
                            <option value="">- pilih -</option>
                            @foreach(['Cerah','Hujan','Mendung'] as $w)
                                <option value="{{ $w }}" @selected(old('weather_condition', $dailyReport->weather_condition) === $w)>{{ $w }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Waktu Cuaca</label>
                        <input type="text" name="weather_time" class="form-control" value="{{ old('weather_time', $dailyReport->weather_time) }}" placeholder="Contoh: Pagi / Siang / Sore">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Catatan Cuaca</label>
                        <textarea name="weather_notes" class="form-control" rows="3">{{ old('weather_notes', $dailyReport->weather_notes) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="supervisor_id">Supervisor</label>
                        <select name="supervisor_id" id="supervisor_id" class="form-select select2-header">
                            <option value="">- pilih -</option>
                            @foreach($teamEmployees as $pe)
                                <option value="{{ $pe->employee_id }}" @selected(old('supervisor_id', $dailyReport->supervisor_id) === $pe->employee_id)>
                                    {{ $pe->employee?->employee_name ?? '-' }} - {{ $pe->projectRole?->role_name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="executor_id">Pelaksana</label>
                        <select name="executor_id" id="executor_id" class="form-select select2-header">
                            <option value="">- pilih -</option>
                            @foreach($teamEmployees as $pe)
                                <option value="{{ $pe->employee_id }}" @selected(old('executor_id', $dailyReport->executor_id) === $pe->employee_id)>
                                    {{ $pe->employee?->employee_name ?? '-' }} - {{ $pe->projectRole?->role_name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header">
                <h5 class="card-title mb-0">Kehadiran & Aktivitas Tim</h5>
            </div>
            <div class="card-body">
                @php
                    $oldTeamPresent = old('team_present');
                    $teamPresentIds = is_array($oldTeamPresent) ? $oldTeamPresent : ($teamPresentIds ?? []);

                    $oldTeamActivity = old('team_activity');
                    $teamActivityById = is_array($oldTeamActivity) ? $oldTeamActivity : ($teamActivityById ?? []);
                @endphp

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-50px">Hadir</th>
                                <th>Nama & Role</th>
                                <th>Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teamEmployees as $pe)
                                <tr>
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="team_present[]" value="{{ $pe->employee_id }}" @checked(in_array($pe->employee_id, $teamPresentIds, true))>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $pe->employee?->employee_name ?? '-' }}</div>
                                        <div class="text-muted">{{ $pe->projectRole?->role_name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="team_activity[{{ $pe->employee_id }}]" value="{{ $teamActivityById[$pe->employee_id] ?? '' }}" placeholder="Contoh: Pasang bekisting / Angkut material">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada anggota tim untuk proyek ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header align-items-center">
                <h5 class="card-title mb-0">Pekerjaan</h5>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-light-primary" id="btnAddWork">
                        <i class="bi bi-plus-lg me-2"></i>Tambah Baris
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-50">Task (WBS)</th>
                                <th class="w-25">Volume</th>
                                <th class="text-end w-25">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="worksBody">
                            @php
                                $workRows = old('works');
                                $fallbackWorks = $dailyReport->works;
                            @endphp

                            @if(is_array($workRows) && count($workRows) > 0)
                                @foreach($workRows as $i => $row)
                                    <tr class="work-row">
                                        <td>
                                            <select name="works[{{ $i }}][task_id]" class="form-select select2-task" required>
                                                <option value="">- pilih -</option>
                                                @foreach($tasks as $t)
                                                    <option value="{{ $t->id }}" @selected(($row['task_id'] ?? '') === $t->id)>{{ $t->task_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="works[{{ $i }}][volume]" class="form-control" value="{{ $row['volume'] ?? 0 }}" required>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-danger btnRemoveWork"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @foreach($fallbackWorks as $i => $w)
                                    <tr class="work-row">
                                        <td>
                                            <select name="works[{{ $i }}][task_id]" class="form-select select2-task" required>
                                                <option value="">- pilih -</option>
                                                @foreach($tasks as $t)
                                                    <option value="{{ $t->id }}" @selected($w->task_id === $t->id)>{{ $t->task_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="works[{{ $i }}][volume]" class="form-control" value="{{ $w->volume }}" required>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-danger btnRemoveWork"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header align-items-center">
                <h5 class="card-title mb-0">Material</h5>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-light-primary" id="btnAddMaterialRow">
                        <i class="bi bi-plus-lg me-2"></i>Tambah Baris
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-40">Material</th>
                                <th class="w-20">Volume</th>
                                <th class="w-30">Catatan</th>
                                <th class="text-end w-10">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="materialsBody">
                            @php
                                $matRows = old('materials');
                                $fallbackMats = $dailyReport->materials;
                            @endphp

                            @if(is_array($matRows) && count($matRows) > 0)
                                @foreach($matRows as $i => $row)
                                    <tr>
                                        <td>
                                            <select name="materials[{{ $i }}][project_material_id]" class="form-select select2-material" required>
                                                <option value="">- pilih -</option>
                                                @foreach($materials as $m)
                                                    <option value="{{ $m->id }}" @selected(($row['project_material_id'] ?? '') === $m->id)>{{ $m->material_name }} ({{ $m->unit }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="materials[{{ $i }}][volume]" class="form-control" value="{{ $row['volume'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="materials[{{ $i }}][notes]" class="form-control" value="{{ $row['notes'] ?? '' }}">
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-danger btnRemoveRow"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @foreach($fallbackMats as $i => $mRow)
                                    <tr>
                                        <td>
                                            <select name="materials[{{ $i }}][project_material_id]" class="form-select select2-material" required>
                                                <option value="">- pilih -</option>
                                                @foreach($materials as $m)
                                                    <option value="{{ $m->id }}" @selected($mRow->project_material_id === $m->id)>{{ $m->material_name }} ({{ $m->unit }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="materials[{{ $i }}][volume]" class="form-control" value="{{ $mRow->volume }}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="materials[{{ $i }}][notes]" class="form-control" value="{{ $mRow->notes }}">
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-danger btnRemoveRow"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header align-items-center">
                <h5 class="card-title mb-0">Alat</h5>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-light-primary" id="btnAddEquipmentRow">
                        <i class="bi bi-plus-lg me-2"></i>Tambah Baris
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-50">Alat</th>
                                <th class="w-25">Volume</th>
                                <th class="text-end w-25">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="equipmentsBody">
                            @php
                                $eqRows = old('equipments');
                                $fallbackEqs = $dailyReport->equipments;
                            @endphp

                            @if(is_array($eqRows) && count($eqRows) > 0)
                                @foreach($eqRows as $i => $row)
                                    <tr>
                                        <td>
                                            <select name="equipments[{{ $i }}][project_equipment_id]" class="form-select select2-equipment" required>
                                                <option value="">- pilih -</option>
                                                @foreach($equipments as $e)
                                                    <option value="{{ $e->id }}" @selected(($row['project_equipment_id'] ?? '') === $e->id)>{{ $e->equipment_name }} ({{ $e->unit }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="equipments[{{ $i }}][volume]" class="form-control" value="{{ $row['volume'] ?? 0 }}" required>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-danger btnRemoveRow"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @foreach($fallbackEqs as $i => $eRow)
                                    <tr>
                                        <td>
                                            <select name="equipments[{{ $i }}][project_equipment_id]" class="form-select select2-equipment" required>
                                                <option value="">- pilih -</option>
                                                @foreach($equipments as $e)
                                                    <option value="{{ $e->id }}" @selected($eRow->project_equipment_id === $e->id)>{{ $e->equipment_name }} ({{ $e->unit }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="equipments[{{ $i }}][volume]" class="form-control" value="{{ $eRow->volume }}" required>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-danger btnRemoveRow"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Simpan Perubahan
            </button>
        </div>
    </form>

    <template id="workRowTemplate">
        <tr class="work-row">
            <td>
                <select name="works[__INDEX__][task_id]" class="form-select select2-task" required>
                    <option value="">- pilih -</option>
                    @foreach($tasks as $t)
                        <option value="{{ $t->id }}">{{ $t->task_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="works[__INDEX__][volume]" class="form-control" value="0" required>
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-light-danger btnRemoveWork"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    </template>

    <template id="materialRowTemplate">
        <tr>
            <td>
                <select name="materials[__INDEX__][project_material_id]" class="form-select select2-material" required>
                    <option value="">- pilih -</option>
                    @foreach($materials as $m)
                        <option value="{{ $m->id }}">{{ $m->material_name }} ({{ $m->unit }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="materials[__INDEX__][volume]" class="form-control" value="0" required>
            </td>
            <td>
                <input type="text" name="materials[__INDEX__][notes]" class="form-control" value="">
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-light-danger btnRemoveRow"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    </template>

    <template id="equipmentRowTemplate">
        <tr>
            <td>
                <select name="equipments[__INDEX__][project_equipment_id]" class="form-select select2-equipment" required>
                    <option value="">- pilih -</option>
                    @foreach($equipments as $e)
                        <option value="{{ $e->id }}">{{ $e->equipment_name }} ({{ $e->unit }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="equipments[__INDEX__][volume]" class="form-control" value="0" required>
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-light-danger btnRemoveRow"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initSelect2(selector) {
            if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2)) return;
            var el = window.jQuery(selector).filter('select');
            el.each(function () {
                var s = window.jQuery(this);
                if (s.data('select2')) {
                    s.select2('destroy');
                }
            });
            var dropdownParent = window.jQuery('#kt_app_content');
            if (!dropdownParent.length) dropdownParent = window.jQuery(document.body);
            el.select2({ width: '100%', dropdownParent: dropdownParent });
        }

        initSelect2('.select2-header');
        initSelect2('.select2-task');
        initSelect2('.select2-material');
        initSelect2('.select2-equipment');

        if (window.jQuery) {
            (function () {
                var closeOnScroll = function () {
                    window.jQuery('.select2-header, .select2-task, .select2-material, .select2-equipment').select2('close');
                };

                window.jQuery(document).on('select2:open.dailyReport', function () {
                    window.addEventListener('wheel', closeOnScroll, true);
                    window.addEventListener('scroll', closeOnScroll, true);
                    window.addEventListener('touchmove', closeOnScroll, true);
                });

                window.jQuery(document).on('select2:close.dailyReport', function () {
                    window.removeEventListener('wheel', closeOnScroll, true);
                    window.removeEventListener('scroll', closeOnScroll, true);
                    window.removeEventListener('touchmove', closeOnScroll, true);
                });
            })();
        }

        var workIndex = document.querySelectorAll('#worksBody tr').length;
        var materialIndex = document.querySelectorAll('#materialsBody tr').length;
        var equipmentIndex = document.querySelectorAll('#equipmentsBody tr').length;

        function addRow(tplId, targetId, index) {
            var tpl = document.getElementById(tplId);
            var target = document.getElementById(targetId);
            if (!tpl || !target) return;

            var html = tpl.innerHTML.replaceAll('__INDEX__', String(index));
            var temp = document.createElement('tbody');
            temp.innerHTML = html.trim();
            var tr = temp.firstElementChild;
            target.appendChild(tr);

            initSelect2(tr.querySelectorAll('select'));
        }

        document.getElementById('btnAddWork')?.addEventListener('click', function () {
            addRow('workRowTemplate', 'worksBody', workIndex);
            workIndex++;
        });

        document.getElementById('btnAddMaterialRow')?.addEventListener('click', function () {
            addRow('materialRowTemplate', 'materialsBody', materialIndex);
            materialIndex++;
        });

        document.getElementById('btnAddEquipmentRow')?.addEventListener('click', function () {
            addRow('equipmentRowTemplate', 'equipmentsBody', equipmentIndex);
            equipmentIndex++;
        });

        document.body.addEventListener('click', function (e) {
            var btnWork = e.target.closest('.btnRemoveWork');
            if (btnWork) {
                var rows = document.querySelectorAll('#worksBody tr.work-row');
                if (rows.length <= 1) return;
                btnWork.closest('tr')?.remove();
                return;
            }

            var btnRow = e.target.closest('.btnRemoveRow');
            if (btnRow) {
                btnRow.closest('tr')?.remove();
            }
        });
    });
</script>
@endpush
