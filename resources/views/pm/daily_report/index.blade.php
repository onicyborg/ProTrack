@extends('layouts.master')

@section('page_title', 'Laporan Harian')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Laporan Harian</h3>
        <div class="d-flex gap-2">
            @if($selectedProjectId)
                <a href="{{ route('pm.daily-reports.create', ['project_id' => $selectedProjectId]) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Tambah Laporan
                </a>
            @endif
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <form method="GET" action="{{ route('pm.daily-reports.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Proyek</label>
                    <select name="project_id" id="project_id" class="form-select">
                        <option value="" @selected(!$selectedProjectId)>Semua Proyek</option>
                        @foreach($pmLinks as $link)
                            <option value="{{ $link->project_id }}" @selected($selectedProjectId == $link->project_id)>
                                {{ $link->project?->project_name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-light-primary">
                            <i class="bi bi-funnel me-2"></i>Tampilkan
                        </button>
                        <a href="{{ route('pm.daily-reports.index') }}" class="btn btn-light">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="daily_reports_table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Tanggal</th>
                            <th>Proyek</th>
                            <th>Cuaca</th>
                            <th>Supervisor</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $r)
                            <tr>
                                <td>{{ $r->report_date }}</td>
                                <td>{{ $r->projectManager?->project?->project_name ?? '-' }}</td>
                                <td>{{ $r->weather_condition ?? '-' }}</td>
                                <td>{{ $r->supervisor?->employee_name ?? ($r->supervisor_name ?? '-') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('pm.daily-reports.show', $r->id) }}" class="btn btn-sm btn-light-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('pm.daily-reports.edit', $r->id) }}" class="btn btn-sm btn-light-warning">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('pm.daily-reports.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus laporan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
            if (window.jQuery('#daily_reports_table').length) {
                window.jQuery('#daily_reports_table').DataTable({ pageLength: 10, ordering: true });
            }
        }

        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            window.jQuery('#project_id').select2({ width: '100%' });
        }
    });
</script>
@endpush
