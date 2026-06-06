<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- Info del empleado --}}
<div class="form-group">
    <label for="user_id">Empleado <span class="text-danger">*</span></label>
    <select name="user_id" id="user_id" class="form-control select2-employee" required style="width:100%">
        <option value="">Seleccione un empleado</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}"
                {{ old('user_id', isset($attendance) ? $attendance->user_id : '') == $user->id ? 'selected' : '' }}>
                {{ $user->name }} - DNI: {{ $user->dni }}
            </option>
        @endforeach
    </select>
    <small class="text-muted">Busque por nombre, apellido o DNI del empleado</small>
</div>

{{-- Card info empleado --}}
<div id="employee-info" class="alert alert-light border mb-3 d-none">
    <div class="row">
        <div class="col-md-6">
            <p class="mb-1"><i class="fas fa-user text-primary mr-1"></i> <strong>Nombre completo:</strong> <span id="info-name"></span></p>
            <p class="mb-1"><i class="fas fa-envelope text-primary mr-1"></i> <strong>Email:</strong> <span id="info-email"></span></p>
        </div>
        <div class="col-md-6">
            <p class="mb-1"><i class="fas fa-id-card text-primary mr-1"></i> <strong>DNI:</strong> <span id="info-dni"></span></p>
            <p class="mb-1"><i class="fas fa-phone text-primary mr-1"></i> <strong>Teléfono:</strong> <span id="info-phone"></span></p>
        </div>
    </div>
    <hr class="my-2">
    <p class="mb-1 font-weight-bold"><i class="fas fa-history mr-1"></i> Registros del día:</p>
    <div id="registros-dia" class="text-muted"><small>No hay registros para este día.</small></div>
    <div id="primer-registro-alert" class="alert alert-warning py-1 mb-0 mt-2 d-none">
        <small><i class="fas fa-exclamation-triangle mr-1"></i> Primer registro del turno - debe ser <strong>ENTRADA</strong></small>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="date">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="date" id="date" class="form-control" required
                   value="{{ old('date', isset($attendance) ? $attendance->date->format('Y-m-d') : now()->format('Y-m-d')) }}">
            <small class="text-muted">Seleccione la fecha de asistencia</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="time">Hora <span class="text-danger">*</span></label>
            <input type="time" name="time" id="time" class="form-control" required
                   value="{{ old('time', isset($attendance) ? $attendance->time : now()->format('H:i')) }}">
            <small class="text-muted">Seleccione la hora de registro</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="schedule_id">Turno <span class="text-danger">*</span></label>
            <select name="schedule_id" id="schedule_id" class="form-control" required>
                <option value="">Seleccione un turno</option>
                @foreach($schedules as $s)
                    <option value="{{ $s->id }}"
                        {{ old('schedule_id', isset($attendance) ? $attendance->schedule_id : ($currentSchedule ? $currentSchedule->id : '')) == $s->id ? 'selected' : '' }}>
                        {{ $s->name }} ({{ $s->time_start }} - {{ $s->time_end }})
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Turno asignado automáticamente según la hora actual</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
    <div class="form-group">
        <label for="type">Tipo <span class="text-danger">*</span></label>
        {{-- Campo hidden que sí se envía --}}
        <input type="hidden" name="type" id="type_hidden">
        {{-- Select solo visual, no se envía --}}
        <select id="type_display" class="form-control" style="pointer-events: none; background-color: #e9ecef;">
            <option id="type_option_entrada" value="Entrada">Entrada</option>
            <option id="type_option_salida"  value="Salida">Salida</option>
        </select>
        <small class="text-muted" id="type-hint">
            <i class="fas fa-info-circle mr-1"></i> Se determina automáticamente
        </small>
    </div>
</div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Estado <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-control" required>
                <option value="Presente" {{ old('status', isset($attendance) ? $attendance->status : 'Presente') == 'Presente' ? 'selected' : '' }}>Presente</option>
                <option value="Ausente"  {{ old('status', isset($attendance) ? $attendance->status : '') == 'Ausente' ? 'selected' : '' }}>Ausente</option>
            </select>
            <small class="text-muted">Estado de la asistencia</small>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="notes">Notas</label>
    <textarea name="notes" id="notes" class="form-control" rows="3"
              placeholder="Agregue notas adicionales sobre la asistencia...">{{ old('notes', isset($attendance) ? $attendance->notes : '') }}</textarea>
    <small class="text-muted">Observaciones o comentarios sobre el registro</small>
</div>

<script>
$(document).ready(function () {

    // Select2
    $('.select2-employee').select2({
        placeholder: 'Buscar por nombre, apellido o DNI...',
        allowClear: true,
        minimumInputLength: 2,
        language: {
            inputTooShort: function () { return 'Escriba al menos 2 letras...'; },
            noResults:     function () { return 'No se encontraron resultados'; }
        },
        dropdownParent: $('#AttendanceModal')
    });

    // ── Auto-detectar turno según hora ────────────────────────────────────────
    $('#time').on('change', function () {
        var time = $(this).val();
        if (!time) return;

        $.get('/attendance/schedule-by-time', { time: time }, function (schedule) {
            if (schedule) {
                $('#schedule_id').val(schedule.id).trigger('change');
            } else {
                $('#schedule_id').val('');
                mostrarAlerta('warning', 'No se encontró un turno para la hora seleccionada.');
            }
        });
    });

    // ── Al cambiar empleado → info + tipo ─────────────────────────────────────
    $('#user_id').on('change', function () {
        var userId = $(this).val();

        if (!userId) {
            $('#employee-info').addClass('d-none');
            return;
        }

        // Info del empleado
        $.get('/attendance/user-info', { user_id: userId }, function (data) {
            if (data) {
                $('#info-name').text(data.name);
                $('#info-dni').text(data.dni);
                $('#info-email').text(data.email);
                $('#info-phone').text(data.phone);
                $('#employee-info').removeClass('d-none');
            }
        });

        actualizarTipo();
    });

    // ── Al cambiar fecha o turno → recalcular tipo ────────────────────────────
    $('#date, #schedule_id').on('change', function () {
        if ($('#user_id').val()) actualizarTipo();
    });

    // ── Función principal: detecta tipo automáticamente ───────────────────────
    function actualizarTipo() {
        var userId     = $('#user_id').val();
        var date       = $('#date').val();
        var scheduleId = $('#schedule_id').val();

        if (!userId || !date || !scheduleId) return;

        $.get('/attendance/type', {
            user_id:     userId,
            date:        date,
            schedule_id: scheduleId
        }, function (data) {

            // Establecer tipo automáticamente y deshabilitar el select
            $('#type_hidden').val(data.type);
            $('#type_display').val(data.type);

            // Mostrar hint
            var hintClass = data.type === 'Entrada' ? 'text-success' : 'text-info';
            $('#type-hint').html(
                '<i class="fas fa-info-circle mr-1"></i> ' + data.mensaje
            ).removeClass('text-success text-info text-warning').addClass(hintClass);

            // Registros del día
            if (data.registros && data.registros.length > 0) {
                var html = '<ul class="list-unstyled mb-0">';
                data.registros.forEach(function (r) {
                    var badge = r.type === 'Entrada' ? 'success' : 'info';
                    html += `<li>
                        <span class="badge badge-${badge} mr-1">${r.type}</span>
                        <strong>${r.time}</strong>
                        <small class="text-muted ml-1">${r.schedule}</small>
                    </li>`;
                });
                html += '</ul>';
                $('#registros-dia').html(html);
                $('#primer-registro-alert').addClass('d-none');
            } else {
                $('#registros-dia').html('<small class="text-muted">No hay registros para este día.</small>');
                if (data.type === 'Entrada') {
                    $('#primer-registro-alert').removeClass('d-none');
                }
            }
        });
    }

    function mostrarAlerta(tipo, mensaje) {
        $('#type-hint').html(
            '<i class="fas fa-exclamation-triangle mr-1"></i> ' + mensaje
        ).removeClass('text-success text-info text-warning').addClass('text-' + tipo);
    }

    // Si es edición, disparar el change para mostrar info
    var userId = $('#user_id').val();
    if (userId) {
        $('#user_id').trigger('change');
    }

    

});
</script>