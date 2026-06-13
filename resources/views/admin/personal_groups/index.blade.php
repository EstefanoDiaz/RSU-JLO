@extends('adminlte::page')

@section('title', 'RSU JLO - Grupos de Personal')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white"><i class="fas fa-users mr-2"></i> Grupos de Personal</h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm ml-auto" id="btn-nuevo-grupo">
                <i class="fas fa-plus mr-1"></i> Nuevo Grupo
            </button>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblGroups" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Zona</th>
                            <th>Turno</th>
                            <th>Vehículo</th>
                            <th>Conductor</th>
                            <th>Ayudantes</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="GroupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header text-white py-3" style="background-color: #071D38;">
                <h5 class="modal-title font-weight-bold" id="GroupModalTitle">Grupo de Personal</h5>
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
            $('#tblGroups').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.personal-group.index') }}",
                columns: [
                    { data: "id", className: "align-middle text-muted" },
                    { data: "name", className: "align-middle font-weight-bold" },
                    { data: "zona_name", className: "align-middle" },
                    { data: "schedule_name", className: "align-middle" },
                    { data: "vehicle_name", className: "align-middle" },
                    { data: "conductor_name", className: "align-middle" },
                    { data: "ayudantes_names", className: "align-middle" },
                    { data: "badge_status", className: "text-center align-middle" },
                    { data: "actions", orderable: false, searchable: false, className: "text-center align-middle text-nowrap" }
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            $('#btn-nuevo-grupo').click(function() {
                $.get("{{ route('admin.personal-group.create') }}", function(response) {
                    $('#GroupModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nuevo Grupo de Personal');
                    $('#GroupModal .modal-body').html(response);
                    $('#GroupModal').modal('show');

                    $('#GroupModal form').off('submit').on('submit', function(e) {
                        e.preventDefault();
                        $.ajax({
                            url: $(this).attr('action'),
                            type: $(this).attr('method'),
                            data: $(this).serialize(),
                            success: function(res) {
                                $('#GroupModal').modal('hide');
                                refreshTable();
                                Swal.fire('¡Registrado!', res.message, 'success');
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                            }
                        });
                    });
                });
            });

            $(document).on('click', '.btn-editar', function() {
                let id = $(this).data('id');
                $.get("{{ route('admin.personal-group.edit', 'ID') }}".replace('ID', id), function(response) {
                    $('#GroupModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Grupo de Personal');
                    $('#GroupModal .modal-body').html(response);
                    $('#GroupModal').modal('show');

                    $('#GroupModal form').off('submit').on('submit', function(e) {
                        e.preventDefault();
                        $.ajax({
                            url: $(this).attr('action'),
                            type: 'POST',
                            data: $(this).serialize(),
                            success: function(res) {
                                $('#GroupModal').modal('hide');
                                refreshTable();
                                Swal.fire('¡Actualizado!', res.message, 'success');
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error');
                            }
                        });
                    });
                });
            });

            $(document).on('submit', '.frmEliminar', function(e) {
                e.preventDefault();
                let form = $(this);
                Swal.fire({
                    title: '¿Eliminar grupo?',
                    text: 'Se eliminará permanentemente el grupo y sus programaciones asociadas.',
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
                            success: function(res) { refreshTable(); Swal.fire('Eliminado', res.message, 'success'); },
                            error: function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error'); }
                        });
                    }
                });
            });
        });

        function refreshTable() {
            $('#tblGroups').DataTable().ajax.reload(null, false);
        }
    </script>
@endsection
