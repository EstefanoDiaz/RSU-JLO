<div class="form-group">
    {!! Form::label('vehicle_id', 'Vehículo *') !!}
    {!! Form::select('vehicle_id', $vehicles, null, ['class' => 'form-control', 'placeholder' => '-- Seleccione Vehículo --', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('user_id', 'Responsable del Mantenimiento *') !!}
    {!! Form::select('user_id', $responsibles, null, ['class' => 'form-control', 'placeholder' => '-- Seleccione Responsable --', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('type', 'Tipo de Mantenimiento *') !!}
    {!! Form::select('type', [
        'PREVENTIVO' => 'PREVENTIVO', 
        'LIMPIEZA' => 'LIMPIEZA', 
        'REPARACIÓN' => 'REPARACIÓN'
    ], null, ['class' => 'form-control', 'placeholder' => '-- Seleccione Tipo --', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('day_of_week', 'Día de la Semana *') !!}
    {!! Form::select('day_of_week', [
        'LUNES' => 'LUNES', 'MARTES' => 'MARTES', 'MIÉRCOLES' => 'MIÉRCOLES',
        'JUEVES' => 'JUEVES', 'VIERNES' => 'VIERNES', 'SÁBADO' => 'SÁBADO', 'DOMINGO' => 'DOMINGO'
    ], null, ['class' => 'form-control', 'placeholder' => '-- Seleccione Día --', 'required']) !!}
</div>

<div class="row">
    <div class="col-6 form-group">
        {!! Form::label('start_time', 'Hora Inicio *') !!}
        {!! Form::time('start_time', null, ['class' => 'form-control', 'required']) !!}
    </div>
    <div class="col-6 form-group">
        {!! Form::label('end_time', 'Hora Fin *') !!}
        {!! Form::time('end_time', null, ['class' => 'form-control', 'required']) !!}
    </div>
</div>

<script>
    // Validación para evitar hora fin menor a hora inicio en el modal
    $('input[name="start_time"]').on('change', function() {
        var horaInicio = $(this).val();
        $('input[name="end_time"]').attr('min', horaInicio);
    });
</script>