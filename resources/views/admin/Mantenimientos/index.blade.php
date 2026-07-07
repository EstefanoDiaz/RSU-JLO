@extends('adminlte::page')

@section('title', 'RSU JLO - Mantenimientos')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">

        {{-- Header --}}
        <div class="card-header custom-crud-header d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-tools mr-2"></i> Mantenimientos
            </h4>

            <button type="button"
                    class="btn btn-warning font-weight-bold px-3 py-2"
                    id="btn-nuevo-mant">
                <i class="fas fa-plus mr-1"></i> Nuevo Mantenimiento
            </button>
        </div>

        {{-- Tabla --}}
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblMantenimientos" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Nuevo / Editar Mantenimiento --}}
<div class="modal fade" id="MantModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
            <div class="modal-header py-3 text-white" style="background:#071D38;">
                <h5 class="modal-title font-weight-bold" id="MantModalTitle">
                    <i class="fas fa-tools mr-2"></i> Mantenimiento
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <form id="formMant">
                    <input type="hidden" id="mantId">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Nombre *</label>
                        <input type="text" id="mantNombre" class="form-control" placeholder="Ej. MANT. DICIEMBRE 2026" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha Inicio *</label>
                            <input type="date" id="mantFechaInicio" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha Fin *</label>
                            <input type="date" id="mantFechaFin" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end pt-2 border-top mt-2">
                        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary font-weight-bold">
                            <i class="fas fa-save mr-1"></i> <span id="mantBtnText">Guardar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Horarios --}}
<div class="modal fade" id="HorariosModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
            <div class="modal-header py-3 text-white" style="background:#071D38;">
                <h5 class="modal-title font-weight-bold" id="HorariosModalTitle">
                    <i class="fas fa-clock mr-2"></i> Horarios
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4" id="HorariosModalBody" style="max-height:80vh;overflow-y:auto;"></div>
        </div>
    </div>
</div>

{{-- Modal: Detalles de un horario --}}
<div class="modal fade" id="DetallesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
            <div class="modal-header py-3 text-white" style="background:#059669;">
                <h5 class="modal-title font-weight-bold" id="DetallesModalTitle">
                    <i class="fas fa-calendar-alt mr-2"></i> Días Generados
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4" id="DetallesModalBody" style="max-height:75vh;overflow-y:auto;"></div>
        </div>
    </div>
</div>

{{-- Modal: Editar Detalle --}}
<div class="modal fade" id="EditDetalleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
            <div class="modal-header py-3 text-white" style="background:#3B82F6;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-edit mr-2"></i> Editar Día
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <form id="formDetalle" enctype="multipart/form-data">
                    <input type="hidden" id="detalleId">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha</label>
                        <input type="text" id="detalleFecha" class="form-control" readonly style="background:#f8f9fa;">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Observación</label>
                        <textarea id="detalleObservacion" class="form-control" rows="3"
                                  placeholder="Observaciones del mantenimiento..."></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Imagen</label>
                        <input type="file" id="detalleImagen" class="form-control-file" accept="image/*">
                        <div id="detalleImagenPreview" class="mt-2"></div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="detalleRealizado">
                            <label class="custom-control-label font-weight-bold" for="detalleRealizado">
                                Mantenimiento Realizado
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end pt-2 border-top">
                        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('custom-crud.css') }}">

    <style>
        .badge-tipo-Preventivo { background:#3B82F6; }
        .badge-tipo-Limpieza   { background:#10B981; }
        .badge-tipo-Reparación { background:#EF4444; }
        .badge-dia { background:#F59E0B; }

        /* Sobrescribir custom-crud.css */
        .custom-crud-header{
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .custom-crud-header h4{
            margin-bottom: 0 !important;
        }

        #btn-nuevo-mant{
            margin-left: auto !important;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function () {

        var CSRF        = '{{ csrf_token() }}';
        var URL_BASE    = '{{ url("admin/mantenimientos") }}';
        var URL_FORM    = '{{ route("admin.mantenimientos.form-data") }}';
        var mantActivoId = null;
        var formData_cache = null; 
        var horarioActivoId = null;

        // ── DataTable ──────────────────────────────────────────
        var table = $('#tblMantenimientos').DataTable({
            processing: true,
            serverSide: true,
            ajax: URL_BASE,
            columns: [
                { data: 'id',              className: 'align-middle text-muted' },
                { data: 'nombre',          className: 'align-middle font-weight-bold' },
                { data: 'fecha_inicio_fmt',className: 'align-middle' },
                { data: 'fecha_fin_fmt',   className: 'align-middle' },
                { data: 'actions',         className: 'text-center align-middle', orderable: false, searchable: false },
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });

        // ── Precargar vehicles y mecánicos ────────────────────
        $.get(URL_FORM, function (data) { formData_cache = data; });

        // ── Nuevo Mantenimiento ────────────────────────────────
        $('#btn-nuevo-mant').click(function () {
            $('#mantId').val('');
            $('#formMant')[0].reset();
            $('#MantModalTitle').html('<i class="fas fa-plus-circle mr-2"></i> Nuevo Mantenimiento');
            $('#mantBtnText').text('Guardar');
            $('#MantModal').modal('show');
        });

        // ── Editar Mantenimiento ───────────────────────────────
        $(document).on('click', '.btn-editar-mant', function () {
            var id = $(this).data('id');
            $.get(URL_BASE + '/' + id + '/edit', function (data) {
                $('#mantId').val(data.id);
                $('#mantNombre').val(data.nombre);
                $('#mantFechaInicio').val(data.fecha_inicio);
                $('#mantFechaFin').val(data.fecha_fin);
                $('#MantModalTitle').html('<i class="fas fa-edit mr-2"></i> Editar Mantenimiento');
                $('#mantBtnText').text('Actualizar');
                $('#MantModal').modal('show');
            });
        });

        // ── Submit Mantenimiento ───────────────────────────────
        $('#formMant').on('submit', function (e) {
            e.preventDefault();
            var id  = $('#mantId').val();
            var url = id ? URL_BASE + '/' + id : URL_BASE;
            var method = id ? 'PUT' : 'POST';

            $.ajax({
                url:  url,
                type: method,
                data: {
                    _token:       CSRF,
                    nombre:       $('#mantNombre').val(),
                    fecha_inicio: $('#mantFechaInicio').val(),
                    fecha_fin:    $('#mantFechaFin').val(),
                },
                success: function (res) {
                    $('#MantModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('¡Listo!', res.message, 'success');
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error');
                }
            });
        });

        // ── Eliminar Mantenimiento ─────────────────────────────
        $(document).on('click', '.btn-eliminar-mant', function () {
            var id       = $(this).data('id');
            var horarios = parseInt($(this).data('horarios'));

            if (horarios > 0) {
                Swal.fire('No permitido', 'Este mantenimiento tiene ' + horarios + ' horario(s) registrado(s). Elimínalos primero.', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Eliminar mantenimiento?',
                text:  'Esta acción no se puede deshacer.',
                icon:  'warning',
                showCancelButton:  true,
                confirmButtonColor: '#dc3545',
                cancelButtonText:  'Cancelar',
                confirmButtonText: 'Sí, eliminar',
            }).then(function (r) {
                if (!r.isConfirmed) return;
                $.ajax({
                    url:  URL_BASE + '/' + id,
                    type: 'DELETE',
                    data: { _token: CSRF },
                    success: function (res) { table.ajax.reload(null, false); Swal.fire('Eliminado', res.message, 'success'); },
                    error:   function (xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error'); }
                });
            });
        });

        // ── Abrir Horarios ────────────────────────────────────
        $(document).on('click', '.btn-horarios', function () {
            mantActivoId = $(this).data('id');
            var nombre   = $(this).data('nombre');
            $('#HorariosModalTitle').html('<i class="fas fa-clock mr-2"></i> ' + nombre + ' — Horarios');
            cargarHorarios(mantActivoId);
            $('#HorariosModal').modal('show');
        });

        // ── Cargar horarios ────────────────────────────────────
        function cargarHorarios(mantId) {
            $.get(URL_BASE + '/' + mantId + '/horarios', function (data) {
                var rows = data.horarios.length
                    ? data.horarios.map(function (h) {
                        var tipoBg = {Preventivo:'#3B82F6', Limpieza:'#10B981', 'Reparación':'#EF4444'}[h.tipo] || '#6B7280';
                        return '<tr>'
                            + '<td style="font-size:.82rem;">'
                            + '<span style="background:#F59E0B;color:#fff;padding:2px 8px;border-radius:12px;font-size:.72rem;font-weight:700;">' + h.dia_semana + '</span>'
                            + '</td>'
                            + '<td style="font-size:.82rem;">' + h.vehicle + '</td>'
                            + '<td style="font-size:.82rem;">' + h.responsable + '</td>'
                            + '<td><span style="background:'+tipoBg+';color:#fff;padding:2px 8px;border-radius:12px;font-size:.72rem;font-weight:700;">' + h.tipo + '</span></td>'
                            + '<td style="font-size:.82rem;">' + h.hora_inicio + '</td>'
                            + '<td style="font-size:.82rem;">' + h.hora_fin + '</td>'
                            + '<td class="text-center">'
                            + '<button class="btn btn-sm btn-success btn-ver-detalles mr-1" data-id="' + h.id + '" title="Ver días"><i class="fas fa-calendar-alt text-white"></i></button>'
                            + '<button class="btn btn-sm btn-warning btn-editar-horario mr-1" data-id="' + h.id + '" title="Editar"><i class="fas fa-pen text-dark"></i></button>'
                            + '<button class="btn btn-sm btn-danger btn-eliminar-horario" data-id="' + h.id + '" title="Eliminar"><i class="fas fa-trash-alt text-white"></i></button>'
                            + '</td>'
                            + '</tr>';
                    }).join('')
                    : '<tr><td colspan="7" class="text-center text-muted py-3"><i class="fas fa-inbox mr-1"></i>Sin horarios registrados.</td></tr>';

                $('#HorariosModalBody').html(`
                    <div class="mb-3 text-right">
                        <button type="button" class="btn btn-primary font-weight-bold" id="btn-nuevo-horario">
                            <i class="fas fa-plus mr-1"></i> Nuevo Horario
                        </button>
                    </div>
                    <div id="form-horario-wrap" class="d-none mb-4"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="border:1px solid #e5e7eb;border-radius:8px;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="font-size:.75rem;">Día</th>
                                    <th style="font-size:.75rem;">Vehículo</th>
                                    <th style="font-size:.75rem;">Responsable</th>
                                    <th style="font-size:.75rem;">Tipo</th>
                                    <th style="font-size:.75rem;">Inicio</th>
                                    <th style="font-size:.75rem;">Fin</th>
                                    <th style="font-size:.75rem;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="horarios-tbody">${rows}</tbody>
                        </table>
                    </div>
                `);
            });
        }

        // ── Formulario inline de horario ──────────────────────
        function buildFormHorario(h) {
            var fd = formData_cache || { vehicles: [], mecanicos: [] };
            var vehicleOpts = fd.vehicles.map(function (v) {
                return '<option value="' + v.id + '"' + (h && h.vehicle_id == v.id ? ' selected' : '') + '>'
                    + v.code + ' — ' + v.name + '</option>';
            }).join('');
            var mecOpts = fd.mecanicos.map(function (m) {
                return '<option value="' + m.id + '"' + (h && h.responsable_id == m.id ? ' selected' : '') + '>'
                    + m.name + '</option>';
            }).join('');
            var tipos   = ['Preventivo','Limpieza','Reparación'];
            var tipoOpts = tipos.map(function (t) {
                return '<option value="' + t + '"' + (h && h.tipo === t ? ' selected' : '') + '>' + t + '</option>';
            }).join('');
            var dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
            var diaOpts = dias.map(function (d) {
                return '<option value="' + d + '"' + (h && h.dia_semana === d ? ' selected' : '') + '>' + d + '</option>';
            }).join('');

            return `
                <div class="card border p-3" style="border-radius:10px;background:#F8FAFC;">
                    <input type="hidden" id="horarioEditId" value="${h ? h.id : ''}">
                    <div class="row">
                        <div class="col-md-4 form-group mb-2">
                            <label class="font-weight-bold text-xs text-secondary text-uppercase">Vehículo *</label>
                            <select id="hVehicle" class="form-control form-control-sm">
                                <option value="">-- Seleccione --</option>${vehicleOpts}
                            </select>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <label class="font-weight-bold text-xs text-secondary text-uppercase">Responsable *</label>
                            <select id="hResponsable" class="form-control form-control-sm">
                                <option value="">-- Seleccione --</option>${mecOpts}
                            </select>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <label class="font-weight-bold text-xs text-secondary text-uppercase">Tipo *</label>
                            <select id="hTipo" class="form-control form-control-sm">
                                <option value="">-- Seleccione --</option>${tipoOpts}
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-2">
                            <label class="font-weight-bold text-xs text-secondary text-uppercase">Día *</label>
                            <select id="hDia" class="form-control form-control-sm">
                                <option value="">-- Seleccione --</option>${diaOpts}
                            </select>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <label class="font-weight-bold text-xs text-secondary text-uppercase">Hora Inicio *</label>
                            <input type="time" id="hHoraInicio" class="form-control form-control-sm" value="${h ? h.hora_inicio : ''}">
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <label class="font-weight-bold text-xs text-secondary text-uppercase">Hora Fin *</label>
                            <input type="time" id="hHoraFin" class="form-control form-control-sm" value="${h ? h.hora_fin : ''}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="btn-cancelar-horario">Cancelar</button>
                        <button type="button" class="btn btn-primary btn-sm font-weight-bold" id="btn-guardar-horario">
                            <i class="fas fa-save mr-1"></i> ${h ? 'Actualizar' : 'Guardar'} Horario
                        </button>
                    </div>
                </div>`;
        }

        // ── Abrir form nuevo horario ──────────────────────────
        $(document).on('click', '#btn-nuevo-horario', function () {
            $('#form-horario-wrap').html(buildFormHorario(null)).removeClass('d-none');
        });

        $(document).on('click', '#btn-cancelar-horario', function () {
            $('#form-horario-wrap').addClass('d-none').html('');
        });

        // ── Guardar horario ───────────────────────────────────
        $(document).on('click', '#btn-guardar-horario', function () {
            var id  = $('#horarioEditId').val();
            var url = id
                ? URL_BASE + '/horarios/' + id
                : URL_BASE + '/' + mantActivoId + '/horarios';
            var method = id ? 'PUT' : 'POST';

            $.ajax({
                url:  url,
                type: method,
                data: {
                    _token:         CSRF,
                    vehicle_id:     $('#hVehicle').val(),
                    responsable_id: $('#hResponsable').val(),
                    tipo:           $('#hTipo').val(),
                    dia_semana:     $('#hDia').val(),
                    hora_inicio:    $('#hHoraInicio').val(),
                    hora_fin:       $('#hHoraFin').val(),
                },
                success: function (res) {
                    $('#form-horario-wrap').addClass('d-none').html('');
                    cargarHorarios(mantActivoId);
                    Swal.fire('¡Listo!', res.message, 'success');
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error');
                }
            });
        });

        // ── Editar horario ────────────────────────────────────
        $(document).on('click', '.btn-editar-horario', function () {
            var id = $(this).data('id');

            $.get(URL_BASE + '/' + mantActivoId + '/horarios', function (data) {

                var h = data.horarios.find(function (x) {
                    return x.id == id;
                });

                if (!h) return;

                $('#form-horario-wrap')
                    .html(buildFormHorario(h))
                    .removeClass('d-none');

                $('#horarioEditId').val(id);
            });
        });

        // ── Eliminar horario ──────────────────────────────────
        $(document).on('click', '.btn-eliminar-horario', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: '¿Eliminar horario?',
                text:  'Se eliminarán también todos los días generados para este horario.',
                icon:  'warning',
                showCancelButton:  true,
                confirmButtonColor: '#dc3545',
                cancelButtonText:  'Cancelar',
                confirmButtonText: 'Sí, eliminar',
            }).then(function (r) {
                if (!r.isConfirmed) return;
                $.ajax({
                    url:  URL_BASE + '/horarios/' + id,
                    type: 'DELETE',
                    data: { _token: CSRF },
                    success: function (res) {
                        cargarHorarios(mantActivoId);
                        Swal.fire('Eliminado', res.message, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error');
                    }
                });
            });
        });

        // ── Ver detalles (días generados) ─────────────────────
        $(document).on('click', '.btn-ver-detalles', function () {
            horarioActivoId = $(this).data('id'); // ← AGREGAR ESTA LÍNEA
            $.get(URL_BASE + '/horarios/' + horarioActivoId + '/detalles', function (data) {
                $('#DetallesModalTitle').html(
                    '<i class="fas fa-calendar-alt mr-2"></i>'
                    + data.horario.mantenimiento + ' — ' + data.horario.dia_semana
                    + ' — ' + data.horario.vehicle
                );

                var rows = data.detalles.length
                    ? data.detalles.map(function (d) {
                        var realizadoBadge = d.realizado
                            ? '<span class="badge badge-success px-2">Realizado</span>'
                            : '<span class="badge badge-secondary px-2">Pendiente</span>';
                        var imgHtml = d.imagen
                            ? '<a href="' + d.imagen + '" target="_blank"><img src="' + d.imagen + '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;"></a>'
                            : '<span class="text-muted" style="font-size:.75rem;">Sin imagen</span>';
                        return '<tr>'
                            + '<td style="font-size:.82rem;">' + d.fecha + '</td>'
                            + '<td style="font-size:.82rem;">' + (d.observacion || '<span class="text-muted">—</span>') + '</td>'
                            + '<td>' + imgHtml + '</td>'
                            + '<td>' + realizadoBadge + '</td>'
                            + '<td class="text-center">'
                            + '<button class="btn btn-sm btn-warning btn-editar-detalle" data-id="' + d.id + '" '
                            + 'data-fecha="' + d.fecha + '" data-obs="' + (d.observacion || '') + '" '
                            + 'data-realizado="' + (d.realizado ? '1' : '0') + '" '
                            + 'data-img="' + (d.imagen || '') + '">'
                            + '<i class="fas fa-pen text-dark"></i></button>'
                            + '</td>'
                            + '</tr>';
                    }).join('')
                    : '<tr><td colspan="5" class="text-center text-muted py-3">Sin días generados.</td></tr>';

                $('#DetallesModalBody').html(`
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="font-size:.75rem;">Fecha</th>
                                    <th style="font-size:.75rem;">Observación</th>
                                    <th style="font-size:.75rem;">Imagen</th>
                                    <th style="font-size:.75rem;">Estado</th>
                                    <th style="font-size:.75rem;" class="text-center">Edit</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                `);

                $('#DetallesModal').modal('show');
            });
        });

        // ── Editar detalle ────────────────────────────────────
        $(document).on('click', '.btn-editar-detalle', function () {
            var $btn = $(this);
            $('#detalleId').val($btn.data('id'));
            $('#detalleFecha').val($btn.data('fecha'));
            $('#detalleObservacion').val($btn.data('obs'));
            $('#detalleRealizado').prop('checked', $btn.data('realizado') == '1');

            var img = $btn.data('img');
            $('#detalleImagenPreview').html(img
                ? '<img src="' + img + '" style="max-width:100%;max-height:120px;border-radius:8px;" class="mt-1">'
                : '');

            $('#EditDetalleModal').modal('show');
        });

        // ── Submit detalle ────────────────────────────────────
        $('#formDetalle').on('submit', function (e) {
            e.preventDefault();
            var id = $('#detalleId').val();
            var fd = new FormData();
            fd.append('_token',      CSRF);
            fd.append('_method',     'POST');
            fd.append('observacion', $('#detalleObservacion').val());
            fd.append('realizado',   $('#detalleRealizado').is(':checked') ? '1' : '0');
            if ($('#detalleImagen')[0].files[0]) {
                fd.append('imagen', $('#detalleImagen')[0].files[0]);
            }

            $.ajax({
                url:         URL_BASE + '/detalles/' + id,
                type:        'POST',
                data:        fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#EditDetalleModal').modal('hide');
                    // Recargar la tabla de detalles correctamente
                    if (horarioActivoId) {
                        $.get(URL_BASE + '/horarios/' + horarioActivoId + '/detalles', function (data) {
                            // copiar el mismo bloque de renderizado de filas que usas en btn-ver-detalles
                            var rows = data.detalles.length
                                ? data.detalles.map(function (d) {
                                    var realizadoBadge = d.realizado
                                        ? '<span class="badge badge-success px-2">Realizado</span>'
                                        : '<span class="badge badge-secondary px-2">Pendiente</span>';
                                    var imgHtml = d.imagen
                                        ? '<a href="' + d.imagen + '" target="_blank"><img src="' + d.imagen + '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;"></a>'
                                        : '<span class="text-muted" style="font-size:.75rem;">Sin imagen</span>';
                                    return '<tr>'
                                        + '<td style="font-size:.82rem;">' + d.fecha + '</td>'
                                        + '<td style="font-size:.82rem;">' + (d.observacion || '<span class="text-muted">—</span>') + '</td>'
                                        + '<td>' + imgHtml + '</td>'
                                        + '<td>' + realizadoBadge + '</td>'
                                        + '<td class="text-center">'
                                        + '<button class="btn btn-sm btn-warning btn-editar-detalle" data-id="' + d.id + '" '
                                        + 'data-fecha="' + d.fecha + '" data-obs="' + (d.observacion || '') + '" '
                                        + 'data-realizado="' + (d.realizado ? '1' : '0') + '" '
                                        + 'data-img="' + (d.imagen || '') + '">'
                                        + '<i class="fas fa-pen text-dark"></i></button>'
                                        + '</td>'
                                        + '</tr>';
                                }).join('')
                                : '<tr><td colspan="5" class="text-center text-muted py-3">Sin días generados.</td></tr>';

                            $('#DetallesModal tbody').html(rows);
                        });
                    }
                    Swal.fire('¡Listo!', res.message, 'success');
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error');
                }
            });
        });

    });
    </script>
@endsection