@extends('adminlte::page')

@section('title', 'RSU JLO - Colores')

@section('content')
<div class="container-fluid">
    <div class="p-2"></div>
    
    <div class="card card-dark shadow">
        <div class="card-header">
            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo-color">
                <i class="fas fa-plus"></i> Nuevo Color
            </button>
            <h4><i class="fas fa-palette"></i> Lista de Colores</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tblColors" class="table table-bordered table-striped table-hover w-100">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="text-center align-middle" width="10%">Color</th>
                            <th class="text-center align-middle" width="20%">Nombre</th>
                            <th class="text-center align-middle" width="10%">Código</th>
                            <th class="text-center align-middle" width="50%">Descripción</th>
                            <th class="text-center align-middle" width="10%">Acciones</th> 
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ColorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="ColorModalTitle">Formulario de Color</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <script>
        $(document).ready(function() {
            $('#tblColors').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.color.index') }}",
                columns: [
                    { 
                        data: "code",
                        orderable: false, 
                        searchable: false,
                        className: 'text-center',
                        render: function(data) {
                            return `<div class="shadow-sm rounded mx-auto border" style="background-color: ${data}; width: 35px; height: 20px;" title="${data}"></div>`;
                        }
                    },
                    { data: "name" },
                    { 
                        data: "code",
                        className: 'text-center',
                        render: function(data) {
                            return `<span class="badge badge-light border px-2 py-1">${data}</span>`;
                        }
                    },
                    { data: "description", defaultContent: '<i class="text-muted">Sin descripción</i>' },
                    { data: "actions", orderable: false, searchable: false, className: 'text-center text-nowrap' }, 
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
                },
            });

            $('#btn-nuevo-color').click(function() {
                $.ajax({
                    url: "{{ route('admin.color.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#ColorModal #ColorModalTitle').html('<i class="fas fa-plus"></i> Nuevo Color');
                        $('#ColorModal .modal-body').html(response);
                        $('#ColorModal').modal("show");

                        $('#ColorModal form').on("submit", function(e) {
                            e.preventDefault();
                            var form = $(this);

                            $.ajax({
                                url: form.attr('action'),
                                type: form.attr('method'),
                                data: form.serialize(),
                                success: function(res) {
                                    $('#ColorModal').modal("hide");
                                    refreshTable();
                                    Swal.fire('¡Registro Exitoso!', res.message, 'success');
                                },
                                error: function(xhr) {
                                    var res = xhr.responseJSON;
                                    var msg = 'Ocurrió un inconveniente al guardar el color.';
                                    
                                    if (xhr.status === 422 && res.message) {
                                        msg = res.message;
                                    }
                                    Swal.fire({ title: 'Color Duplicado o Inválido', text: msg, icon: 'error' });
                                }
                            });
                        });
                    }
                });
            });

        });

        $(document).on('click', '.btn-editar', function() {
            var id = $(this).attr("id");
            
            $.ajax({
                url: "{{ route('admin.color.edit', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    $('#ColorModal #ColorModalTitle').html('<i class="fas fa-edit"></i> Modificar Color');
                    $('#ColorModal .modal-body').html(response);
                    $('#ColorModal').modal("show");

                    $('#ColorModal form').on("submit", function(e) {
                        e.preventDefault();
                        var form = $(this);

                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: form.serialize(),
                            success: function(res) {
                                $('#ColorModal').modal("hide");
                                refreshTable(); 
                                Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                            },
                            error: function(xhr) {
                                var res = xhr.responseJSON;
                                var msg = 'Ocurrió un inconveniente al actualizar el color.';
                                
                                if (xhr.status === 422 && res.message) {
                                    msg = res.message;
                                }
                                Swal.fire({ title: 'Color Duplicado o Inválido', text: msg, icon: 'error' });
                            }
                        });
                    });
                },
                error: function() {
                    Swal.fire('Error', 'No se pudieron recuperar los datos del color.', 'error');
                }
            });
        });

        $(document).on('submit', '.frmEliminar', function(e) {
            e.preventDefault();
            var form = $(this); 
            
            Swal.fire({
                title: "¿Está seguro de Eliminar?",
                text: "¡Esta acción removerá el color del catálogo de forma permanente!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, ¡eliminar!",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'), 
                        data: form.serialize(), 
                        success: function(res) {
                            refreshTable(); 
                            Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'No se pudo eliminar el registro en el servidor.', 'error');
                        }
                    });
                }
            });
        });

        function refreshTable() {
            $('#tblColors').DataTable().ajax.reload(null, false);
        }
    </script>
@endsection