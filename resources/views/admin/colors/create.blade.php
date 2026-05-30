{!! Form::open(['route' => 'admin.color.store', 'method' => 'POST', 'id' => 'formColorRegister']) !!}
    
    @include('admin.colors.template.form')

    <div class="modal-footer p-0 pt-3 border-top-0 d-flex justify-content-end">
        <button type="button" class="btn btn-danger mr-2 btn-sm" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button>
    </div>

{!! Form::close() !!}