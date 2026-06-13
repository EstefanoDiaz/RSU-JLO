{{-- Formulario: Nueva Programación --}}

{!! Form::open(['route' => 'admin.programacion.store', 'method' => 'POST', 'id' => 'formNuevaProgramacion']) !!}

<div class="row">
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('start_date', 'Fecha de inicio *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        {!! Form::date('start_date', null, ['class' => 'form-control', 'id' => 'progStartDate', 'required' => true]) !!}
    </div>
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('end_date', 'Fecha de fin *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        {!! Form::date('end_date', null, ['class' => 'form-control', 'id' => 'progEndDate', 'required' => true]) !!}
    </div>
    <div class="col-md-4 form-group mb-3 d-flex flex-column justify-content-end">
        <button type="button" class="btn btn-outline-primary font-weight-bold" id="btnValidarDisp">
            <i class="fas fa-check-circle mr-1"></i> Validar disponibilidad
        </button>
    </div>
</div>

<div class="form-group mb-3">
    {!! Form::label('group_id', 'Grupo de Personal *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
    <select name="group_id" id="selGroup" class="form-control" required>
        <option value="">-- Seleccione un grupo --</option>
        @foreach($groups as $group)
            <option value="{{ $group->id }}">{{ $group->name }}</option>
        @endforeach
    </select>
    <small class="text-muted">Busque por nombre, zona o turno</small>
</div>

{{-- Alerta de cambio de datos --}}
<div id="alertDatosCambiados" class="alert alert-info d-none mb-3" role="alert">
    <i class="fas fa-info-circle mr-1"></i> Los datos han cambiado. Valide la disponibilidad nuevamente.
</div>

{{-- Alerta de error --}}
<div id="alertErrors" class="alert alert-danger d-none mb-3" role="alert">
    <strong><i class="fas fa-exclamation-triangle mr-1"></i> Hay errores que corregir</strong>
    <ul id="listErrors" class="mb-2 mt-2"></ul>
    <div id="blockSuggestions" class="d-none">
        <i class="fas fa-lightbulb text-warning mr-1"></i> <strong>Sugerencias:</strong>
        <ul id="listSuggestions" class="mb-0 mt-1"></ul>
    </div>
</div>

{{-- Alerta de éxito de validación --}}
<div id="alertSuccess" class="alert alert-success d-none mb-3" role="alert">
    <i class="fas fa-check-circle mr-1"></i> <strong>Todo está correcto. Puede guardar la programación.</strong>
</div>

{{-- Info del grupo seleccionado --}}
<div id="groupInfoRow" class="d-none mb-3">
    <div class="row text-center border rounded p-2 bg-light mx-0">
        <div class="col-3">
            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Grupo</small>
            <strong id="infoGrupo">-</strong>
        </div>
        <div class="col-3">
            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Zona</small>
            <strong id="infoZona">-</strong>
        </div>
        <div class="col-3">
            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Turno</small>
            <strong id="infoTurno">-</strong>
        </div>
        <div class="col-3">
            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Vehículo</small>
            <strong id="infoVehiculo">-</strong>
        </div>
    </div>
</div>

{{-- Personal (rellenado automáticamente, editable) --}}
<div class="row">
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('conductor_id', 'Conductor *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="conductor_id" id="selConductor" class="form-control" required>
            <option value="">-- Conductor --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('ayudante1_id', 'Ayudante 1 *', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="ayudante1_id" id="selAyudante1" class="form-control" required>
            <option value="">-- Ayudante 1 --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 form-group mb-3">
        {!! Form::label('ayudante2_id', 'Ayudante 2', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
        <select name="ayudante2_id" id="selAyudante2" class="form-control">
            <option value="">-- Opcional --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Días de trabajo --}}
<div class="form-group mb-3">
    <label class="font-weight-bold text-xs text-secondary text-uppercase d-block">Días de trabajo *</label>
    <div class="d-flex flex-wrap" style="gap:.5rem;">
        @php
            $dias = [
                ['label' => 'Lunes',     'value' => 1],
                ['label' => 'Martes',    'value' => 2],
                ['label' => 'Miércoles', 'value' => 3],
                ['label' => 'Jueves',    'value' => 4],
                ['label' => 'Viernes',   'value' => 5],
                ['label' => 'Sábado',    'value' => 6],
                ['label' => 'Domingo',   'value' => 0],
            ];
        @endphp
        @foreach($dias as $dia)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="work_days[]" id="dia_{{ $dia['value'] }}" value="{{ $dia['value'] }}">
                <label class="form-check-label" for="dia_{{ $dia['value'] }}">{{ $dia['label'] }}</label>
            </div>
        @endforeach
    </div>
</div>

{{-- Observaciones --}}
<div class="form-group mb-3">
    {!! Form::label('observations', 'Observaciones', ['class' => 'font-weight-bold text-xs text-secondary text-uppercase']) !!}
    {!! Form::textarea('observations', null, ['class' => 'form-control', 'placeholder' => 'Observaciones adicionales...', 'rows' => 2]) !!}
</div>

<div class="d-flex justify-content-end mt-3 pt-2 border-top">
    <button type="button" class="btn btn-danger mr-2" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Cancelar
    </button>
    <button type="submit" id="btnGuardarProg" class="btn btn-primary font-weight-bold" disabled>
        <i class="fas fa-save mr-1"></i> Guardar
    </button>
</div>

{!! Form::close() !!}

<script>
$(document).ready(function() {
    let validationPassed = false;

    function markDirty() {
        validationPassed = false;
        $('#btnGuardarProg').prop('disabled', true);
        $('#alertSuccess').addClass('d-none');
        $('#alertErrors').addClass('d-none');
        let start = $('#progStartDate').val();
        let end   = $('#progEndDate').val();
        let group = $('#selGroup').val();
        if (start && end && group) {
            $('#alertDatosCambiados').removeClass('d-none');
        }
    }

    $('#selGroup').on('change', function() {
        let groupId = $(this).val();
        if (!groupId) {
            $('#groupInfoRow').addClass('d-none');
            markDirty();
            return;
        }
        $.get("{{ route('admin.personal-group.data', 'ID') }}".replace('ID', groupId), function(data) {
            $('#infoGrupo').text(data.name);
            $('#infoZona').text(data.zone.name);
            $('#infoTurno').text(data.schedule.name + ' (' + data.schedule.time_start + ' - ' + data.schedule.time_end + ')');
            $('#infoVehiculo').text(data.vehicle.name);
            $('#groupInfoRow').removeClass('d-none');

            // Auto-fill personal
            $('#selConductor').val(data.conductor.id);
            $('#selAyudante1').val(data.ayudante1.id);
            if (data.ayudante2) {
                $('#selAyudante2').val(data.ayudante2.id);
            } else {
                $('#selAyudante2').val('');
            }
        });
        markDirty();
    });

    $('#progStartDate, #progEndDate').on('change', markDirty);
    $('#selConductor, #selAyudante1, #selAyudante2').on('change', markDirty);

    $('#btnValidarDisp').on('click', function() {
        let start  = $('#progStartDate').val();
        let end    = $('#progEndDate').val();
        let cond   = $('#selConductor').val();
        let ay1    = $('#selAyudante1').val();
        let ay2    = $('#selAyudante2').val();
        let group  = $('#selGroup').val();

        if (!start || !end || !cond || !ay1 || !group) {
            Swal.fire('Atención', 'Complete las fechas, grupo de personal y asigne el conductor y ayudante 1 antes de validar.', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.programacion.validate') }}",
            type: 'POST',
            data: {
                _token:        '{{ csrf_token() }}',
                start_date:    start,
                end_date:      end,
                conductor_id:  cond,
                ayudante1_id:  ay1,
                ayudante2_id:  ay2 || null,
            },
            success: function(res) {
                $('#alertDatosCambiados').addClass('d-none');
                if (res.status === 'success') {
                    $('#alertErrors').addClass('d-none');
                    $('#alertSuccess').removeClass('d-none');
                    validationPassed = true;
                    $('#btnGuardarProg').prop('disabled', false);
                } else {
                    showErrors(res.errors, res.suggestions);
                    validationPassed = false;
                    $('#btnGuardarProg').prop('disabled', true);
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al validar', 'error');
            }
        });
    });

    function showErrors(errors, suggestions) {
        $('#alertSuccess').addClass('d-none');
        let errHtml = '';
        errors.forEach(e => errHtml += '<li>' + e + '</li>');
        $('#listErrors').html(errHtml);

        if (suggestions && suggestions.length > 0) {
            let sugHtml = '';
            suggestions.forEach(s => sugHtml += '<li>' + s + '</li>');
            $('#listSuggestions').html(sugHtml);
            $('#blockSuggestions').removeClass('d-none');
        } else {
            $('#blockSuggestions').addClass('d-none');
        }
        $('#alertErrors').removeClass('d-none');
    }

    $('#formNuevaProgramacion').on('submit', function(e) {
        e.preventDefault();
        if (!validationPassed) {
            Swal.fire('Atención', 'Debe validar la disponibilidad antes de guardar.', 'warning');
            return;
        }
        let workDays = $('input[name="work_days[]"]:checked');
        if (workDays.length === 0) {
            Swal.fire('Atención', 'Debe seleccionar al menos un día de trabajo.', 'warning');
            return;
        }
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#ProgramacionModal').modal('hide');
                if (typeof tableProg !== 'undefined') tableProg.ajax.reload(null, false);
                Swal.fire('¡Guardado!', res.message, 'success');
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
            }
        });
    });
});
</script>
