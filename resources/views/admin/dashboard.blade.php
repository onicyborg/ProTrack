@extends('layouts.master')

@section('page_title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Dashboard</h3>
    </div>

    <div class="row g-5 mb-5">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Total Clients</div>
                    <div class="fs-2hx fw-bold">{{ $totalClients }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Total Employees</div>
                    <div class="fs-2hx fw-bold">{{ $totalEmployees }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Projects Active</div>
                    <div class="fs-2hx fw-bold">{{ $totalActiveProjects }}</div>
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
                    <h5 class="card-title mb-0">Project Overview</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Proyek</th>
                                    <th>PM</th>
                                    <th class="text-end">Tgl Selesai</th>
                                    <th class="text-end">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $p)
                                    @php
                                        $pmNames = $p->projectManagers
                                            ->map(fn($pm) => $pm->pm?->employee_name)
                                            ->filter()
                                            ->values();
                                        $percent = (int) ($p->progress_percent ?? 0);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $p->project_name }}</div>
                                            <div class="text-muted">{{ $p->client?->client_name ?? '-' }}</div>
                                        </td>
                                        <td>
                                            @if($pmNames->count() > 0)
                                                @foreach($pmNames as $name)
                                                    <span class="badge badge-light-primary me-1 mb-1">{{ $name }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">{{ $p->end_date ?? '-' }}</td>
                                        <td class="text-end" style="min-width: 180px;">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="text-muted">{{ $p->done_tasks ?? 0 }}/{{ $p->total_tasks ?? 0 }}</span>
                                                    <span class="fw-semibold">{{ $percent }}%</span>
                                                </div>
                                                <div class="progress h-6px w-100">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Tidak ada proyek aktif.</td>
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
