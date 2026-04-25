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
            <div class="card-toolbar" style="min-width: 320px;">
                <select id="project_filter" class="form-select select2-calendar" data-placeholder="Filter Proyek">
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

        function getSelectedProjectId() {
            return filterSelect ? (filterSelect.value || '') : '';
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
