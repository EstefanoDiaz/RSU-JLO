{!! Form::model($schedule, ['route' => ['admin.schedules.update', $schedule->id], 'method' => 'PUT']) !!}
    @include('admin.maintenance_schedules.template.form')
    <div class="text-right mt-3">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-sync mr-1"></i> Actualizar Horario</button>
    </div>
{!! Form::close() !!}