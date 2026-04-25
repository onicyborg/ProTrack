@extends('layouts.master')

@section('page_title', 'Detail Laporan Harian')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-1">Detail Laporan Harian</h3>
            <div class="text-muted">
                {{ $dailyReport->projectManager?->project?->project_name ?? '-' }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.calendar.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ route('admin.daily-reports.download-pdf', $dailyReport->id) }}" class="btn btn-light-danger">
                <i class="bi bi-file-earmark-pdf me-2"></i>Unduh PDF
            </a>
        </div>
    </div>

    <div class="row g-5 mb-5">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Header</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-muted">Tanggal</div>
                            <div class="fw-semibold">{{ $dailyReport->report_date }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted">Cuaca</div>
                            <div class="fw-semibold">{{ $dailyReport->weather_condition ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted">Waktu Cuaca</div>
                            <div class="fw-semibold">{{ $dailyReport->weather_time ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted">Catatan Cuaca</div>
                            <div class="fw-semibold">{{ $dailyReport->weather_notes ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Supervisor</div>
                            <div class="fw-semibold">{{ $dailyReport->supervisor?->employee_name ?? ($dailyReport->supervisor_name ?? '-') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Pelaksana</div>
                            <div class="fw-semibold">{{ $dailyReport->executor?->employee_name ?? ($dailyReport->executor_name ?? '-') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Manpower</h5>
                </div>
                <div class="card-body">
                    @if($manpowerByRole->count() > 0)
                        @php
                            $totalManpower = $manpowerByRole->sum('total');
                        @endphp
                        @foreach($manpowerByRole as $row)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold">{{ $row->role_name }}</div>
                                <span class="badge badge-light-primary">{{ $row->total }} orang</span>
                            </div>
                        @endforeach

                        <div class="separator my-4"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-bold">Total</div>
                            <span class="badge badge-light-success">{{ $totalManpower }} orang</span>
                        </div>
                    @else
                        <div class="text-muted">Belum ada log tenaga kerja pada tanggal ini.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header">
            <h5 class="card-title mb-0">Pekerjaan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Task</th>
                            <th class="text-end">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyReport->works as $w)
                            <tr>
                                <td>{{ $w->task?->task_name ?? '-' }}</td>
                                <td class="text-end">{{ $w->volume }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header">
            <h5 class="card-title mb-0">Material</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Material</th>
                            <th class="text-end">Volume</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyReport->materials as $m)
                            <tr>
                                <td>{{ $m->projectMaterial?->material_name ?? '-' }}</td>
                                <td class="text-end">{{ $m->volume }}</td>
                                <td>{{ $m->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Alat</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Alat</th>
                            <th class="text-end">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyReport->equipments as $e)
                            <tr>
                                <td>{{ $e->projectEquipment?->equipment_name ?? '-' }}</td>
                                <td class="text-end">{{ $e->volume }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
