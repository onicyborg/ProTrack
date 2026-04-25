@extends('layouts.master')

@section('page_title', 'Monitoring Kalender')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-0">Monitoring Kalender</h3>
            <div class="text-muted">Pantau rentang proyek dan WBS (task) dalam kalender</div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background-color: #3E97FF;">&nbsp;</span>
                <span class="text-muted">Rentang Proyek</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background-color: #FFC700;">&nbsp;</span>
                <span class="text-muted">WBS/Task</span>
            </div>
        </div>
    </div>

    <div class="card" id="pmCalendarCard">
        <div class="card-header">
            <div class="card-title">Kalender</div>
            <div class="card-toolbar" style="min-width: 320px;">
                <select id="project_filter" class="form-select select2-calendar" data-placeholder="Filter Proyek">
                    <option value="">Semua Proyek</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}" @selected(($selectedProjectId ?? '') === $p->id)>{{ $p->project_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body">
            <div id="kt_calendar_pm"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('kt_calendar_pm');
        if (!calendarEl || !window.FullCalendar) return;

        var cardEl = document.getElementById('pmCalendarCard');
        var eventsUrl = @json(route('pm.calendar.events'));
        var checkUrl = @json(route('pm.calendar.check'));
        var projectsShowBaseUrl = @json(url('/pm/projects'));

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

        function getClickedDate(info) {
            var e = info && info.jsEvent ? info.jsEvent : null;
            if (e && typeof document.elementFromPoint === 'function') {
                var el = document.elementFromPoint(e.clientX, e.clientY);
                if (el && el.closest) {
                    var dateElFromPoint = el.closest('[data-date]');
                    if (dateElFromPoint && dateElFromPoint.getAttribute) {
                        var d1 = dateElFromPoint.getAttribute('data-date');
                        if (d1) return d1;
                    }
                }
            }

            var t = e ? e.target : null;
            if (t && t.closest) {
                var dateEl = t.closest('[data-date]');
                if (dateEl && dateEl.getAttribute) {
                    var d2 = dateEl.getAttribute('data-date');
                    if (d2) return d2;
                }
            }

            return null;
        }

        function block() {
            if (!cardEl || typeof KTBlockUI === 'undefined') return null;
            var b = new KTBlockUI(cardEl, {
                message: '<div class="blockui-message"><span class="spinner-border text-primary"></span> Memproses...</div>'
            });
            b.block();
            return b;
        }

        function unblock(b) {
            if (!b) return;
            try { b.release(); } catch (e) {}
            try { b.destroy(); } catch (e) {}
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

                if (props.type === 'wbs') {
                    var clickedDate = (props.date || (info.event.startStr ? info.event.startStr.slice(0, 10) : null));
                    if (!clickedDate || !props.task_id) return;

                    var b = block();

                    var params = new URLSearchParams();
                    params.set('task_id', props.task_id);
                    params.set('date', clickedDate);

                    fetch(checkUrl + '?' + params.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(function (res) { return res.json(); })
                        .then(function (json) {
                            unblock(b);
                            if (json && json.redirect_url) {
                                window.location.href = json.redirect_url;
                            }
                        })
                        .catch(function (err) {
                            unblock(b);
                            if (window.toastr && toastr.error) {
                                toastr.error('Gagal mengecek Daily Report.');
                            } else {
                                console.error(err);
                            }
                        });
                }
            }
        });

        calendar.render();
    });
</script>
@endpush
