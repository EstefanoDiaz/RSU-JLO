@extends('adminlte::page')

@section('title', 'RSU JLO - Zonas')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-map-marked-alt mr-2 text-white-75"></i> Lista de Zonas
            </h4>
            <div class="ml-auto d-flex align-items-center" style="gap: 12px;">
                <button type="button" class="btn btn-success font-weight-bold px-3.5 py-2 shadow-sm d-flex align-items-center" id="btn-ver-mapa" style="border-radius: 8px;">
                    <i class="fas fa-map mr-1.5"></i> Ver Mapa de Zonas
                </button>
                
                <button type="button" class="btn btn-action-add font-weight-bold px-3.5 py-2 shadow-sm d-flex align-items-center" id="btn-nueva-zona" style="border-radius: 8px;">
                    <i class="fas fa-plus mr-1.5"></i> Nueva Zona
                </button>
            </div>
        </div>
                
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblZones" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="align-middle">NOMBRE</th>
                            <th class="align-middle">DISTRITO</th>
                            <th class="align-middle">PROVINCIA</th>
                            <th class="align-middle">DEPARTAMENTO</th>
                            <th class="align-middle">DESCRIPCIÓN</th>
                            <th class="text-center align-middle">COORDENADAS</th>
                            <th class="text-center align-middle">ESTADO</th>
                            <th class="text-center align-middle">FECHA CREACIÓN</th>
                            <th class="text-center align-middle">ACCIONES</th> 
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ZoneModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold" id="ZoneModalTitle">Formulario de Zona</h5>
                <button type="button" class="close text-white opacity-80 hover-opacity-100" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-light-panel">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ZoneMapModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header custom-modal-header text-white">
                <h5 class="modal-title">
                    <i class="fas fa-map-marked-alt mr-2"></i>
                    Mapa de la Zona
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                </div>
        </div>
    </div>
</div>

<div class="p-2"></div>
@endsection

@section('css')
    <link class="styles" rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="{{ asset('custom-crud.css') }}">
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        $(document).ready(function() {

            $('#tblZones').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.zone.index') }}",
                columns: [
                    { data: "name", className: 'align-middle font-weight-bold text-dark-blue' },
                    { data: "district_name", name: "district.name", className: 'align-middle' },
                    { data: "province_name", name: "district.province.name", className: 'align-middle' },
                    { data: "department_name", name: "district.province.department.name", className: 'align-middle' },
                    { 
                        data: "description", 
                        className: 'align-middle text-muted',
                        defaultContent: '<i class="text-muted opacity-60">Sin descripción</i>' 
                    },
                    { data: "coords_count", orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: "status_badge", orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: "formatted_date", name: "created_at", className: 'text-center align-middle' },
                    { data: "actions", orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }, 
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
                },
            });

            // Registrar Nueva Zona
            $('#btn-nueva-zona').click(function() {
                $.ajax({
                    url: "{{ route('admin.zone.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#ZoneModal #ZoneModalTitle').html('<i class="fas fa-plus-circle mr-1.5"></i> Nueva Zona');
                        $('#ZoneModal .modal-body').html(response);
                        $('#ZoneModal').modal("show");

                        $('#ZoneModal form').on("submit", function(e) {
                            e.preventDefault();
                            var form = $(this);
                            $.ajax({
                                url: form.attr('action'),
                                type: form.attr('method'),
                                data: form.serialize(),
                                success: function(res) {
                                    $('#ZoneModal').modal("hide");
                                    refreshTable();
                                    Swal.fire('¡Registro Exitoso!', res.message, 'success');
                                },
                                error: function(xhr) {
                                    var res = xhr.responseJSON;
                                    var msg = 'Ocurrió un inconveniente al guardar la zona.';
                                    if (xhr.status === 422 && res.message) { msg = res.message; }
                                    Swal.fire({ title: 'Datos Inválidos', text: msg, icon: 'error' });
                                }
                            });
                        });
                    }
                });
            });
        });

        // Editar Registro de Zona
        $(document).on('click', '.btn-editar', function() {
            var id = $(this).attr("id");
            $.ajax({
                url: "{{ route('admin.zone.edit', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    $('#ZoneModal #ZoneModalTitle').html('<i class="fas fa-edit mr-1.5"></i> Editar Registro de Zona');
                    $('#ZoneModal .modal-body').html(response);
                    $('#ZoneModal').modal("show");

                    $('#ZoneModal form').on("submit", function(e) {
                        e.preventDefault();
                        var form = $(this);
                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: form.serialize(),
                            success: function(res) {
                                $('#ZoneModal').modal("hide");
                                refreshTable(); 
                                Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                            },
                            error: function(xhr) {
                                var res = xhr.responseJSON;
                                var msg = 'Ocurrió un inconveniente al actualizar la zona.';
                                if (xhr.status === 422 && res.message) { msg = res.message; }
                                Swal.fire({ title: 'Datos Inválidos', text: msg, icon: 'error' });
                            }
                        });
                    });
                }
            });
        });

        // Eliminar Registro de Zona
        $(document).on('submit', '.frmEliminar', function(e) {
            e.preventDefault();
            var form = $(this); 
            
            Swal.fire({
                title: "¿Está seguro de Eliminar?",
                text: "¡Esta acción removerá la zona y sus configuraciones de forma permanente!",
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

        // Evento Detalle del Mapa (Disparador Sincronizado con Bootstrap)
        $(document).on('click', '.btn-mapa', function() {
            var id = $(this).attr("id");

            // Limpieza de listeners viejos
            $('#ZoneMapModal').off('shown.bs.modal');

            $.ajax({
                url: "{{ route('admin.zones.mapDetails', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    // Vaciar y rellenar dinámicamente la subvista
                    $('#ZoneMapModal .modal-body').empty().html(response);

                    // CONTROL EXACTO: Dispara el mapa solo cuando la transición CSS del modal termine
                    $('#ZoneMapModal').one('shown.bs.modal', function () {
                        if (typeof window.inicializarMapaDetalle === 'function') {
                            window.inicializarMapaDetalle();
                        }
                    });

                    // Mostrar modal
                    $('#ZoneMapModal').modal("show");
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo cargar la información del mapa.',
                        icon: 'error'
                    });
                }
            });
        });

        function refreshTable() {
            $('#tblZones').DataTable().ajax.reload(null, false);
        }
    </script>
@endsection