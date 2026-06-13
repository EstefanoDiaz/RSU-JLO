{{-- Formulario: Editar Programación Individual --}}

{!! Form::open([
    'route'  => ['admin.assignment.update', $assignment->id],
    'method' => 'PUT',
    'id'     => 'formEditAsignacion',
]) !!}

<div class="row mb-3">
    <div class="col-md-6">
        <small class="text-muted text-uppercase font-weight-bold">Fecha</small>
        <p class="font-weight-bold mb-0">{{ \Carbon\Carbon::parse($assignment->date)->format('d/m/Y') }}</p>
    </div>
    <div class="col-md-6">
        <small class="text-muted text-uppercase font-weight-bold">Grupo</small>
        <p class="font-weight-bold mb-0">{{ $assignment->group->name ?? '-' }}</p>
    </div>
</div>

<div class="row mb-3 p-2 mx-0 border rounded bg-light text-center">
    <div class="col-4">
        <small class="text-muted d-block" style="font-size:10px; text-transform:uppercase;">Zona</small>
        <strong>{{ $assignment->zone->name ?? '-' }}</strong>
    </div>
    <div class="col-4">
        <small class="text-muted d-block" style="font-size:10px; text-transform:uppercase;">Turno</small>
        <strong>{{ $assignment->schedule->name ?? '-' }}</strong>
    </div>
    <div class="col-4">
        <small class="text-muted d-block" style="font-size:10px; text-transform:uppercase;">Vehículo</small>
        <strong>{{ ($assignment->vehicle->name ?? '-') . ' - ' . ($assignment->vehicle->code ?? '') }}</strong>
    </div>
</div>

<div class="row">
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('conductor_id', 'Conductor *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="conductor_id" class="form-control" required>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ $assignment->conductor_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('ayudante1_id', 'Ayudante 1 *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="ayudante1_id" class="form-control" required>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ $assignment->ayudante1_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('ayudante2_id', 'Ayudante 2', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="ayudante2_id" class="form-control">
            <option value="">-- Opcional --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ $assignment->ayudante2_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group mb-3">
    {!! Form::label('status', 'Estado *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
    {!! Form::select('status', ['Programado' => 'Programado', 'Finalizado' => 'Finalizado'], $assignment->status, ['class' => 'form-control', 'required' => true]) !!}
</div>

<div class="form-group mb-3">
    {!! Form::label('observations', 'Observaciones', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
    {!! Form::textarea('observations', $assignment->observations, ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Observaciones adicionales...']) !!}
</div>

<div class="d-flex justify-content-end mt-3 pt-2 border-top">
    <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Cancelar
    </button>
    {!! Form::submit('Actualizar', ['class' => 'btn btn-primary font-weight-bold']) !!}
</div>

{!! Form::close() !!}
