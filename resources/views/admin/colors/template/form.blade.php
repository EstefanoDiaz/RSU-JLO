<div class="form-group">
    {!! Form::label('name', 'Nombre del Color') !!}
    {!! Form::text('name', null, [
        'class' => 'form-control',
        'placeholder' => 'Ej: Azul, Gris Metálico, Blanco Perlado...',
        'required',
        'maxlength' => '100'
    ]) !!}
</div>

<div class="form-group">
    {!! Form::label('code', 'Código del Color (RGB / HEX)') !!}
    <div class="input-group">
        {!! Form::text('code', isset($color) ? $color->code : '#FFFFFF', [
            'class' => 'form-control',
            'id' => 'txtColorCode',
            'placeholder' => '#FFFFFF',
            'required',
            'maxlength' => '7'
        ]) !!}
        <div class="input-group-append">
            <input type="color" id="htmlColorPicker" class="form-control p-1" style="width: 50px; height: 38px; cursor: pointer;" value="{{ isset($color) ? $color->code : '#FFFFFF' }}">
        </div>
    </div>
    <small class="text-muted">Puedes escribir el código hexadecimal manualmente o presionar el selector de la derecha.</small>
</div>

<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::textarea('description', null, [
        'class' => 'form-control',
        'placeholder' => 'Agregue una descripción del color...',
        'rows' => '3'
    ]) !!}
</div>

<div class="form-group">
    <label class="font-weight-bold">Vista Previa del Color:</label>
    <div id="boxColorPreview" class="text-white text-center font-weight-bold shadow rounded p-3" 
         style="background-color: {{ isset($color) ? $color->code : '#FFFFFF' }}; font-size: 16px; letter-spacing: 1px;">
        <span id="lblColorCodePreview">{{ isset($color) ? $color->code : '#FFFFFF' }}</span>
    </div>
</div>

<script>
    $(document).ready(function() {
        function updatePreview(hexColor) {
            if (/^#[0-9A-F]{6}$/i.test(hexColor)) {
                $('#boxColorPreview').css('background-color', hexColor);
                $('#lblColorCodePreview').text(hexColor.toUpperCase());
            }
        }

        // SELECTORRRRR DE TONALIDADDD
        $('#htmlColorPicker').on('input change', function() {
            let selectedColor = $(this).val().toUpperCase();
            $('#txtColorCode').val(selectedColor);
            updatePreview(selectedColor);
        });

        // MANUALMENTEEEEE EL CODIGO
        $('#txtColorCode').on('input', function() {
            let enteredColor = $(this).val();
            
            if (enteredColor.length > 0 && !enteredColor.startsWith('#')) {
                enteredColor = '#' + enteredColor;
                $(this).val(enteredColor);
            }
            
            if (enteredColor.length > 7) {
                enteredColor = enteredColor.substring(0, 7);
                $(this).val(enteredColor);
            }

            if (/^#[0-9A-F]{6}$/i.test(enteredColor)) {
                $('#htmlColorPicker').val(enteredColor);
                updatePreview(enteredColor);
            }
        });
    });
</script>