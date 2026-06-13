@php $isEdit = isset($group); @endphp

<form action="{{ $isEdit ? route('admin.personalgroup.update', $group->id) : route('admin.personalgroup.store') }}"
      method="POST" id="frmGroup">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        {{-- Nombre --}}
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold text-dark-blue">Nombre del Grupo <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control"
                   placeholder="Ej: Grupo A"
                   value="{{ $isEdit ? $group->name : '' }}" required>
        </div>

        {{-- Estado --}}
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold text-dark-blue">Estado</label>
            <select name="status" class="form-control">
                <option value="Activo"   {{ ($isEdit && $group->status === 'Activo')   ? 'selected' : '' }}>Activo</option>
                <option value="Inactivo" {{ ($isEdit && $group->status === 'Inactivo') ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>

        {{-- Turno --}}
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold text-dark-blue">Turno <span class="text-danger">*</span></label>
            <select name="schedule_id" class="form-control" required>
                <option value="">-- Seleccione --</option>
                @foreach($schedules as $s)
                    <option value="{{ $s->id }}"
                        {{ ($isEdit && $group->schedule_id == $s->id) ? 'selected' : '' }}>
                        {{ $s->name }} ({{ \Carbon\Carbon::parse($s->time_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->time_end)->format('H:i') }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Zona --}}
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold text-dark-blue">Zona <span class="text-danger">*</span></label>
            <select name="zone_id" class="form-control" required>
                <option value="">-- Seleccione --</option>
                @foreach($zones as $z)
                    <option value="{{ $z->id }}"
                        {{ ($isEdit && $group->zone_id == $z->id) ? 'selected' : '' }}>
                        {{ $z->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Vehículo --}}
        <div class="col-md-12 mb-3">
            <label class="font-weight-bold text-dark-blue">Vehículo <span class="text-danger">*</span></label>
            <select name="vehicle_id" class="form-control" required>
                <option value="">-- Seleccione --</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}"
                        {{ ($isEdit && $group->vehicle_id == $v->id) ? 'selected' : '' }}>
                        {{ $v->plate ?? 'Vehículo #' . $v->id }}
                        @if($v->vehicleType) — {{ $v->vehicleType->name }} @endif
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Conductor --}}
        <div class="col-md-12 mb-3">
            <label class="font-weight-bold text-dark-blue">Conductor <span class="text-danger">*</span></label>
            <select name="conductor_id" class="form-control" required>
                <option value="">-- Seleccione conductor --</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}"
                        {{ ($isEdit && $group->conductor_id == $u->id) ? 'selected' : '' }}>
                        {{ $u->name }} — DNI: {{ $u->dni ?? '-' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Ayudante 1 --}}
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold text-dark-blue">Ayudante 1</label>
            <select name="assistant1_id" class="form-control">
                <option value="">-- Opcional --</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}"
                        {{ ($isEdit && $group->assistant1_id == $u->id) ? 'selected' : '' }}>
                        {{ $u->name }} — DNI: {{ $u->dni ?? '-' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Ayudante 2 --}}
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold text-dark-blue">Ayudante 2</label>
            <select name="assistant2_id" class="form-control">
                <option value="">-- Opcional --</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}"
                        {{ ($isEdit && $group->assistant2_id == $u->id) ? 'selected' : '' }}>
                        {{ $u->name }} — DNI: {{ $u->dni ?? '-' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Días de trabajo --}}
        <div class="col-md-12 mb-3">
            <label class="font-weight-bold text-dark-blue d-block">Días de Trabajo <span class="text-danger">*</span></label>
            <small class="text-muted">Seleccione los días disponibles para este grupo</small>
            <div class="mt-2 d-flex flex-wrap gap-2">
                @php
                    $dayOptions = ['lun'=>'Lunes','mar'=>'Martes','mie'=>'Miércoles','jue'=>'Jueves','vie'=>'Viernes','sab'=>'Sábado','dom'=>'Domingo'];
                    $selectedDays = $isEdit ? ($group->work_days ?? []) : [];
                @endphp
                @foreach($dayOptions as $key => $label)
                <div class="form-check form-check-inline mr-3">
                    <input class="form-check-input" type="checkbox"
                           name="work_days[]" value="{{ $key }}" id="day_{{ $key }}"
                           {{ in_array($key, $selectedDays) ? 'checked' : '' }}>
                    <label class="form-check-label font-weight-bold" for="day_{{ $key }}">{{ $label }}</label>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <hr>
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="submit" class="btn font-weight-bold text-white" style="background-color: #071D38;">
            <i class="fas fa-save mr-1"></i> Guardar
        </button>
    </div>
</form>