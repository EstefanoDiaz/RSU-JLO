@extends('adminlte::page')

@section('title', 'Horarios de Mantenimiento')

@section('content')
<div class="container-fluid pt-4 pb-4">
    <a href="{{ route('admin.maintenance.index') }}" class="btn btn-sm btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Volver a Mantenimientos
    </a>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between py-3" style="background-color: #071D38 !important;">
            <h4 class="mb-0 font-weight-bold text-uppercase text-white">
                <i class="fas fa-calendar-alt mr-2 text-white-75"></i> MANT. {{ $maintenance->name }}
            </h4>
            <button type="button" class="btn btn-success font-weight-bold btn-sm shadow-sm ml-auto" id="btn-nuevo-horario">
                <i class="fas fa-plus mr-1"></i> Agregar Horario
            </button>
        </div>
                
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblSchedules" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>DÍA</th>
                            <th>VEHÍCULO</th>
                            <th>RESPONSABLE</th>
                            <th>TIPO</th>
                            <th>INICIO</th>
                            <th>FIN</th>
                            <th class="text-center">ACCIONES</th> 
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal General para Formulario  -->
<div class="modal fade" id="ScheduleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold" id="ScheduleModalTitle">Formulario de Horario</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4"></div>
        </div>
    </div>
</div>

<!-- Modal para Ver los Días Calculados con Tabla -->
<div class="modal fade" id="DaysModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-check text-white"></i> Control de Días de Mantenimiento</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center mb-0" id="tblDetalleDias">
                        <thead class="bg-light">
                            <tr>
                                <th>FECHA</th>
                                <th>OBSERVACIÓN</th>
                                <th>IMAGEN</th>
                                <th>EDIT</th>
                                <th>EST</th>
                            </tr>
                        </thead>
                        <tbody id="bodyDetalleDias">
                            <!-- Se llena dinámicamente con JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sub-Modal para Formulario de Edición de un Día Individual -->
<div class="modal fade" id="EditDayModal" tabindex="-1" role="dialog" aria-hidden="true" style="background: rgba(0,0,0,0.5); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit"></i> Gestionar Día Específico</h5>
                <button type="button" class="close text-white" onclick="$('#EditDayModal').modal('hide')">&times;</button>
            </div>
            <form id="frmEditarDia" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="edit_day_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">¿Se realizó el mantenimiento? (EST)</label>
                        <select class="form-control" id="edit_day_status" name="status">
                            <option value="1">SÍ, REALIZADO ✔</option>
                            <option value="0">NO REALIZADO ❌</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Observación</label>
                        <textarea class="form-control" id="edit_day_observation" name="observation" rows="3" placeholder="Ej. Todo conforme / No se realizó..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Evidencia (Imagen)</label>
                        <input type="file" class="form-control-file" id="edit_day_image" name="image" accept="image/*">
                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG. Máx 2MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light border" onclick="$('#EditDayModal').modal('hide')">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    
    <script>
        $(document).ready(function() {
            var activeScheduleId = null;

            var table = $('#tblSchedules').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.maintenance.schedules.index', $maintenance->id) }}",
                columns: [
                    { data: "day_of_week", className: 'font-weight-bold text-capitalize' },
                    { data: "vehicle.plate" },
                    { data: "user.name" },
                    { data: "type" },
                    { data: "start_time" },
                    { data: "end_time" },
                    { data: "actions", orderable: false, searchable: false, className: 'text-center text-nowrap' }, 
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json' },
            });

            // Acción: Abrir Modal Crear Horario
            $('#btn-nuevo-horario').click(function() {
                $.ajax({
                    url: "{{ route('admin.maintenance.schedules.create', $maintenance->id) }}",
                    type: "GET",
                    success: function(response) {
                        $('#ScheduleModal #ScheduleModalTitle').text('Asignar Nuevo Horario');
                        $('#ScheduleModal .modal-body').html(response);
                        $('#ScheduleModal').modal("show");
                        bindForm();
                    }
                });
            });

            // Acción: Abrir Modal Editar Horario
            $(document).on('click', '.btn-editar-horario', function() {
                var id = $(this).attr("id");
                $.ajax({
                    url: "{{ url('maintenance-schedules') }}/" + id + "/edit",
                    type: "GET",
                    success: function(response) {
                        $('#ScheduleModal #ScheduleModalTitle').text('Modificar Horario');
                        $('#ScheduleModal .modal-body').html(response);
                        $('#ScheduleModal').modal("show");
                        bindForm();
                    }
                });
            });

            // Acción: Ver Días Autogenerados en Tabla Interactiva 
            $(document).on('click', '.btn-ver-dias', function() {
                var id = $(this).attr("id");
                activeScheduleId = id; 
                cargarTablaDias(id);
            });

            function cargarTablaDias(scheduleId) {
                $.ajax({
                    url: "{{ url('maintenance-schedules') }}/" + scheduleId + "/days",
                    type: "GET",
                    success: function(res) {
                        var tbody = $('#bodyDetalleDias');
                        tbody.empty();
                        
                        if(res.length === 0) {
                            tbody.append('<tr><td colspan="5" class="text-muted text-center p-3">No se agendaron días automáticos en el detalle.</td></tr>');
                        } else {
                            res.forEach(function(item) {
                                // Formatear Fecha DD/MM/YYYY nativamente
                                var fechaFormateada = new Date(item.date + 'T00:00:00').toLocaleDateString('es-ES', {day: '2-digit', month: '2-digit', year: 'numeric'});
                                
                                // Sanitizar nulidad en la observación
                                var observacion = item.observation ? item.observation : '';
                                
                                // Renderizado de Imagen o placeholder
                                var imagenHtml = item.image 
                                    ? '<a href="{{ url("/") }}/' + item.image + '" target="_blank"><img src="{{ url("/") }}/' + item.image + '" class="img-thumbnail" style="max-height: 40px; cursor: pointer;"></a>' 
                                    : '<span class="text-muted small">-</span>';
                                
                                // Renderizado estricto del Estado 
                                var estadoHtml = item.status == 1 
                                    ? '<i class="fas fa-check text-success fa-2x" style="font-weight: 900;"></i>' 
                                    : '<i class="fas fa-times text-danger fa-2x" style="font-weight: 900;"></i>';

                                // Construcción dinámica de la fila
                                var fila = '<tr>' +
                                    '<td class="align-middle font-weight-bold" style="font-size: 1.05rem;">' + fechaFormateada + '</td>' +
                                    '<td class="align-middle text-left px-3">' + observacion + '</td>' +
                                    '<td class="align-middle">' + imagenHtml + '</td>' +
                                    '<td class="align-middle">' +
                                        '<button type="button" class="btn btn-sm btn-secondary btn-editar-dia-celda" ' +
                                            'data-id="' + item.id + '" ' +
                                            'data-status="' + (item.status ?? 0) + '" ' +
                                            'data-observation="' + observacion + '">' +
                                            '<i class="fas fa-edit text-white"></i>' +
                                        '</button>' +
                                    '</td>' +
                                    '<td class="align-middle">' + estadoHtml + '</td>' +
                                '</tr>';
                                
                                tbody.append(fila);
                            });
                        }
                        $('#DaysModal').modal('show');
                    }
                });
            }

            // Al presionar el botón EDIT de una fila específica de la tabla de detalles
            $(document).on('click', '.btn-editar-dia-celda', function() {
                var id = $(this).data('id');
                var status = $(this).data('status');
                var observation = $(this).data('observation');

                $('#edit_day_id').val(id);
                $('#edit_day_status').val(status);
                $('#edit_day_observation').val(observation);
                $('#edit_day_image').val(''); 

                // Abrir el modal secundario sobrepuesto
                $('#EditDayModal').modal('show');
            });

            $('#frmEditarDia').on('submit', function(e) {
                e.preventDefault();
                var id = $('#edit_day_id').val();
                var formData = new FormData(this);

                $.ajax({
                    url: "{{ url('maintenance-schedules/detail') }}/" + id,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#EditDayModal').modal('hide');
                        Swal.fire('¡Éxito!', res.message, 'success');
                        
                        if(activeScheduleId) {
                            cargarTablaDias(activeScheduleId);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON.message || 'No se pudo procesar la solicitud.', 'error');
                    }
                });
            });

            function bindForm() {
                $('#ScheduleModal form').on("submit", function(e) {
                    e.preventDefault();
                    var form = $(this);
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        success: function(res) {
                            $('#ScheduleModal').modal("hide");
                            table.ajax.reload(null, false);
                            Swal.fire('¡Éxito!', res.message, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Atención', xhr.responseJSON.message || 'Error en el proceso.', 'error');
                        }
                    });
                });
            }

            $(document).on('submit', '.frmEliminarHorario', function(e) {
                e.preventDefault();
                var form = $(this);
                Swal.fire({
                    title: "¿Eliminar este Horario?",
                    text: "¡Atención! Esta acción removerá el horario y TODOS los días agendados automáticamente en el detalle.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#071D38",
                    cancelButtonColor: "#a13825",
                    confirmButtonText: "Sí, eliminar todo",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'), 
                            data: form.serialize(), 
                            success: function(res) {
                                table.ajax.reload(null, false); 
                                Swal.fire('Removido', res.message, 'success');
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection