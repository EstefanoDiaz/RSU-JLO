@extends('adminlte::page')

@section('title', 'Marcas')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">

    <h1 class="mb-0">
        <i class="fas fa-tags text-primary"></i>
        Lista de Marcas
    </h1>

    <button
        class="btn btn-primary shadow-sm"
        data-toggle="modal"
        data-target="#modalMarca"
        onclick="abrirModalCrear()">

        <i class="fas fa-plus"></i> Nueva Marca
    </button>

</div>
@stop


@section('content')

{{-- BUSCADOR UI --}}
<div class="card mb-3">
    <div class="card-body">

        <div class="input-group">
            <input type="text" id="buscarMarca" class="form-control"
                   placeholder="Buscar marca...">

            <div class="input-group-append">
                <span class="input-group-text bg-primary text-white">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>

    </div>
</div>


<div class="card shadow-sm">

    <div class="card-header bg-white">
        <h3 class="card-title">Marcas registradas</h3>
    </div>

    <div class="card-body p-0">

        <table id="tablaMarcas" class="table table-hover table-striped mb-0">

            <thead class="thead-dark">
                <tr>
                    <th width="90">Logo</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th width="140">Fecha creación</th>
                    <th width="140">Acciones</th>
                </tr>
            </thead>

            <tbody id="bodyMarcas">

                @forelse($marcas as $marca)

                    <tr class="fila-marca">

                        <td class="text-center">

                            @if($marca->imagen)
                                <img src="{{ asset('storage/' . $marca->imagen) }}"
                                     class="img-thumbnail"
                                     style="width:55px;height:55px;object-fit:contain;">
                            @else
                                <span class="badge badge-secondary">Sin logo</span>
                            @endif

                        </td>

                        <td class="font-weight-bold marca-nombre">
                            {{ $marca->nombre }}
                        </td>

                        <td class="marca-desc">
                            {{ $marca->descripcion }}
                        </td>

                        <td>
                            <span class="badge badge-info">
                                {{ $marca->created_at->format('d/m/Y') }}
                            </span>
                        </td>

                        <td>

                            <button
                                class="btn btn-warning btn-sm btn-editar"
                                data-id="{{ $marca->id }}"
                                data-nombre="{{ $marca->nombre }}"
                                data-descripcion="{{ $marca->descripcion }}"
                                data-imagen="{{ $marca->imagen }}">

                                <i class="fas fa-edit"></i>
                            </button>

                            <form
                                action="{{ route('marcas.destroy', $marca->id) }}"
                                method="POST"
                                class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Eliminar marca?')">

                                    <i class="fas fa-trash"></i>
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">

                            <i class="fas fa-box-open fa-3x mb-2"></i>
                            <br>
                            No hay marcas registradas

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


{{-- ================= MODAL ================= --}}
<div class="modal fade" id="modalMarca">

    <div class="modal-dialog modal-lg">

        <form id="formMarca" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="methodField" name="_method" value="POST">

            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title" id="tituloModal">Nueva Marca</h4>

                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-8">

                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="text" id="nombre" name="nombre"
                                       class="form-control"
                                       placeholder="Ej: Toyota"
                                       required>
                            </div>

                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea id="descripcion"
                                          name="descripcion"
                                          class="form-control"
                                          rows="4"
                                          placeholder="Descripción..."></textarea>
                            </div>

                        </div>

                        <div class="col-md-4 text-center">

                            <label>Logo</label>

                            <div class="border rounded p-2 mb-2">

                                <img id="previewImg"
                                     src="https://via.placeholder.com/180"
                                     class="img-fluid"
                                     style="max-height:180px;object-fit:contain;">

                            </div>

                            <input type="file"
                                   name="imagen"
                                   class="form-control"
                                   onchange="previewImage(event)">

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        Guardar
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

@stop


@section('js')

<script>

/* ===================== BUSCADOR ===================== */
document.getElementById('buscarMarca').addEventListener('keyup', function () {

    let value = this.value.toLowerCase();

    document.querySelectorAll('.fila-marca').forEach(row => {

        let nombre = row.querySelector('.marca-nombre').innerText.toLowerCase();
        let desc = row.querySelector('.marca-desc').innerText.toLowerCase();

        row.style.display = (nombre.includes(value) || desc.includes(value))
            ? ''
            : 'none';
    });

});


/* ===================== PREVIEW IMAGEN ===================== */
function previewImage(event) {

    const reader = new FileReader();

    reader.onload = function () {
        document.getElementById('previewImg').src = reader.result;
    };

    reader.readAsDataURL(event.target.files[0]);
}


/* ===================== CREAR ===================== */
function abrirModalCrear() {

    document.getElementById('tituloModal').innerText = "Nueva Marca";
    document.getElementById('formMarca').action = "{{ route('marcas.store') }}";
    document.getElementById('methodField').value = "POST";

    document.getElementById('nombre').value = "";
    document.getElementById('descripcion').value = "";

    document.getElementById('previewImg').src =
        "https://via.placeholder.com/180";

    document.getElementById('btnGuardar').innerText = "Guardar";

    $('#modalMarca').modal('show');
}


/* ===================== EDITAR (CORREGIDO) ===================== */
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.btn-editar');

    if (!btn) return;

    const marca = {
        id: btn.dataset.id,
        nombre: btn.dataset.nombre,
        descripcion: btn.dataset.descripcion,
        imagen: btn.dataset.imagen
    };

    abrirModalEditar(marca);
});


function abrirModalEditar(marca) {

    document.getElementById('tituloModal').innerText = "Editar Marca";

    document.getElementById('formMarca').action = `/marcas/${marca.id}`;

    document.getElementById('methodField').value = "PUT";

    document.getElementById('nombre').value = marca.nombre ?? '';
    document.getElementById('descripcion').value = marca.descripcion ?? '';

    document.getElementById('btnGuardar').innerText = "Actualizar";

    if (marca.imagen) {
        document.getElementById('previewImg').src =
            `/storage/${marca.imagen}`;
    } else {
        document.getElementById('previewImg').src =
            "https://via.placeholder.com/180";
    }

    $('#modalMarca').modal('show');
}

</script>

@stop
