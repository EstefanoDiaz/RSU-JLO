@extends('adminlte::page')

@section('title', 'RSU JLO - Mantenimientos')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-tools mr-2 text-white-75"></i> Lista de Mantenimientos
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3.5 py-2 shadow-sm ml-auto" id="btn-nuevo-mantenimiento">
                <i class="fas fa-plus mr-1.5"></i> Nuevo Mantenimiento
            </button>
        </div>
                
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblMaintenances" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="align-middle" width="45%">Nombre</th>
                            <th class="text-center align-middle" width="20%">Fecha Inicio</th>
                            <th class="text-center align-middle" width="20%">Fecha Fin</th>
                            <th class="text-center align-middle" width="15%">Acciones</th> 
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="MaintenanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold" id="MaintenanceModalTitle">Formulario de Mantenimiento</h5>
                <button type="button" class="close text-white opacity-80" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-light-panel"></div>
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
            var table = $('#tblMaintenances').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.maintenance.index') }}",
                columns: [
                    { data: "name", className: 'align-middle font-weight-bold text-dark-blue' },
                    { data: "start_date", className: 'text-center align-middle' },
                    { data: "end_date", className: 'text-center align-middle' },
                    { data: "actions", orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }, 
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json' },
            });

            // Acción: Nuevo Registro
            $('#btn-nuevo-mantenimiento').click(function() {
                $.ajax({
                    url: "{{ route('admin.maintenance.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#MaintenanceModal #MaintenanceModalTitle').html('<i class="fas fa-plus-circle mr-1.5"></i> Nuevo Mantenimiento');
                        $('#MaintenanceModal .modal-body').html(response);
                        $('#MaintenanceModal').modal("show");
                        attachFormEvent();
                    }
                });
            });

            // Acción: Editar Registro
            $(document).on('click', '.btn-editar', function() {
                var id = $(this).attr("id");
                $.ajax({
                    url: "{{ route('admin.maintenance.edit', 'id') }}".replace('id', id),
                    type: "GET",
                    success: function(response) {
                        $('#MaintenanceModal #MaintenanceModalTitle').html('<i class="fas fa-edit mr-1.5"></i> Editar Registro');
                        $('#MaintenanceModal .modal-body').html(response);
                        $('#MaintenanceModal').modal("show");
                        attachFormEvent();
                    }
                });
            });

            // Acción para el botón Horario 
$(document).on('click', '.btn-horario', function() {
    var id = $(this).attr("id");
    
    window.location.href = "{{ url('maintenance') }}/" + id + "/schedules";
});

            function attachFormEvent() {
                $('#MaintenanceModal form').on("submit", function(e) {
                    e.preventDefault();
                    var form = $(this);
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        success: function(res) {
                            $('#MaintenanceModal').modal("hide");
                            table.ajax.reload(null, false);
                            Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error en el proceso.';
                            Swal.fire({ title: 'Atención', text: msg, icon: 'error' });
                        }
                    });
                });
            }

            // Acción: Eliminar Registro
            $(document).on('submit', '.frmEliminar', function(e) {
                e.preventDefault();
                var form = $(this);
                Swal.fire({
                    title: "¿Está seguro de Eliminar?",
                    text: "¡Esta acción removerá el mantenimiento de forma permanente!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#071D38",
                    cancelButtonColor: "#a13825",
                    confirmButtonText: "Sí, ¡eliminar!",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'), 
                            data: form.serialize(), 
                            success: function(res) {
                                table.ajax.reload(null, false); 
                                Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                            },
                            error: function(xhr) {
                                Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection