@extends('layouts.master')

@section('page_title', 'Proyek Saya')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Proyek Saya</h3>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="pm_project_table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Nama Proyek</th>
                            <th>Klien</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Material</th>
                            <th>Peralatan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td>{{ $project->project_name }}</td>
                                <td>{{ $project->client?->client_name ?? '-' }}</td>
                                <td>{{ $project->start_date ?? '-' }}</td>
                                <td>{{ $project->end_date ?? '-' }}</td>
                                <td>{{ $project->materials_count ?? 0 }}</td>
                                <td>{{ $project->equipments_count ?? 0 }}</td>
                                <td class="text-end">
                                    <a href="{{ route('pm.projects.show', $project->id) }}" class="btn btn-sm btn-light">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                    </a>
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
            window.jQuery('#pm_project_table').DataTable({
                pageLength: 10,
                ordering: true,
            });
        }
    });
</script>
@endpush
