@extends('layouts.master')

@section('page_title', 'Dashboard Project Manager')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Dashboard Project Manager</h3>
    </div>

    @if($shouldWarnNoReport)
        <div class="alert alert-warning d-flex align-items-center mb-5" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-3"></i>
            <div>
                <div class="fw-bold">Belum ada Daily Report hari ini.</div>
                <div class="text-muted">Pastikan kamu membuat laporan harian untuk proyek yang kamu handle.</div>
            </div>
        </div>
    @endif

    <div class="row g-5 mb-5">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Proyek Aktif</div>
                    <div class="fs-2hx fw-bold">{{ $activeProjectsCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Total Anggota Tim</div>
                    <div class="fs-2hx fw-bold">{{ $totalTeamMembers }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Task Berjalan</div>
                    <div class="fs-2hx fw-bold">{{ $runningTasksCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Manpower Hari Ini</div>
                    <div class="fs-2hx fw-bold">{{ $totalManpowerToday }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Daily Reports</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Tanggal</th>
                                    <th>Proyek</th>
                                    <th>Cuaca</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentReports as $r)
                                    <tr>
                                        <td>{{ $r->report_date }}</td>
                                        <td>{{ $r->projectManager?->project?->project_name ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-light-primary">{{ $r->weather_condition ?? '-' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('pm.daily-reports.show', $r->id) }}" class="btn btn-sm btn-light">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada laporan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-5">
                <div class="card-header">
                    <h5 class="card-title mb-0">Distribusi Manpower (Hari Ini)</h5>
                </div>
                <div class="card-body">
                    <div style="height: 260px;">
                        <canvas id="manpowerChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ringkasan Manpower</h5>
                </div>
                <div class="card-body">
                    @if($manpowerByRole->count() > 0)
                        @foreach($manpowerByRole as $row)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold">{{ $row->role_name }}</div>
                                <span class="badge badge-light-primary">{{ $row->total }} orang</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted">Belum ada log tenaga kerja hari ini.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ctx = document.getElementById('manpowerChart');
        if (!ctx || typeof Chart === 'undefined') return;

        var chartData = @json($manpowerChart);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    data: chartData.data || [],
                    backgroundColor: [
                        '#3E97FF', '#50CD89', '#F1416C', '#FFC700', '#7239EA', '#00A3FF', '#E4E6EF'
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    });
</script>
@endpush
