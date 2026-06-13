@extends('adminlte::page')

@section('title', 'RSU JLO - Grupos de Personal')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-users-cog mr-2"></i> Lista de Grupos de Personal
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm ml-auto" id="btn-nuevo-grupo">
                <i class="fas fa-plus mr-1"></i> Nuevo Grupo
            </button>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblGroups" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Turno</th>
                            <th>Zona</th>
                            <th>Vehículo</th>
                            <th>Conductor</th>
                            <th>Ayudantes</th>
                            <th class="text-center">Días</th>
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

{{-- MODAL --}}
<div class="modal fade" id="GroupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header text-white py-3" style="background-color: #071D38;">
                <h5 class="modal-title font-weight-bold" id="GroupModalTitle">Grupo de Personal</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
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
    $(document).ready(function () {
        $('#tblGroups').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.personalgroup.index') }}",
            columns: [
                { data: 'name',           className: 'align-middle font-weight-bold' },
                { data: 'schedule_name',  className: 'align-middle' },
                { data: 'zone_name',      className: 'align-middle' },
                { data: 'vehicle_plate',  className: 'align-middle' },
                { data: 'conductor_name', className: 'align-middle' },
                { data: 'assistants',     className: 'align-middle', orderable: false },
                { data: 'days_badges',    className: 'align-middle text-center text-nowrap', orderable: false },
                { data: 'badge_status',   className: 'align-middle text-center', orderable: false },
                { data: 'actions',        className: 'align-middle text-center text-nowrap', orderable: false, searchable: false },
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });

        // ABRIR MODAL CREAR
        $('#btn-nuevo-grupo').click(function () {
            $.ajax({
                url: "{{ route('admin.personalgroup.create') }}",
                type: 'GET',
                success: function (html) {
                    $('#GroupModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nuevo Grupo de Personal');
                    $('#GroupModal .modal-body').html(html);
                    $('#GroupModal').modal('show');
                    bindForm('{{ route('admin.personalgroup.store') }}', 'POST');
                }
            });
        });

        // ABRIR MODAL EDITAR
        $(document).on('click', '.btn-editar', function () {
            let id = $(this).data('id');
            $.ajax({
                url: "{{ url('admin/personalgroup') }}/" + id + "/edit",
                type: 'GET',
                success: function (html) {
                    $('#GroupModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Grupo de Personal');
                    $('#GroupModal .modal-body').html(html);
                    $('#GroupModal').modal('show');
                    bindForm("{{ url('admin/personalgroup') }}/" + id, 'POST', true);
                }
            });
        });

        // ELIMINAR
        $(document).on('submit', '.frmEliminar', function (e) {
            e.preventDefault();
            let form = $(this);
            Swal.fire({
                title: '¿Eliminar grupo?',
                text: 'Esta acción no se puede revertir.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#a13825',
                confirmButtonText: 'Sí, eliminar'
            }).then(r => {
                if (r.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        success: function (res) {
                            refreshTable();
                            Swal.fire('Eliminado', res.message, 'success');
                        },
                        error: function (xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message ?? 'Error', 'error');
                        }
                    });
                }
            });
        });
    });

    function bindForm(url, method, isEdit = false) {
        $('#GroupModal form').off('submit').on('submit', function (e) {
            e.preventDefault();
            // Recoger checkboxes de días manualmente
            let days = [];
            $(this).find('input[name="work_days[]"]:checked').each(function () {
                days.push($(this).val());
            });
            let data = $(this).serializeArray();
            // Eliminar work_days del serializeArray y añadir los correctos
            data = data.filter(d => d.name !== 'work_days[]');
            days.forEach(d => data.push({ name: 'work_days[]', value: d }));
            if (isEdit) data.push({ name: '_method', value: 'PUT' });

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (res) {
                    $('#GroupModal').modal('hide');
                    refreshTable();
                    Swal.fire('¡Listo!', res.message, 'success');
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message ?? 'Error al guardar', 'error');
                }
            });
        });
    }

    function refreshTable() {
        $('#tblGroups').DataTable().ajax.reload(null, false);
    }
    </script>
@endsection