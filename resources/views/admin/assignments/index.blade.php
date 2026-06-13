@extends('adminlte::page')

@section('title', 'RSU JLO - Lista de Programaciones')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">

        {{-- Header --}}
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between flex-wrap py-3" style="gap:.5rem;">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="far fa-calendar-alt mr-2"></i> Lista de Programaciones
            </h4>
            <div class="ml-auto d-flex" style="gap:.5rem;">
                <!-- <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light font-weight-bold">
                    <i class="fas fa-tachometer-alt mr-1"></i> Ir al Dashboard
                </a> -->
                <button type="button" class="btn btn-success font-weight-bold" id="btn-nueva-programacion">
                    <i class="fas fa-plus mr-1"></i> Nueva Programación
                </button>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="card-body border-bottom bg-light py-3">
            <div class="row align-items-end">
                <div class="col-md-2 form-group mb-0">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de Inicio</label>
                    <input type="date" id="filtroStart" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 form-group mb-0">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de Fin</label>
                    <input type="date" id="filtroEnd" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 form-group mb-0">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Zona</label>
                    <select id="filtroZone" class="form-control form-control-sm">
                        <option value="">Todas las zonas</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-0">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Turno</label>
                    <select id="filtroSchedule" class="form-control form-control-sm">
                        <option value="">Todos los turnos</option>
                        @foreach($schedules as $schedule)
                            <option value="{{ $schedule->id }}">{{ $schedule->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group mb-0">
                    <button id="btnFiltrar" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                    <button id="btnLimpiar" class="btn btn-outline-secondary btn-sm w-100 mt-1">
                        <i class="fas fa-times mr-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblAssignments" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Zona</th>
                            <th>Turnos</th>
                            <th>Vehículo</th>
                            <th>Conductor</th>
                            <th>Ayudantes</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Nueva / Editar --}}
<div class="modal fade" id="AssignmentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header custom-modal-header text-white py-3" style="">
                <h5 class="modal-title font-weight-bold" id="TipoModalTitle">
                    <i class="fas fa-calendar-plus mr-1"></i> Programación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-white" id="AssignmentModalBody"></div>
        </div>
    </div>
</div>

{{-- Modal Ver Detalle --}}
<div class="modal fade" id="DetalleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header py-3" style="background-color: #071D38;">
                <h5 class="modal-title font-weight-bold text-white">
                    <i class="fas fa-info-circle mr-1"></i> Detalle de Programación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4" id="DetalleModalBody"></div>
        </div>
    </div>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('custom-crud.css') }}">
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tableAssignment;

        $(document).ready(function() {
            tableAssignment = $('#tblAssignments').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.assignment.index') }}",
                    data: function(d) {
                        d.start_date  = $('#filtroStart').val();
                        d.end_date    = $('#filtroEnd').val();
                        d.zone_id     = $('#filtroZone').val();
                        d.schedule_id = $('#filtroSchedule').val();
                    }
                },
                columns: [
                    { data: "fecha_fmt",       className: "align-middle font-weight-bold" },
                    { data: "badge_status",    className: "align-middle" },
                    { data: "zona_name",       className: "align-middle" },
                    { data: "turno_name",      className: "align-middle" },
                    { data: "vehicle_name",    className: "align-middle" },
                    { data: "conductor_name",  className: "align-middle" },
                    { data: "ayudantes_names", className: "align-middle" },
                    { data: "actions", orderable: false, searchable: false, className: "text-center align-middle text-nowrap" }
                ],
                order: [[0, 'desc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            $('#btnFiltrar').click(function() { tableAssignment.ajax.reload(); });
            $('#btnLimpiar').click(function() {
                $('#filtroStart, #filtroEnd').val('');
                $('#filtroZone, #filtroSchedule').val('');
                tableAssignment.ajax.reload();
            });

            // Nueva Programación
            $('#btn-nueva-programacion').click(function() {
                $.get("{{ route('admin.assignment.create') }}", function(response) {
                    $('#AssignmentModalTitle').html('<i class="fas fa-calendar-plus mr-1"></i> Nueva Programación');
                    $('#AssignmentModalBody').html(response);
                    $('#AssignmentModal').modal('show');
                });
            });

            // Ver Detalle
            $(document).on('click', '.btn-ver', function() {
                let id = $(this).data('id');
                $.get("{{ route('admin.assignment.show', 'ID') }}".replace('ID', id), function(data) {
                    let html = `
                        <table class="table table-bordered table-sm">
                            <tr><th class="bg-light" style="width:40%">Fecha</th><td>${data.date}</td></tr>
                            <tr><th class="bg-light">Estado</th><td>${data.status}</td></tr>
                            <tr><th class="bg-light">Grupo</th><td>${data.group?.name ?? '-'}</td></tr>
                            <tr><th class="bg-light">Zona</th><td>${data.zone?.name ?? '-'}</td></tr>
                            <tr><th class="bg-light">Turno</th><td>${data.schedule?.name ?? '-'}</td></tr>
                            <tr><th class="bg-light">Vehículo</th><td>${data.vehicle?.name ?? '-'} - ${data.vehicle?.code ?? ''}</td></tr>
                            <tr><th class="bg-light">Conductor</th><td>${data.conductor?.name ?? '-'}</td></tr>
                            <tr><th class="bg-light">Ayudante 1</th><td>${data.ayudante1?.name ?? '-'}</td></tr>
                            <tr><th class="bg-light">Ayudante 2</th><td>${data.ayudante2?.name ?? '-'}</td></tr>
                            <tr><th class="bg-light">Observaciones</th><td>${data.observations ?? '-'}</td></tr>
                        </table>`;
                    $('#DetalleModalBody').html(html);
                    $('#DetalleModal').modal('show');
                });
            });

            // Editar
            $(document).on('click', '.btn-editar', function() {
                let id = $(this).data('id');
                $.get("{{ route('admin.assignment.edit', 'ID') }}".replace('ID', id), function(response) {
                    $('#AssignmentModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Programación');
                    $('#AssignmentModalBody').html(response);
                    $('#AssignmentModal').modal('show');

                    $('#AssignmentModal form').off('submit').on('submit', function(e) {
                        e.preventDefault();
                        $.ajax({
                            url: $(this).attr('action'),
                            type: 'POST',
                            data: $(this).serialize(),
                            success: function(res) {
                                $('#AssignmentModal').modal('hide');
                                tableAssignment.ajax.reload(null, false);
                                Swal.fire('¡Actualizado!', res.message, 'success');
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error');
                            }
                        });
                    });
                });
            });

            // Finalizar
            $(document).on('click', '.btn-finalizar', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: '¿Finalizar programación?',
                    text: 'El estado cambiará a Finalizado y no podrá revertirse.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Sí, finalizar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/assignment') }}/" + id + "/finalizar",
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(res) {
                                tableAssignment.ajax.reload(null, false);
                                Swal.fire('Finalizado', res.message, 'success');
                            },
                            error: function(xhr) { Swal.fire('Error', xhr.responseJSON?.message, 'error'); }
                        });
                    }
                });
            });

            // Eliminar
            $(document).on('submit', '.frmEliminar', function(e) {
                e.preventDefault();
                let form = $(this);
                Swal.fire({
                    title: '¿Eliminar programación?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#a13825',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(res) {
                                tableAssignment.ajax.reload(null, false);
                                Swal.fire('Eliminado', res.message, 'success');
                            },
                            error: function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error'); }
                        });
                    }
                });
            });
        });
    </script>
@endsection
