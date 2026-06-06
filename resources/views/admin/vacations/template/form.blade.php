@php
    // Calculamos dinámicamente los límites visuales conforme a tus reglas de tiempo
    $minFechaHtml = \Carbon\Carbon::now()->startOfMonth()->toDateString(); // Primer día del mes actual
    $maxFechaHtml = \Carbon\Carbon::now()->addYear()->endOfYear()->toDateString(); // Último día del año siguiente
@endphp

<div class="row">
    <div class="col-md-12 form-group mb-3">
        {!! Form::label('user_id', 'Seleccionar Personal Apto con Contrato Activo *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        <select name="user_id" class="form-control rounded-xl" required {{ isset($vacation) ? 'disabled' : '' }}>
            <option value="">-- Seleccione un usuario --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ (isset($vacation) && $vacation->user_id == $user->id) ? 'selected' : '' }}>
                    {{ $user->dni ?? 'S/D' }} - {{ $user->name }}
                </option>
            @endforeach
        </select>
        @if(isset($vacation))
            <input type="hidden" name="user_id" value="{{ $vacation->user_id }}">
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-group mb-3">
        {!! Form::label('start_date', 'Fecha de Inicio *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::date('start_date', null, [
            'class' => 'form-control rounded-xl', 
            'id' => 'dtStart', 
            'required',
            'min' => $minFechaHtml,
            'max' => $maxFechaHtml
        ]) !!}
    </div>
    <div class="col-md-6 form-group mb-3">
        {!! Form::label('end_date', 'Fecha de Término *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::date('end_date', null, [
            'class' => 'form-control rounded-xl', 
            'id' => 'dtEnd', 
            'required',
            'min' => $minFechaHtml,
            'max' => $maxFechaHtml
        ]) !!}
    </div>
</div>

<div class="form-group mb-3">
    {!! Form::label('notes', 'Notas Adicionales o Justificación', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
    {!! Form::textarea('notes', null, ['class' => 'form-control rounded-xl', 'placeholder' => 'Detalles de la solicitud...', 'rows' => '2']) !!}
</div>

<div class="form-group mt-3">
    <div id="boxDaysCounter" class="text-center font-weight-black shadow-inner rounded-xl p-3 border bg-light" style="font-size: 14px; color: #071D38; letter-spacing: 0.5px;">
        <i class="fas fa-calculator mr-2"></i> Total Días Solicitados: <span id="lblDaysCount" class="badge badge-dark px-2 py-1">0 días</span>
    </div>
</div>

<script>
    $(document).ready(function() {
        function calcularDias() {
            let startVal = $('#dtStart').val();
            let endVal = $('#dtEnd').val();
            if(startVal && endVal) {
                let s = new Date(startVal);
                let e = new Date(endVal);
                if(e >= s) {
                    let diff = Math.floor((e - s) / (1000 * 60 * 60 * 24)) + 1;
                    $('#lblDaysCount').text(diff + ' día(s)');
                    return;
                }
            }
            $('#lblDaysCount').text('0 días');
        }
        $('#dtStart, #dtEnd').on('change input', calcularDias);
        calcularDias();
    });
</script>