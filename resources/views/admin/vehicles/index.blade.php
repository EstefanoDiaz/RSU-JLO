@extends('adminlte::page')

@section('title', 'RSU JLO - Vehículos')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-truck mr-2 text-white-75"></i> Lista de Vehículos
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm ml-auto" id="btn-nuevo">
                <i class="fas fa-plus mr-1"></i> Nuevo Vehículo
            </button>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblVehicles" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="text-center align-middle" width="8%">Imagen</th>
                            <th class="align-middle">Nombre</th>
                            <th class="align-middle">Código</th>
                            <th class="align-middle">Placa</th>
                            <th class="align-middle">Año</th>
                            <th class="align-middle">Capacidad</th>
                            <th class="align-middle">Marca</th>
                            <th class="align-middle">Modelo</th>
                            <th class="align-middle">Tipo</th>
                            <th class="text-center align-middle">Color</th>
                            <th class="text-center align-middle" width="12%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="VehicleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold" id="VehicleModalTitle">Formulario de Vehículo</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
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
    <style>
    #VehicleModal select.form-control {
        height: auto !important;
        min-height: 38px !important;
        padding: 6px 12px !important;
        line-height: 1.5 !important;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    $('#tblVehicles').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.vehicle.index') }}",
        columns: [
            { data: "image",    orderable: false, searchable: false, className: 'text-center align-middle' },
            { data: "name",     className: 'align-middle' },
            { data: "code",     className: 'align-middle' },
            { data: "plate",    className: 'align-middle' },
            { data: "year",     className: 'align-middle' },
            { data: "occupant_capacity", className: 'align-middle' },
            { data: "brand_id", className: 'align-middle', orderable: false },
            { data: "model_id", className: 'align-middle', orderable: false },
            { data: "type_id",  className: 'align-middle', orderable: false },
            { data: "color_id", className: 'text-center align-middle', orderable: false },
            { data: "actions",  orderable: false, searchable: false, className: 'text-center align-middle' },
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
        },
    });

    // ─── NUEVO ────────────────────────────────────────────────────────────────
    $('#btn-nuevo').click(function () {
        $.ajax({
            url: "{{ route('admin.vehicle.create') }}",
            type: "GET",
            success: function (response) {
                $('#VehicleModal #VehicleModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nuevo Vehículo');
                $('#VehicleModal .modal-body').html(response);
                $('#VehicleModal').modal("show");
                bindBrandChange();
                bindFormSubmit();
            }
        });
    });

    // ─── EDITAR ───────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-editar', function () {
        var id = $(this).attr("id");
        $.ajax({
            url: "{{ route('admin.vehicle.edit', 'id') }}".replace('id', id),
            type: "GET",
            success: function (response) {
                $('#VehicleModal #VehicleModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Vehículo');
                $('#VehicleModal .modal-body').html(response);
                $('#VehicleModal').modal("show");
                bindBrandChange();
                bindFormSubmit();
            }
        });
    });

    // ─── ELIMINAR ─────────────────────────────────────────────────────────────
    $(document).on('submit', '.frmEliminar', function (e) {
        e.preventDefault();
        var form = $(this);
        Swal.fire({
            title: "¿Está seguro de Eliminar?",
            text: "¡Esta acción removerá el vehículo de forma permanente!",
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
                    success: function (res) {
                        refreshTable();
                        Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON.message, 'error');
                    }
                });
            }
        });
    });

});

// ─── FILTRAR MODELOS POR MARCA ────────────────────────────────────────────────
function bindBrandChange() {
    $('#brand_id').on('change', function () {
        var brandId = $(this).val();
        var currentModelId = $('#model_id').data('selected') || '';

        if (!brandId) {
            $('#model_id').html('<option value="">Seleccione un modelo</option>');
            return;
        }

        $.ajax({
            url: '/vehicle/models-by-brand',
            type: "GET",
            data: { brand_id: brandId },
            success: function (models) {
                var options = '<option value="">Seleccione un modelo</option>';
                models.forEach(function (m) {
                    var selected = m.id == currentModelId ? 'selected' : '';
                    options += `<option value="${m.id}" ${selected}>${m.name}</option>`;
                });
                $('#model_id').html(options);
            },
            error: function () {
                Swal.fire('Error', 'No se pudieron cargar los modelos.', 'error');
            }
        });
    });

    // Si estamos editando, guardar el modelo actual y disparar el change
    var selectedModel = $('#model_id').find('option:selected').val();
    if (selectedModel) {
        $('#model_id').data('selected', selectedModel);
        $('#brand_id').trigger('change');
    }
}

function bindFormSubmit() {
    $('#VehicleModal form').on("submit", function (e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function (res) {
                $('#VehicleModal').modal("hide");
                refreshTable();
                Swal.fire('¡Registro Exitoso!', res.message, 'success');
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar.';
                Swal.fire('Error de Validación', msg, 'error');
            }
        });
    });
}

function refreshTable() {
    $('#tblVehicles').DataTable().ajax.reload(null, false);
}
</script>
@endsection