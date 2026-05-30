@extends('adminlte::page')

@section('title', 'Brands')

@section('plugins.Datatables', true)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="m-0">Marcas</h1>
    <button type="button" class="btn btn-primary" onclick="abrirModalCrear()">
        <i class="fas fa-plus"></i> Nueva marca
    </button>
</div>

<div id="alerts"></div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h3 class="card-title">Registro de marcas</h3>
    </div>

    <div class="card-body p-0">
        <table id="brandsTable" class="table table-hover table-striped mb-0">
            <thead class="thead-dark">
                <tr>
                    <th width="90">Logo</th>
                    <th>Nombre</th>
                    <th>Descripcion</th>
                    <th width="140">Creado</th>
                    <th width="140">Acciones</th>
                </tr>
            </thead>

            <tbody id="bodyBrands">
                @forelse($brands as $brand)
                    <tr class="fila-brand" data-id="{{ $brand->id }}">
                        <td class="text-center">
                            @if($brand->logo)
                                <img src="{{ asset('storage/' . $brand->logo) }}" class="img-thumbnail" style="width:55px;height:55px;object-fit:contain;">
                            @else
                                <span class="badge badge-secondary">No logo</span>
                            @endif
                        </td>
                        <td class="font-weight-bold brand-name">{{ $brand->name }}</td>
                        <td class="brand-description">{{ $brand->description }}</td>
                        <td><span class="badge badge-info">{{ $brand->created_at->format('d/m/Y') }}</span></td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm btn-edit"
                                    data-id="{{ $brand->id }}"
                                    data-name="{{ $brand->name }}"
                                    data-description="{{ $brand->description }}"
                                    data-logo="{{ $brand->logo }}">
                                <i class="fas fa-edit"></i>
                            </button>

                            <form action="{{ route('brand.destroy', $brand->id) }}" method="POST" class="d-inline form-delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            No brands registered
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalBrand" tabindex="-1" role="dialog" aria-labelledby="modalBrandLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formBrand" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="methodField" name="_method" value="POST">

            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalBrandLabel">New Brand</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Example: Toyota" required>
                            </div>
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Description..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-4 text-center">
                            <label>Logo</label>
                            <div class="border rounded p-2 mb-2">
                                <img id="previewImg" src="https://via.placeholder.com/180" class="img-fluid" style="max-height:180px;object-fit:contain;">
                            </div>
                            <input type="file" name="logo" class="form-control" onchange="previewImage(event)">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
function showAlert(type, message) {
    const wrapper = document.getElementById('alerts');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `${message}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>`;
    wrapper.prepend(alert);
    setTimeout(() => $(alert).alert('close'), 4000);
}

function buildRowHTML(brand) {
    const imgCell = brand.logo
        ? `<img src="/storage/${brand.logo}" class="img-thumbnail" style="width:55px;height:55px;object-fit:contain;">`
        : `<span class="badge badge-secondary">No logo</span>`;

    return `
        <tr class="fila-brand" data-id="${brand.id}">
            <td class="text-center">${imgCell}</td>
            <td class="font-weight-bold brand-name">${brand.name}</td>
            <td class="brand-description">${brand.description ?? ''}</td>
            <td><span class="badge badge-info">${brand.created_at}</span></td>
            <td>
                <button type="button" class="btn btn-warning btn-sm btn-edit"
                    data-id="${brand.id}"
                    data-name="${brand.name}"
                    data-description="${brand.description ?? ''}"
                    data-logo="${brand.logo ?? ''}">
                    <i class="fas fa-edit"></i>
                </button>
                <form action="/brand/${brand.id}" method="POST" class="d-inline form-delete">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
    `;
}

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function () {
        document.getElementById('previewImg').src = reader.result;
    };
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}

function abrirModalCrear() {
    document.getElementById('modalBrandLabel').innerText = 'New Brand';
    document.getElementById('formBrand').action = '{{ route('brand.store') }}';
    document.getElementById('methodField').value = 'POST';
    document.getElementById('btnSave').innerText = 'Save';
    document.getElementById('name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('previewImg').src = 'https://via.placeholder.com/180';
    $('#modalBrand').modal('show');
}

function abrirModalEditar(brand) {
    document.getElementById('modalBrandLabel').innerText = 'Editar Marca';
    document.getElementById('formBrand').action = `/brand/${brand.id}`;
    document.getElementById('methodField').value = 'PUT';
    document.getElementById('btnSave').innerText = 'Actualizar';
    document.getElementById('name').value = brand.name ?? '';
    document.getElementById('description').value = brand.description ?? '';
    document.getElementById('previewImg').src = brand.logo ? `/storage/${brand.logo}` : 'https://via.placeholder.com/180';
    $('#modalBrand').modal('show');
}

let brandsTable;

function initDataTable() {
    brandsTable = $('#brandsTable').DataTable({
        paging: true,
        searching: true,
        ordering: false,
        lengthChange: false,
        pageLength: 10,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json',
                search: 'Buscar:',
                zeroRecords: 'No se encontraron marcas',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ marcas',
                infoEmpty: 'Mostrando 0 a 0 de 0 marcas',
                infoFiltered: '(filtrado de _MAX_ marcas totales)',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }  
        },
        columnDefs: [
            { orderable: false, targets: [0, 4] }
        ]
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initDataTable();
});

const formBrand = document.getElementById('formBrand');
formBrand.addEventListener('submit', async function (e) {
    e.preventDefault();

    const url = formBrand.action;
    const formData = new FormData(formBrand);
    formData.set('_method', document.getElementById('methodField').value || 'POST');

    try {
        const res = await fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            const data = await res.json().catch(() => null);
            if (res.status === 422 && data && data.errors) {
                const messages = Object.values(data.errors).flat().join('<br>');
                showAlert('danger', messages);
                return;
            }
            showAlert('danger', data?.message || 'Server error');
            return;
        }

        const data = await res.json();
        $('#modalBrand').modal('hide');

        const method = document.getElementById('methodField').value;
        if (method === 'POST') {
            brandsTable.row.add($(buildRowHTML(data.brand))).draw(false);
            showAlert('success', data.message || 'Brand created successfully');
            formBrand.reset();
            document.getElementById('previewImg').src = 'https://via.placeholder.com/180';
        } else {
            const existingRow = document.querySelector(`.fila-brand[data-id="${data.brand.id}"]`);
            if (existingRow) {
                brandsTable.row(existingRow).remove();
            }
            brandsTable.row.add($(buildRowHTML(data.brand))).draw(false);
            showAlert('success', data.message || 'Brand updated successfully');
        }
    } catch (err) {
        console.error(err);
        showAlert('danger', 'AJAX error');
    }
});

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-edit');
    if (!btn) return;

    const brand = {
        id: btn.dataset.id,
        name: btn.dataset.name,
        description: btn.dataset.description,
        logo: btn.dataset.logo
    };
    abrirModalEditar(brand);
});

document.addEventListener('submit', function (e) {
    const deleteForm = e.target.closest('.form-delete');
    if (!deleteForm) return;

    e.preventDefault();

    if (!confirm('Delete this brand?')) {
        return;
    }

    const url = deleteForm.action;
    const formData = new FormData(deleteForm);

    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    }).then(async res => {
        if (!res.ok) {
            const data = await res.json().catch(() => null);
            showAlert('danger', data?.message || 'Delete failed');
            return;
        }

        const data = await res.json();
        const row = deleteForm.closest('tr.fila-brand');
        if (row) {
            brandsTable.row(row).remove().draw(false);
        }
        showAlert('success', data.message || 'Brand deleted successfully');
    }).catch(err => {
        console.error(err);
        showAlert('danger', 'AJAX error');
    });
});
</script>
@endsection