{!! Form::open(['route' => ['admin.maintenance.schedules.store', $maintenance->id], 'method' => 'POST']) !!}
    @include('admin.maintenance_schedules.template.form')
    <div class="text-right mt-3">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-save mr-1"></i> Guardar Horario</button>
    </div>
{!! Form::close() !!}