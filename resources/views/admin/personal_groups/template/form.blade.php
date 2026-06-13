@php $editing = isset($group); @endphp

{!! Form::open([
    'route'  => $editing ? ['admin.personal-group.update', $group->id] : 'admin.personal-group.store',
    'method' => $editing ? 'PUT' : 'POST',
    'id'     => 'formGroup',
]) !!}

<div class="row">
    <div class="col-md-8 form-group mb-3">
        {!! Form::label('name', 'Nombre del Grupo *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        {!! Form::text('name', $editing ? $group->name : null, ['class' => 'form-control', 'placeholder' => 'Ej. Grupo A', 'required' => true]) !!}
    </div>
    @if($editing)
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('status', 'Estado *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        {!! Form::select('status', ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'], $group->status, ['class' => 'form-control', 'required' => true]) !!}
    </div>
    @endif
</div>

<div class="row">
    <div class="col-md-6 form-group mb-3">
        {!! Form::label('zone_id', 'Zona *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="zone_id" class="form-control" required>
            <option value="">-- Seleccione zona --</option>
            @foreach($zones as $zone)
                <option value="{{ $zone->id }}" {{ ($editing && $group->zone_id == $zone->id) ? 'selected' : '' }}>{{ $zone->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 form-group mb-3">
        {!! Form::label('schedule_id', 'Turno *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="schedule_id" class="form-control" required>
            <option value="">-- Seleccione turno --</option>
            @foreach($schedules as $schedule)
                <option value="{{ $schedule->id }}" {{ ($editing && $group->schedule_id == $schedule->id) ? 'selected' : '' }}>
                    {{ $schedule->name }} ({{ $schedule->time_start }} - {{ $schedule->time_end }})
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group mb-3">
    {!! Form::label('vehicle_id', 'Vehículo *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
    <select name="vehicle_id" class="form-control" required>
        <option value="">-- Seleccione vehículo --</option>
        @foreach($vehicles as $vehicle)
            <option value="{{ $vehicle->id }}" {{ ($editing && $group->vehicle_id == $vehicle->id) ? 'selected' : '' }}>
                {{ $vehicle->name }} - {{ $vehicle->code }}
            </option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('conductor_id', 'Conductor *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="conductor_id" class="form-control" required>
            <option value="">-- Seleccione conductor --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ ($editing && $group->conductor_id == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('ayudante1_id', 'Ayudante 1 *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="ayudante1_id" class="form-control" required>
            <option value="">-- Seleccione ayudante --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ ($editing && $group->ayudante1_id == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('ayudante2_id', 'Ayudante 2', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="ayudante2_id" class="form-control">
            <option value="">-- Opcional --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ ($editing && $group->ayudante2_id == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="d-flex justify-content-end mt-3 pt-2 border-top">
    <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Cancelar
    </button>
    {!! Form::submit($editing ? 'Actualizar' : 'Guardar', ['class' => 'btn btn-primary font-weight-bold']) !!}
</div>

{!! Form::close() !!}
