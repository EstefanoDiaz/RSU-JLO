@extends('adminlte::page')

@section('title', 'Lista de Modelos de Vehículos')

@section('content')
    <div class="p-2"></div>
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-wrench"></i> Lista de Modelos de Vehículos</h4>
            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo">
                <i class="fas fa-plus"></i> Nuevo Modelo
            </button>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="datatable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Marca</th>
                        <th>Descripción</th>
                        <th>Creación</th>
                        <th>Actualización</th>
                        <th width="100">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Modal Registro / Edición --}}
    <div class="modal fade" id="FormModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="FormModalTitle">
                        <i class="fas fa-wrench"></i> Nuevo Modelo
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
$(document).ready(function () {

    // ─── DATATABLE ────────────────────────────────────────────────────────────
    $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.brandmodel.index') }}",
        columns: [
            { data: "name" },
            { data: "code" },
            { data: "brand_id",  orderable: false },
            { data: "description" },
            { data: "created_at" },
            { data: "updated_at" },
            { data: "actions", orderable: false, searchable: false },
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
        },
    });

    // ─── ELIMINAR ─────────────────────────────────────────────────────────────
    $(document).on('submit', '.frmEliminar', function (e) {
        e.preventDefault();
        var form = $(this);
        Swal.fire({
            title: "¿Está seguro de Eliminar?",
            text: "Esta acción es irreversible!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, eliminar!",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    success: function (response) {
                        refreshTable();
                        Swal.fire('Eliminado', response.message, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('No se puede eliminar', xhr.responseJSON.message, 'error');
                    }
                });
            }
        });
    });

    // ─── NUEVO MODELO ─────────────────────────────────────────────────────────
    $('#btn-nuevo').click(function () {
        $.ajax({
            url: "{{ route('admin.brandmodel.create') }}",
            type: "GET",
            success: function (response) {
                $('#FormModal #FormModalTitle').html('<i class="fas fa-wrench"></i> Nuevo Modelo');
                $('#FormModal .modal-body').html(response);
                $('#FormModal').modal("show");
                bindFormSubmit();
            }
        });
    });

    // ─── EDITAR MODELO ────────────────────────────────────────────────────────
    $(document).on('click', '.btn-editar', function () {
        var id = $(this).attr("id");
        $.ajax({
            url: "{{ route('admin.brandmodel.edit', 'id') }}".replace('id', id),
            type: "GET",
            success: function (response) {
                $('#FormModal #FormModalTitle').html('<i class="fas fa-pen"></i> Editar Modelo');
                $('#FormModal .modal-body').html(response);
                $('#FormModal').modal("show");
                bindFormSubmit();
            }
        });
    });

});

function bindFormSubmit() {
    $('#FormModal form').on("submit", function (e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#FormModal').modal("hide");
                refreshTable();
                Swal.fire('Proceso Exitoso', response.message, 'success');
            },
            error: function (xhr) {
                Swal.fire('Ocurrió un Error', xhr.responseJSON.message, 'error');
            }
        });
    });
}

function refreshTable() {
    $('#datatable').DataTable().ajax.reload(null, false);
}
</script>

@if (session('success') != null)
    <script>
        Swal.fire({ title: "Operación exitosa", text: "{{ session('success') }}", icon: "success", draggable: true });
    </script>
@endif

@if (session('error') != null)
    <script>
        Swal.fire({ title: "Ocurrió un error", text: "{{ session('error') }}", icon: "error", draggable: true });
    </script>
@endif

@stop