<div class="row">
    <div class="col-md-12 form-group">
        {!! Form::label('name', 'Nombre del Mantenimiento *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::text('name', null, ['class' => 'form-control rounded-xl', 'placeholder' => 'Ej: Mantenimiento Preventivo Trimestral...', 'required', 'maxlength' => '150']) !!}
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::label('start_date', 'Fecha de Inicio *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::date('start_date', null, ['class' => 'form-control rounded-xl', 'required']) !!}
    </div>

    <div class="col-md-6 form-group">
        {!! Form::label('end_date', 'Fecha de Fin *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::date('end_date', null, ['class' => 'form-control rounded-xl', 'required']) !!}
    </div>
</div>

<style>
    .rounded-xl { border-radius: 10px !important; }
    .text-xs { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.06em; }
</style>