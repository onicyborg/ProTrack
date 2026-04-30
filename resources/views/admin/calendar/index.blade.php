@extends('layouts.master')

@section('page_title', 'Monitoring Kalender')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Monitoring Kalender</h3>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background-color: #3E97FF;">&nbsp;</span>
                <span class="text-muted">Rentang Proyek</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background-color: #50CD89;">&nbsp;</span>
                <span class="text-muted">Daily Report</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Kalender</div>
            <div class="card-toolbar d-flex flex-nowrap align-items-center gap-2" style="min-width: 320px;">
                <button type="button" class="btn btn-light-primary flex-shrink-0" id="btnOpenDownloadRange" data-bs-toggle="modal" data-bs-target="#downloadRangeModal">
                    <i class="bi bi-download me-2"></i>Download Report
                </button>
                <select id="project_filter" class="form-select select2-calendar w-auto" style="min-width: 260px;" data-placeholder="Filter Proyek">
                    <option value="">Semua Proyek</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body">
            <div id="kt_calendar_admin"></div>
        </div>
    </div>

    <div class="modal fade" id="downloadRangeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="GET" action="{{ route('admin.calendar.download-daily-reports') }}" id="downloadRangeForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Download Daily Report</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label">Proyek</label>
                                <select name="project_id" id="download_project_id" class="form-select">
                                    <option value="">Semua Proyek</option>
                                    @foreach ($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" id="download_start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Tanggal Selesai</label>
                                <input type="date" class="form-control" name="end_date" id="download_end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download me-2"></i>Download
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dailyReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Daily Report</h5>
                    <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="text-muted mb-1">Proyek</div>
                            <div class="fw-semibold" id="dr_project">-</div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="text-muted mb-1">Tanggal</div>
                            <div class="fw-semibold" id="dr_date">-</div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="text-muted mb-1">Cuaca</div>
                            <div class="fw-semibold" id="dr_weather">-</div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="text-muted mb-1">Waktu</div>
                            <div class="fw-semibold" id="dr_weather_time">-</div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="text-muted mb-1">Supervisor</div>
                            <div class="fw-semibold" id="dr_supervisor">-</div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="text-muted mb-1">Pelaksana</div>
                            <div class="fw-semibold" id="dr_executor">-</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted mb-1">Catatan</div>
                            <div class="border rounded p-3" id="dr_notes">-</div>
                        </div>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('kt_calendar_admin');
        if (!calendarEl || !window.FullCalendar) return;

        var eventsUrl = @json(route('admin.calendar.events'));
        var projectsShowBaseUrl = @json(url('/admin/projects'));
        var dailyReportsShowBaseUrl = @json(url('/admin/daily-reports'));

        var filterSelect = document.getElementById('project_filter');
        var downloadProjectIdInput = document.getElementById('download_project_id');
        var btnOpenDownloadRange = document.getElementById('btnOpenDownloadRange');

        function getSelectedProjectId() {
            return filterSelect ? (filterSelect.value || '') : '';
        }

        function syncDownloadProjectId() {
            if (downloadProjectIdInput) {
                downloadProjectIdInput.value = getSelectedProjectId();
            }
        }

        if (btnOpenDownloadRange) {
            btnOpenDownloadRange.addEventListener('click', function () {
                syncDownloadProjectId();
            });
        }

        if (window.$ && filterSelect) {
            window.$(filterSelect).select2({
                width: '100%',
                placeholder: window.$(filterSelect).data('placeholder') || 'Filter Proyek',
                allowClear: true,
                dropdownParent: window.$('#kt_app_content').length ? window.$('#kt_app_content') : window.$(document.body)
            });

            window.$(filterSelect).on('change', function () {
                calendar.refetchEvents();
            });
        } else if (filterSelect) {
            filterSelect.addEventListener('change', function () {
                calendar.refetchEvents();
            });
        }

        var modalEl = document.getElementById('dailyReportModal');
        var modalInstance = modalEl ? new bootstrap.Modal(modalEl) : null;

        function setText(id, value) {
            var el = document.getElementById(id);
            if (el) el.textContent = value || '-';
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'bootstrap5',
            initialView: 'dayGridMonth',
            height: 800,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: function (fetchInfo, successCallback, failureCallback) {
                var params = new URLSearchParams();
                params.set('start', fetchInfo.startStr);
                params.set('end', fetchInfo.endStr);

                var projectId = getSelectedProjectId();
                if (projectId) params.set('project_id', projectId);

                fetch(eventsUrl + '?' + params.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) { successCallback(data || []); })
                    .catch(function (err) { failureCallback(err); });
            },
            eventClick: function (info) {
                var props = info.event.extendedProps || {};

                if (props.type === 'project') {
                    if (props.model_id) {
                        window.location.href = projectsShowBaseUrl + '/' + props.model_id;
                    }
                    return;
                }

                if (props.type === 'report') {
                    if (props.model_id) {
                        window.location.href = dailyReportsShowBaseUrl + '/' + props.model_id;
                    } else if (modalInstance) {
                        setText('dr_project', props.project_name);
                        setText('dr_date', props.report_date);
                        setText('dr_weather', props.weather_condition);
                        setText('dr_weather_time', props.weather_time);
                        setText('dr_supervisor', props.supervisor_name);
                        setText('dr_executor', props.executor_name);
                        setText('dr_notes', props.weather_notes);
                        modalInstance.show();
                    }
                }
            }
        });

        calendar.render();
    });
</script>
@endpush
