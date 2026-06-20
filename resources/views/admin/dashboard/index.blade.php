@extends('adminlte::page')

@section('title', 'Dashboard de Programaciones')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-0 font-weight-bold" style="color:#1E293B;">
                <i class="fas fa-tachometer-alt mr-2" style="color:#6366F1;"></i>Dashboard de Programaciones
            </h4>
            <small class="text-muted">Monitoreo diario en tiempo real</small>
        </div>
        <a href="{{ route('admin.programacion.index') }}" class="btn btn-sm font-weight-bold"
            style="background:#6366F1;color:#fff;border-radius:8px;">
            <i class="fas fa-list mr-1"></i> Ir al Módulo de Programación
        </a>
    </div>
@endsection

@section('content')

    {{-- ── FILTROS ──────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.monitoreo.index') }}" id="formFiltro"
                class="d-flex align-items-end flex-wrap" style="gap:1rem;">
                <div>
                    <label class="text-xs text-uppercase font-weight-bold text-secondary d-block mb-1">
                        <i class="fas fa-calendar mr-1"></i> Fecha
                    </label>
                    <input type="date" name="fecha" value="{{ $fecha }}" class="form-control"
                        style="min-width:180px;border-radius:8px;" onchange="this.form.submit()">
                </div>
                <div>
                    <label class="text-xs text-uppercase font-weight-bold text-secondary d-block mb-1">
                        <i class="fas fa-clock mr-1"></i> Turno
                    </label>
                    <select name="schedule_id" class="form-control" style="min-width:200px;border-radius:8px;"
                        onchange="this.form.submit()">
                        <option value="">Todos los turnos</option>
                        @foreach($schedules as $s)
                            <option value="{{ $s->id }}" {{ $scheduleId == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn font-weight-bold"
                        style="background:#6366F1;color:#fff;border-radius:8px;">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                    <a href="{{ route('admin.monitoreo.index') }}" class="btn btn-secondary ml-1"
                        style="border-radius:8px;">
                        <i class="fas fa-redo mr-1"></i> Hoy
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── MÉTRICAS ─────────────────────────────────────────────── --}}
    <div class="row mb-4">
        @php
            $metricas = [
                ['valor' => $totalProgramaciones, 'label' => 'Total Programaciones', 'icon' => 'fa-calendar-check', 'grad' => 'linear-gradient(135deg,#6366F1,#818CF8)'],
                ['valor' => $totalCompletas, 'label' => 'Completas', 'icon' => 'fa-check-circle', 'grad' => 'linear-gradient(135deg,#059669,#34D399)'],
                ['valor' => $totalIncompletas, 'label' => 'Incompletas', 'icon' => 'fa-exclamation-triangle', 'grad' => 'linear-gradient(135deg,#DC2626,#F87171)'],
                ['valor' => $totalPersonalFaltante, 'label' => 'Personal Faltante', 'icon' => 'fa-user-times', 'grad' => 'linear-gradient(135deg,#D97706,#FCD34D)'],
            ];
        @endphp
        @foreach($metricas as $m)
            <div class="col-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius:12px;background:{{ $m['grad'] }};">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <div style="font-size:2.2rem;font-weight:800;color:#fff;line-height:1;">{{ $m['valor'] }}</div>
                            <div
                                style="font-size:.8rem;color:rgba(255,255,255,.85);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:4px;">
                                {{ $m['label'] }}
                            </div>
                        </div>
                        <div
                            style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;">
                            <i class="fas {{ $m['icon'] }}" style="color:#fff;font-size:1.3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── CARDS ────────────────────────────────────────────────── --}}
    @if($programaciones->isEmpty())
        <div class="text-center py-5">
            <div
                style="width:72px;height:72px;border-radius:50%;background:#F1F5F9;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                <i class="fas fa-calendar-times" style="font-size:1.8rem;color:#94A3B8;"></i>
            </div>
            <h6 style="color:#64748B;">Sin programaciones para esta fecha</h6>
            <small class="text-muted">Prueba con otra fecha o turno</small>
        </div>
    @else
        <div class="row">
            @foreach($programaciones as $prog)
                @php
                    $hayFalta = count($prog->faltantes) > 0;
                    $borderColor = $hayFalta ? '#DC2626' : '#059669';
                    $badgeBg = $hayFalta ? '#DC2626' : '#059669';
                @endphp
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100"
                        style="border-radius:14px;border-left:4px solid {{ $borderColor }} !important;border:1px solid #E2E8F0;">
                        <div class="card-header border-0 pb-2 pt-3 px-4" style="background:transparent;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div
                                        style="width:8px;height:8px;border-radius:50%;background:{{ $borderColor }};margin-right:8px;">
                                    </div>
                                    <strong style="font-size:.95rem;color:#1E293B;">
                                        {{ optional($prog->zone)->name ?? 'Sin Zona' }}
                                    </strong>
                                </div>
                                <span class="badge px-2 py-1"
                                    style="background:{{ $badgeBg }};color:#fff;border-radius:20px;font-size:.72rem;">
                                    <i class="fas {{ $hayFalta ? 'fa-exclamation-circle' : 'fa-check-circle' }} mr-1"></i>
                                    {{ $hayFalta ? 'Incompleto' : 'Completo' }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body px-4 pt-2 pb-3">
                            <div class="row mb-3" style="font-size:.82rem;">
                                <div class="col-6">
                                    <span class="text-muted d-block"
                                        style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Turno</span>
                                    <span class="font-weight-bold" style="color:#4F46E5;">
                                        <i class="fas fa-clock mr-1"></i>{{ optional($prog->schedule)->name ?? '—' }}
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block"
                                        style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Vehículo</span>
                                    <span class="font-weight-bold" style="color:#0F766E;">
                                        <i class="fas fa-truck mr-1"></i>{{ optional($prog->vehicle)->code ?? '—' }}
                                    </span>
                                </div>
                                <div class="col-12 mt-2">
                                    <span class="text-muted d-block"
                                        style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Grupo</span>
                                    <span class="font-weight-bold"
                                        style="color:#1E293B;">{{ optional($prog->group)->name ?? '—' }}</span>
                                </div>
                            </div>

                            <div class="d-flex mb-3" style="gap:.6rem;">
                                <div class="flex-grow-1 text-center py-2 rounded"
                                    style="background:#F0FDF4;border:1px solid #86EFAC;">
                                    <div style="font-size:1.4rem;font-weight:800;color:#059669;line-height:1;">
                                        {{ $prog->presentes }}
                                    </div>
                                    <div style="font-size:.7rem;color:#059669;font-weight:600;text-transform:uppercase;">Presentes
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-center py-2 rounded"
                                    style="background:{{ $hayFalta ? '#FEF2F2' : '#F0FDF4' }};border:1px solid {{ $hayFalta ? '#FECACA' : '#86EFAC' }};">
                                    <div
                                        style="font-size:1.4rem;font-weight:800;color:{{ $hayFalta ? '#DC2626' : '#059669' }};line-height:1;">
                                        {{ count($prog->faltantes) }}
                                    </div>
                                    <div
                                        style="font-size:.7rem;color:{{ $hayFalta ? '#DC2626' : '#059669' }};font-weight:600;text-transform:uppercase;">
                                        Faltantes</div>
                                </div>
                            </div>

                            @if($hayFalta)
                                <div class="mb-3">
                                    @foreach($prog->faltantes as $faltante)
                                        <div class="d-flex align-items-center mb-1 px-2 py-1 rounded"
                                            style="background:#FFF7F7;border:1px solid #FECACA;font-size:.8rem;">
                                            <i class="fas fa-user-times text-danger mr-2" style="font-size:.75rem;"></i>
                                            <span class="text-danger font-weight-bold">{{ $faltante['nombre'] }}</span>
                                            <span class="ml-auto badge"
                                                style="background:#FEE2E2;color:#DC2626;border-radius:10px;font-size:.68rem;">
                                                {{ ucfirst($faltante['rol']) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <button type="button" class="btn btn-block font-weight-bold btn-ver-detalles" data-id="{{ $prog->id }}"
                                style="background:{{ $hayFalta ? '#6366F1' : '#F1F5F9' }};
                                                                                                                                       color:{{ $hayFalta ? '#fff' : '#475569' }};
                                                                                                                                       border-radius:8px;font-size:.85rem;border:none;">
                                <i class="fas {{ $hayFalta ? 'fa-exchange-alt' : 'fa-eye' }} mr-1"></i>
                                {{ $hayFalta ? 'Gestionar Faltantes' : 'Ver Detalles' }}
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── MODAL EDITOR ─────────────────────────────────────────── --}}
    <div class="modal fade" id="modalEditor" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document" style="max-width:720px;">
            <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
                <div class="modal-header border-0" style="background:#1E293B;padding:1.25rem 1.5rem;">
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" style="color:#fff;">
                            <i class="fas fa-exchange-alt mr-2" style="color:#818CF8;"></i>Editor de Programación
                        </h5>
                        <small id="modal-subtitulo" style="color:#94A3B8;font-size:.8rem;"></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;text-shadow:none;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" id="modal-body-content">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <div class="mt-2 text-muted">Cargando...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('css')
    <style>
        .card {
            transition: box-shadow .2s;
        }

        .card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, .1) !important;
        }

        /* Secciones del modal */
        .editor-section {
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .editor-section-header {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: default;
        }

        .editor-section-body {
            padding: 14px 16px;
            border-top: 1px solid #E2E8F0;
        }

        /* Slot de personal */
        .slot-card {
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            padding: 12px 14px;
            margin-bottom: 10px;
            background: #fff;
        }

        .slot-card.faltante {
            border-color: #FECACA;
            background: #FFF7F7;
        }

        .slot-card.presente {
            border-color: #86EFAC;
            background: #F0FDF4;
        }

        /* Dropdown búsqueda */
        .search-results-dropdown {
            position: absolute;
            z-index: 1060;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .12);
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            left: 0;
            top: 100%;
        }

        .search-result-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            font-size: .85rem;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background: #F8FAFC;
        }

        .label-mini {
            font-size: .7rem;
            color: #64748B;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: .4px;
            margin-bottom: 3px;
            display: block;
        }
    </style>
@endsection

@section('js')
    <script>
        (function () {
            'use strict';

            // ── URLs de rutas ────────────────────────────────────────
            var URL_DETALLE = "{{ route('admin.monitoreo.detalle', ':id') }}";
            var URL_DISPONIBLE = "{{ route('admin.monitoreo.personal-disponible') }}";
            var URL_REEMPLAZAR = "{{ route('admin.monitoreo.reemplazar', ':id') }}";
            var URL_TURNO = "{{ route('admin.monitoreo.cambiar-turno', ':id') }}";
            var URL_VEHICULO = "{{ route('admin.monitoreo.cambiar-vehiculo', ':id') }}";
            var URL_ASISTENCIA = "{{ route('admin.monitoreo.verificar-asistencia') }}";
            var CSRF = "{{ csrf_token() }}";

            // Motivos pasados desde el controller
            var MOTIVOS_TURNO = @json($motivosTurno);
            var MOTIVOS_VEHICULO = @json($motivosVehiculo);
            var MOTIVOS_PERSONAL = @json($motivosPersonal);

            // Estado del modal
            var modalData = {};

            // ── Abrir modal ──────────────────────────────────────────
            $(document).on('click', '.btn-ver-detalles', function () {
                var progId = $(this).data('id');
                $('#modal-body-content').html(spinnerHtml());
                $('#modalEditor').modal('show');

                $.getJSON(URL_DETALLE.replace(':id', progId), function (data) {
                    modalData = data;
                    $('#modal-subtitulo').text(data.fecha + ' · ' + data.schedule_info + ' · ' + data.vehicle_info);
                    renderModal(data);
                }).fail(function () {
                    $('#modal-body-content').html('<div class="p-4 text-danger">Error al cargar los datos.</div>');
                });
            });

            // ── Render principal del modal ───────────────────────────
            function renderModal(data) {
                var html = '<div class="p-4">';

                // ── Sección Turno ──
                html += sectionTurno(data);

                // ── Sección Vehículo ──
                html += sectionVehiculo(data);

                // ── Sección Personal ──
                html += '<div class="editor-section" style="border-color:#FED7AA;">';
                html += '<div class="editor-section-header" style="background:#FFF7ED;">';
                html += '  <div class="d-flex align-items-center">';
                html += '    <div style="width:30px;height:30px;border-radius:50%;background:#FFEDD5;display:flex;align-items:center;justify-content:center;margin-right:10px;">';
                html += '      <i class="fas fa-users" style="color:#EA580C;font-size:.8rem;"></i></div>';
                html += '    <strong style="font-size:.88rem;color:#1E293B;">Cambio de Personal</strong>';
                html += '  </div>';
                html += '</div>';
                html += '<div class="editor-section-body">';

                data.personal.forEach(function (p) {
                    html += slotPersonalHtml(p, data.id, data.fecha_iso);
                });

                html += '</div></div>'; // section personal

                html += '</div>'; // p-4
                $('#modal-body-content').html(html);

                initBusquedas();
            }

            // ── Sección turno ────────────────────────────────────────
            function sectionTurno(data) {
                var opcionesSelect = '<option value="">-- Seleccione turno --</option>';
                data.turnos.forEach(function (t) {
                    var disabled = (t.id == data.schedule_id) ? 'disabled style="color:#aaa"' : '';
                    var label = (t.id == data.schedule_id) ? ' ← actual' : '';
                    opcionesSelect += '<option value="' + t.id + '" ' + disabled + '>'
                        + t.name + ' (' + t.time_start + ' - ' + t.time_end + ')' + label
                        + '</option>';
                });

                var motivos = motivosSelectHtml(MOTIVOS_TURNO, 'motivo-turno');

                return '<div class="editor-section" style="border-color:#C7D7FC;">'
                    + '<div class="editor-section-header" style="background:#EEF2FF;">'
                    + '  <div class="d-flex align-items-center">'
                    + '    <div style="width:30px;height:30px;border-radius:50%;background:#E0E7FF;display:flex;align-items:center;justify-content:center;margin-right:10px;">'
                    + '      <i class="fas fa-clock" style="color:#4F46E5;font-size:.8rem;"></i></div>'
                    + '    <div>'
                    + '      <strong style="font-size:.88rem;color:#1E293B;">Cambio de Turno</strong>'
                    + '      <div style="font-size:.75rem;color:#64748B;">Actual: <strong>' + data.schedule_info + '</strong></div>'
                    + '    </div>'
                    + '  </div>'
                    + '</div>'
                    + '<div class="editor-section-body">'
                    + '  <div class="row">'
                    + '    <div class="col-md-6 mb-2">'
                    + '      <span class="label-mini">Nuevo Turno</span>'
                    + '      <select id="select-nuevo-turno" class="form-control form-control-sm" style="border-radius:7px;">' + opcionesSelect + '</select>'
                    + '    </div>'
                    + '    <div class="col-md-6 mb-2">'
                    + '      <span class="label-mini">Motivo</span>'
                    + '      <select id="motivo-turno" class="form-control form-control-sm select-motivo-turno" style="border-radius:7px;">' + motivos + '</select>'
                    + '    </div>'
                    + '    <div class="col-12 mb-2 detalle-turno-col" style="display:none;">'
                    + '      <input type="text" id="detalle-turno" class="form-control form-control-sm" style="border-radius:7px;" placeholder="Describe el motivo...">'
                    + '    </div>'
                    + '  </div>'
                    + '  <button type="button" id="btn-guardar-turno" class="btn btn-sm font-weight-bold w-100"'
                    + '          data-prog-id="' + data.id + '"'
                    + '          style="background:#4F46E5;color:#fff;border-radius:7px;margin-top:4px;">'
                    + '    <i class="fas fa-save mr-1"></i> Guardar cambio de turno'
                    + '  </button>'
                    + '</div>'
                    + '</div>';
            }

            // ── Sección vehículo ─────────────────────────────────────
            function sectionVehiculo(data) {
                var opcionesSelect = '<option value="">-- Seleccione vehículo --</option>';
                data.vehiculos.forEach(function (v) {
                    var disabled = (v.id == data.vehicle_id) ? 'disabled style="color:#aaa"' : '';
                    var label = (v.id == data.vehicle_id) ? ' ← actual' : '';
                    opcionesSelect += '<option value="' + v.id + '" ' + disabled + '>'
                        + v.code + ' — ' + v.name + ' (Cap. ' + v.occupant_capacity + ')' + label
                        + '</option>';
                });

                var motivos = motivosSelectHtml(MOTIVOS_VEHICULO, 'motivo-vehiculo');

                return '<div class="editor-section" style="border-color:#A7F3D0;">'
                    + '<div class="editor-section-header" style="background:#ECFDF5;">'
                    + '  <div class="d-flex align-items-center">'
                    + '    <div style="width:30px;height:30px;border-radius:50%;background:#D1FAE5;display:flex;align-items:center;justify-content:center;margin-right:10px;">'
                    + '      <i class="fas fa-truck" style="color:#059669;font-size:.8rem;"></i></div>'
                    + '    <div>'
                    + '      <strong style="font-size:.88rem;color:#1E293B;">Cambio de Vehículo</strong>'
                    + '      <div style="font-size:.75rem;color:#64748B;">Actual: <strong>' + data.vehicle_info + '</strong></div>'
                    + '    </div>'
                    + '  </div>'
                    + '</div>'
                    + '<div class="editor-section-body">'
                    + '  <div class="row">'
                    + '    <div class="col-md-6 mb-2">'
                    + '      <span class="label-mini">Nuevo Vehículo</span>'
                    + '      <select id="select-nuevo-vehiculo" class="form-control form-control-sm" style="border-radius:7px;">' + opcionesSelect + '</select>'
                    + '    </div>'
                    + '    <div class="col-md-6 mb-2">'
                    + '      <span class="label-mini">Motivo</span>'
                    + '      <select id="motivo-vehiculo" class="form-control form-control-sm select-motivo-vehiculo" style="border-radius:7px;">' + motivos + '</select>'
                    + '    </div>'
                    + '    <div class="col-12 mb-2 detalle-vehiculo-col" style="display:none;">'
                    + '      <input type="text" id="detalle-vehiculo" class="form-control form-control-sm" style="border-radius:7px;" placeholder="Describe el motivo...">'
                    + '    </div>'
                    + '  </div>'
                    + '  <button type="button" id="btn-guardar-vehiculo" class="btn btn-sm font-weight-bold w-100"'
                    + '          data-prog-id="' + data.id + '"'
                    + '          style="background:#059669;color:#fff;border-radius:7px;margin-top:4px;">'
                    + '    <i class="fas fa-save mr-1"></i> Guardar cambio de vehículo'
                    + '  </button>'
                    + '</div>'
                    + '</div>';
            }

            // ── HTML de un slot de personal ──────────────────────────
            function slotPersonalHtml(p, progId, fecha) {
                var esFaltante = !p.presente;
                var cardClass = esFaltante ? 'faltante' : 'presente';
                var iconColor = esFaltante ? '#DC2626' : '#059669';
                var icon = esFaltante ? 'fa-user-times' : 'fa-user-check';
                var rolLabel = p.rol === 'conductor' ? 'Conductor' : 'Ayudante';

                var html = '<div class="slot-card ' + cardClass + '">';
                html += '<div class="d-flex align-items-center justify-content-between mb-2">';
                html += '  <div class="d-flex align-items-center">';
                html += '    <i class="fas ' + icon + ' mr-2" style="color:' + iconColor + ';font-size:1rem;"></i>';
                html += '    <div>';
                html += '      <div class="label-mini" style="margin-bottom:1px;">' + rolLabel + '</div>';
                html += '      <div style="font-size:.95rem;font-weight:700;color:#1E293B;">' + p.nombre + '</div>';
                html += '    </div>';
                html += '  </div>';

                if (esFaltante) {
                    html += '<span class="badge px-2 py-1" style="background:#FEE2E2;color:#DC2626;border-radius:12px;font-size:.7rem;"><i class="fas fa-clock mr-1"></i>Faltante</span>';
                } else {
                    html += '<span class="badge px-2 py-1" style="background:#DCFCE7;color:#059669;border-radius:12px;font-size:.7rem;"><i class="fas fa-check mr-1"></i>Presente</span>';
                }
                html += '</div>'; // d-flex header

                if (esFaltante) {
                    html += reemplazoFormHtml(p, progId, fecha);
                }

                html += '</div>'; // slot-card
                return html;
            }

            // ── Formulario de reemplazo ──────────────────────────────
            function reemplazoFormHtml(p, progId, fecha) {
                var motivos = motivosSelectHtml(MOTIVOS_PERSONAL, 'motivo-personal-' + p.slot);

                return '<div class="reemplazo-form pt-2" style="border-top:1px dashed #FECACA;">'
                    + '<div class="row">'
                    + '  <div class="col-md-7 mb-2 position-relative">'
                    + '    <span class="label-mini">Buscar reemplazo</span>'
                    + '    <input type="text" class="form-control form-control-sm search-reemplazo" style="border-radius:7px;"'
                    + '           data-slot="' + p.slot + '" data-rol="' + p.rol + '" data-prog-id="' + progId + '" data-fecha="' + fecha + '"'
                    + '           placeholder="Nombre o DNI..." autocomplete="off">'
                    + '    <div class="search-results-dropdown" style="display:none;"></div>'
                    + '  </div>'
                    + '  <div class="col-md-5 mb-2">'
                    + '    <span class="label-mini">Motivo</span>'
                    + '    <select class="form-control form-control-sm select-motivo-personal" style="border-radius:7px;"'
                    + '            data-slot="' + p.slot + '">' + motivos + '</select>'
                    + '  </div>'
                    + '  <div class="col-12 mb-2 detalle-personal-col" style="display:none;" data-slot="' + p.slot + '">'
                    + '    <input type="text" class="form-control form-control-sm input-detalle-personal" style="border-radius:7px;"'
                    + '           placeholder="Describe el motivo...">'
                    + '  </div>'
                    + '</div>'
                    + '<div class="selected-reemplazo-info mb-2" style="display:none;">'
                    + '  <div class="d-flex align-items-center justify-content-between px-2 py-1 rounded"'
                    + '       style="background:#EEF2FF;border:1px solid #C7D7FC;font-size:.82rem;">'
                    + '    <div><i class="fas fa-user-plus text-primary mr-1"></i>'
                    + '      <strong class="nuevo-nombre text-primary"></strong>'
                    + '    </div>'
                    + '    <button type="button" class="btn-limpiar" style="background:none;border:none;color:#94A3B8;cursor:pointer;">'
                    + '      <i class="fas fa-times"></i></button>'
                    + '  </div>'
                    + '</div>'
                    + '<button type="button" class="btn btn-sm font-weight-bold btn-aplicar-reemplazo w-100"'
                    + '        data-slot="' + p.slot + '" data-prog-id="' + progId + '"'
                    + '        style="background:#6366F1;color:#fff;border-radius:7px;display:none;">'
                    + '  <i class="fas fa-exchange-alt mr-1"></i> Confirmar reemplazo'
                    + '</button>'
                    + '</div>';
            }

            // ── Helper: opciones de motivos ──────────────────────────
            function motivosSelectHtml(lista, id) {
                var html = '<option value="">-- Motivo predefinido --</option>';
                lista.forEach(function (m) {
                    html += '<option value="' + m.id + '">' + m.name + '</option>';
                });
                return html;
            }

            // ── Inicializar eventos después de render ────────────────
            function initBusquedas() {
                // noop — los eventos están delegados en $(document)
            }

            // ── Búsqueda de personal ─────────────────────────────────
            var searchTimers = {};
            $(document).on('input', '.search-reemplazo', function () {
                var $input = $(this);
                var slot = $input.data('slot');
                var $drop = $input.siblings('.search-results-dropdown');
                var q = $input.val().trim();

                clearTimeout(searchTimers[slot]);
                if (q.length < 2) { $drop.hide(); return; }

                searchTimers[slot] = setTimeout(function () {
                    $.getJSON(URL_DISPONIBLE, {
                        q: q,
                        rol: $input.data('rol'),
                        programacion_id: $input.data('prog-id'),
                        fecha: $input.data('fecha'),
                    }, function (users) {
                        if (!users.length) {
                            $drop.html('<div class="search-result-item text-muted">Sin resultados disponibles.</div>').show();
                            return;
                        }
                        var html = users.map(function (u) {
                            return '<div class="search-result-item" data-id="' + u.id + '" data-name="' + u.name + '">'
                                + '<strong>' + u.name + '</strong>'
                                + '<div style="font-size:.72rem;color:#6B7280;">DNI ' + u.dni + '</div>'
                                + '</div>';
                        }).join('');
                        $drop.html(html).show();
                    });
                }, 300);
            });

            // Seleccionar resultado de búsqueda
            $(document).on('click', '.search-result-item', function () {
                var $drop = $(this).closest('.search-results-dropdown');
                var $form = $(this).closest('.reemplazo-form');
                var userId = $(this).data('id');
                var name = $(this).data('name');

                $form.find('.selected-reemplazo-info').show().find('.nuevo-nombre').text(name);
                $form.find('.search-reemplazo').val('').data('selected-id', userId);
                $form.find('.btn-aplicar-reemplazo').show().data('nuevo-user-id', userId);
                $drop.hide();
            });

            // Limpiar selección
            $(document).on('click', '.btn-limpiar', function () {
                var $form = $(this).closest('.reemplazo-form');
                $form.find('.selected-reemplazo-info').hide();
                $form.find('.btn-aplicar-reemplazo').hide().data('nuevo-user-id', null);
                $form.find('.search-reemplazo').val('').removeData('selected-id');
            });

            // Mostrar campo detalle si motivo = "Otro" (id 5)
            $(document).on('change', '.select-motivo-personal', function () {
                var slot = $(this).data('slot');
                var $col = $('.detalle-personal-col[data-slot="' + slot + '"]');
                $(this).val() == '5' ? $col.show() : $col.hide().find('input').val('');
            });

            $(document).on('change', '.select-motivo-turno', function () {
                $(this).val() == '5' ? $('.detalle-turno-col').show() : $('.detalle-turno-col').hide().find('input').val('');
            });

            $(document).on('change', '.select-motivo-vehiculo', function () {
                $(this).val() == '5' ? $('.detalle-vehiculo-col').show() : $('.detalle-vehiculo-col').hide().find('input').val('');
            });

            // ── Confirmar reemplazo de personal ─────────────────────
            $(document).on('click', '.btn-aplicar-reemplazo', function () {
                var $btn = $(this);
                var slot = $btn.data('slot');
                var progId = $btn.data('prog-id');
                var nuevoId = $btn.data('nuevo-user-id');
                var $form = $btn.closest('.reemplazo-form');
                var motivoId = $form.find('.select-motivo-personal').val();
                var detalle = $form.find('.input-detalle-personal').val();

                if (!nuevoId) { Swal.fire('Atención', 'Selecciona primero un reemplazo.', 'warning'); return; }

                setBtnLoading($btn, true, 'Guardando...');

                $.ajax({
                    url: URL_REEMPLAZAR.replace(':id', progId),
                    method: 'POST',
                    data: { _token: CSRF, slot: slot, nuevo_user_id: nuevoId, motivo_id: motivoId || null, motivo_detalle: detalle || null },
                    success: function (res) {
                        var $slotCard = $btn.closest('.slot-card');
                        var nuevoNombre = $form.find('.nuevo-nombre').text();
                        var fecha = $btn.closest('[data-fecha]').data('fecha') || modalData.fecha_iso;

                        $slotCard.find('div[style*="font-size:.95rem"]').text(nuevoNombre);
                        $slotCard.find('.reemplazo-form').remove();

                        // Consultar si el nuevo personal ya tiene asistencia hoy
                        $.getJSON(URL_ASISTENCIA, { user_id: nuevoId, fecha: fecha }, function (data) {
                            if (data.presente) {
                                $slotCard.removeClass('faltante').addClass('presente');
                                $slotCard.css({ 'border-color': '#86EFAC', 'background': '#F0FDF4' });
                                $slotCard.find('.fas.fa-user-times').removeClass('fa-user-times fa-user-clock').addClass('fa-user-check').css('color', '#059669');
                                $slotCard.find('.badge').html('<i class="fas fa-check mr-1"></i>Presente').css({ 'background': '#DCFCE7', 'color': '#059669' });
                            } else {
                                $slotCard.css({ 'border-color': '#FCD34D', 'background': '#FFFBEB' });
                                $slotCard.find('.fas.fa-user-times').removeClass('fa-user-times').addClass('fa-user-clock').css('color', '#D97706');
                                $slotCard.find('.badge').html('<i class="fas fa-clock mr-1"></i>Sin asistencia').css({ 'background': '#FEF3C7', 'color': '#D97706' });
                            }
                        });

                        toastOk('Personal reemplazado correctamente.');
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar.';
                        Swal.fire('Error', msg, 'error');
                        setBtnLoading($btn, false, '<i class="fas fa-exchange-alt mr-1"></i> Confirmar reemplazo');
                    }
                });
            });



            // ── Guardar cambio de turno ──────────────────────────────
            $(document).on('click', '#btn-guardar-turno', function () {
                var $btn = $(this);
                var progId = $btn.data('prog-id');
                var schedId = $('#select-nuevo-turno').val();
                var motivoId = $('#motivo-turno').val();
                var detalle = $('#detalle-turno').val();

                if (!schedId) { Swal.fire('Atención', 'Selecciona el nuevo turno.', 'warning'); return; }

                setBtnLoading($btn, true, 'Guardando...');

                $.ajax({
                    url: URL_TURNO.replace(':id', progId),
                    method: 'POST',
                    data: { _token: CSRF, schedule_id: schedId, motivo_id: motivoId || null, motivo_detalle: detalle || null },
                    success: function (res) {
                        var textoSeleccionado = $('#select-nuevo-turno option:selected').text();
                        var $section = $btn.closest('.editor-section');
                        $section.find('.editor-section-header div div').last().html('Actual: <strong>' + textoSeleccionado + '</strong>');
                        $('#select-nuevo-turno option:selected').prop('disabled', true).text(textoSeleccionado + ' ← actual');
                        $('#select-nuevo-turno').val('');
                        setBtnLoading($btn, false, '<i class="fas fa-save mr-1"></i> Guardar cambio de turno');
                        toastOk('Turno cambiado correctamente.');
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar.';
                        Swal.fire('Error', msg, 'error');
                        setBtnLoading($btn, false, '<i class="fas fa-save mr-1"></i> Guardar cambio de turno');
                    }
                });
            });

            // ── Guardar cambio de vehículo ───────────────────────────
            $(document).on('click', '#btn-guardar-vehiculo', function () {
                var $btn = $(this);
                var progId = $btn.data('prog-id');
                var vehicleId = $('#select-nuevo-vehiculo').val();
                var motivoId = $('#motivo-vehiculo').val();
                var detalle = $('#detalle-vehiculo').val();

                if (!vehicleId) { Swal.fire('Atención', 'Selecciona el nuevo vehículo.', 'warning'); return; }

                setBtnLoading($btn, true, 'Guardando...');

                $.ajax({
                    url: URL_VEHICULO.replace(':id', progId),
                    method: 'POST',
                    data: { _token: CSRF, vehicle_id: vehicleId, motivo_id: motivoId || null, motivo_detalle: detalle || null },
                    success: function (res) {
                        var textoSeleccionado = $('#select-nuevo-vehiculo option:selected').text().replace(' ← actual', '').trim();
                        var $section = $btn.closest('.editor-section');
                        $section.find('.editor-section-header div div').last().html('Actual: <strong>' + textoSeleccionado + '</strong>');
                        $('#select-nuevo-vehiculo option:selected').prop('disabled', true).text(textoSeleccionado + ' ← actual');
                        $('#select-nuevo-vehiculo').val('');
                        setBtnLoading($btn, false, '<i class="fas fa-save mr-1"></i> Guardar cambio de vehículo');
                        toastOk('Vehículo cambiado correctamente.');
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar.';
                        Swal.fire('Error', msg, 'error');
                        setBtnLoading($btn, false, '<i class="fas fa-save mr-1"></i> Guardar cambio de vehículo');
                    }
                });
            });

            // ── Helper: estado del botón ─────────────────────────────
            function setBtnLoading($btn, loading, label) {
                $btn.prop('disabled', loading);
                if (loading) {
                    $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> ' + label);
                } else {
                    $btn.html(label);
                }
            }

            // ── Cerrar dropdowns al clic fuera ───────────────────────
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.search-reemplazo, .search-results-dropdown').length) {
                    $('.search-results-dropdown').hide();
                }
            });

            function spinnerHtml() {
                return '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><div class="mt-2 text-muted">Cargando...</div></div>';
            }

            $('#modalEditor').on('hidden.bs.modal', function () {
                location.reload();
            });



            function toastOk(msg) {
                var $toast = $('<div>')
                    .text('✓  ' + msg)
                    .css({
                        position: 'fixed', bottom: '24px', right: '24px', zIndex: 9999,
                        background: '#059669', color: '#fff', padding: '12px 20px',
                        borderRadius: '10px', fontWeight: '600', fontSize: '.88rem',
                        boxShadow: '0 4px 16px rgba(0,0,0,.2)', opacity: 0,
                    })
                    .appendTo('body')
                    .animate({ opacity: 1 }, 200);
                setTimeout(function () { $toast.animate({ opacity: 0 }, 300, function () { $toast.remove(); }); }, 2500);
            }

        })();
    </script>
@endsection