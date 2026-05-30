@extends('adminlte::page')

@section('title', 'RSU JLO - Tipos de Vehículos')

@section('content')
<div class="container-fluid">
    <div class="p-2"></div>

    <div class="card card-dark shadow">
        <div class="card-header">
            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo-tipo">
                <i class="fas fa-plus"></i> Nuevo Tipo
            </button>
            <h4><i class="fas fa-truck"></i> Lista de Tipos de Vehículos</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tblTipos" class="table table-bordered table-striped table-hover w-100">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="text-center align-middle" width="30%">Nombre</th>
                            <th class="text-center align-middle" width="55%">Descripción</th>
                            <th class="text-center align-middle" width="15%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="TipoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="TipoModalTitle">Formulario de Tipo de Vehículo</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>

<div class="p-2"></div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {

            // ── DataTable ──────────────────────────────────────────────
            $('#tblTipos').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.tipo-vehiculo.index') }}",
                columns: [
                    { data: 'name' },
                    { data: 'description', defaultContent: '<i class="text-muted">Sin descripción</i>' },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center text-nowrap' },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
                },
            });

            // ── Nuevo Tipo ─────────────────────────────────────────────
            $('#btn-nuevo-tipo').click(function () {
                $.ajax({
                    url: "{{ route('admin.tipo-vehiculo.create') }}",
                    type: 'GET',
                    success: function (response) {
                        $('#TipoModal #TipoModalTitle').html('<i class="fas fa-plus"></i> Nuevo Tipo de Vehículo');
                        $('#TipoModal .modal-body').html(response);
                        $('#TipoModal').modal('show');

                        $('#TipoModal form').on('submit', function (e) {
                            e.preventDefault();
                            var form = $(this);
                            $.ajax({
                                url: form.attr('action'),
                                type: form.attr('method'),
                                data: form.serialize(),
                                success: function (res) {
                                    $('#TipoModal').modal('hide');
                                    refreshTable();
                                    Swal.fire('¡Registro Exitoso!', res.message, 'success');
                                },
                                error: function (xhr) {
                                    var res = xhr.responseJSON;
                                    var msg = 'Ocurrió un inconveniente al guardar el tipo.';
                                    if (xhr.status === 422 && res.message) msg = res.message;
                                    Swal.fire({ title: 'Tipo Duplicado o Inválido', text: msg, icon: 'error' });
                                }
                            });
                        });
                    }
                });
            });
        });

        // ── Editar ─────────────────────────────────────────────────────
        $(document).on('click', '.btn-editar', function () {
            var id = $(this).attr('id');
            $.ajax({
                url: "{{ route('admin.tipo-vehiculo.edit', 'id') }}".replace('id', id),
                type: 'GET',
                success: function (response) {
                    $('#TipoModal #TipoModalTitle').html('<i class="fas fa-edit"></i> Modificar Tipo de Vehículo');
                    $('#TipoModal .modal-body').html(response);
                    $('#TipoModal').modal('show');

                    $('#TipoModal form').on('submit', function (e) {
                        e.preventDefault();
                        var form = $(this);
                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: form.serialize(),
                            success: function (res) {
                                $('#TipoModal').modal('hide');
                                refreshTable();
                                Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                            },
                            error: function (xhr) {
                                var res = xhr.responseJSON;
                                var msg = 'Ocurrió un inconveniente al actualizar el tipo.';
                                if (xhr.status === 422 && res.message) msg = res.message;
                                Swal.fire({ title: 'Tipo Duplicado o Inválido', text: msg, icon: 'error' });
                            }
                        });
                    });
                },
                error: function () {
                    Swal.fire('Error', 'No se pudieron recuperar los datos del tipo.', 'error');
                }
            });
        });

        // ── Eliminar ───────────────────────────────────────────────────
        $(document).on('submit', '.frmEliminar', function (e) {
            e.preventDefault();
            var form = $(this);
            Swal.fire({
                title: '¿Está seguro de Eliminar?',
                text: '¡Esta acción removerá el tipo de vehículo del catálogo de forma permanente!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        success: function (res) {
                            refreshTable();
                            Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                        },
                        error: function () {
                            Swal.fire('Error', 'No se pudo eliminar el registro en el servidor.', 'error');
                        }
                    });
                }
            });
        });

        function refreshTable() {
            $('#tblTipos').DataTable().ajax.reload(null, false);
        }
    </script>
@endsection