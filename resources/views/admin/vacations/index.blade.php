@extends('adminlte::page')

@section('title', 'RSU JLO - Vacaciones')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white"><i class="fas fa-umbrella-beach mr-2"></i> Gestión de Vacaciones del Personal</h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3.5 py-2 shadow-sm ml-auto" id="btn-nueva-vacacion">
                <i class="fas fa-plus mr-1.5"></i> Nueva Solicitud
            </button>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblVacations" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th>DNI</th>
                            <th>Empleado</th>
                            <th>F. Solicitud</th>
                            <th>F. Inicio</th>
                            <th>F. Término</th>
                            <th class="text-center">Días</th>
                            <th>Estado</th>
                            <th>Días R.</th>
                            <th>Notes</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="VacationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header text-white py-3" style="background-color: #071D38;">
                <h5 class="modal-title font-weight-bold" id="VacationModalTitle">Formulario de Vacación</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body p-4 bg-white"></div>
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
        $(document).ready(function() {
            // Inicializar DataTable asíncrono cruzado
            $('#tblVacations').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.vacation.index') }}",
                columns: [
                    { data: "dni", className: "align-middle font-weight-bold text-dark-blue" },
                    { data: "employee_name", className: "align-middle" },
                    { data: "request_date", className: "align-middle" },
                    { data: "start_date", className: "align-middle" },
                    { data: "end_date", className: "align-middle" },
                    { data: "days", className: "text-center align-middle font-weight-bold" },
                    { data: "badge_status", className: "align-middle" },
                    { data: "available_days", className: "align-middle text-center" },
                    { data: "notes", className: "align-middle text-muted", defaultContent: '<i>-</i>' },
                    { data: "actions", orderable: false, searchable: false, className: "text-center align-middle text-nowrap" }
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            // ACCIÓN: ABRIR MODAL CREAR
            $('#btn-nueva-vacacion').click(function() {
                $.ajax({
                    url: "{{ route('admin.vacation.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#VacationModal #VacationModalTitle').html('<i class="fas fa-plus-circle"></i> Nueva Solicitud de Vacaciones');
                        $('#VacationModal .modal-body').html(response);
                        $('#VacationModal').modal("show");

                        $('#VacationModal form').on("submit", function(e) {
                            e.preventDefault();
                            $.ajax({
                                url: $(this).attr('action'),
                                type: $(this).attr('method'),
                                data: $(this).serialize(),
                                success: function(res) {
                                    $('#VacationModal').modal("hide");
                                    refreshTable();
                                    Swal.fire('¡Registrado!', res.message, 'success');
                                },
                                error: function(xhr) {
                                    Swal.fire('Restricción', xhr.responseJSON.message || 'Error', 'error');
                                }
                            });
                        });
                    }
                });
            });
        });

        // ACCIÓN: BOTÓN CHECK (APROBAR)
        $(document).on('click', '.btn-aprobar', function() {
            let id = $(this).attr('id');
            Swal.fire({
                title: '¿Aprobar solicitud de vacaciones?',
                text: "Los días de descanso solicitados se restaran a los días disponibles de vacaciones del usuario.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#071D38',
                confirmButtonText: 'Sí, aprobar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/vacation') }}/" + id + "/approve",
                        type: "POST",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) { refreshTable(); Swal.fire('Aprobado', res.message, 'success'); },
                        error: function(xhr) { Swal.fire('Error', xhr.responseJSON.message, 'error'); }
                    });
                }
            });
        });

        // ACCIÓN: BOTÓN CANCELADO (RECHAZAR)
        $(document).on('click', '.btn-rechazar', function() {
            let id = $(this).attr('id');
            Swal.fire({
                title: '¿Rechazar solicitud de vacaciones?',
                text: "La solicitud cambiará a estado RECHAZADA. Los días disponibles de vaciones del usuario se mantendrá intacto.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#a13825',
                confirmButtonText: 'Sí, rechazar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/vacation') }}/" + id + "/reject",
                        type: "POST",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) { refreshTable(); Swal.fire('Rechazado', res.message, 'success'); }
                    });
                }
            });
        });

        // ACCIÓN: BOTÓN EDITAR
        $(document).on('click', '.btn-editar', function() {
            let id = $(this).attr('id');
            $.ajax({
                url: "{{ route('admin.vacation.edit', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    $('#VacationModal #VacationModalTitle').html('<i class="fas fa-edit"></i> Modificar Solicitud');
                    $('#VacationModal .modal-body').html(response);
                    $('#VacationModal').modal("show");

                    $('#VacationModal form').on("submit", function(e) {
                        e.preventDefault();
                        $.ajax({
                            url: $(this).attr('action'),
                            type: 'POST',
                            data: $(this).serialize(),
                            success: function(res) {
                                $('#VacationModal').modal("hide");
                                refreshTable();
                                Swal.fire('Actualizado', res.message, 'success');
                            },
                            error: function(xhr) { Swal.fire('Error', xhr.responseJSON.message, 'error'); }
                        });
                    });
                }
            });
        });

        // ACCIÓN: BOTÓN ELIMINAR
        $(document).on('submit', '.frmEliminar', function(e) {
            e.preventDefault();
            let form = $(this);
            Swal.fire({
                title: '¿Eliminar solicitud?',
                text: "La solicitud se removerá de forma permanente del listado de control.",
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
                        success: function(res) { refreshTable(); Swal.fire('Eliminado', res.message, 'success'); }
                    });
                }
            });
        });

        function refreshTable() {
            $('#tblVacations').DataTable().ajax.reload(null, false);
        }
    </script>
@endsection