<div class="row">
    <div class="col-md-12 form-group">
        {!! Form::label('name', 'Nombre de la Zona *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::text('name', null, [
    'class' => 'form-control rounded-xl custom-select-appearance',
    'placeholder' => 'Nombre de la zona',
    'required',
    'maxlength' => '150'
]) !!}
    </div>
</div>

<div class="row">
    <div class="col-md-4 form-group">
        {!! Form::label('department_id', 'Departamento *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::select('department_id', $departments ?? [], null, [
    'class' => 'form-control rounded-xl custom-select-appearance',
    'id' => 'cmbDepartment',
    'placeholder' => 'Seleccione...',
    'required'
]) !!}
    </div>
    <div class="col-md-4 form-group">
        {!! Form::label('province_id', 'Provincia *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::select('province_id', $provinces ?? [], null, [
    'class' => 'form-control rounded-xl custom-select-appearance',
    'id' => 'cmbProvince',
    'placeholder' => 'Seleccione...',
    'required'
]) !!}
    </div>
    <div class="col-md-4 form-group">
        {!! Form::label('district_id', 'Distrito *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::select('district_id', $districts ?? [], null, [
    'class' => 'form-control rounded-xl custom-select-appearance',
    'id' => 'cmbDistrict',
    'placeholder' => 'Seleccione...',
    'required'
]) !!}
    </div>
</div>

<div class="form-group mt-2">
    {!! Form::label('description', 'Descripción', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
    {!! Form::textarea('description', null, [
    'class' => 'form-control rounded-xl custom-select-appearance',
    'placeholder' => 'Agregue una descripción de la zona',
    'rows' => '2'
]) !!}
</div>

<div class="row mt-2">
    <div class="col-md-6 form-group">
        {!! Form::label('average_waste', 'Residuos Promedio (kg)', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::number('average_waste', null, [
    'class' => 'form-control rounded-xl custom-select-appearance',
    'placeholder' => 'Ej: 150.50',
    'step' => '0.01',
    'min' => '0'
]) !!}
        <small class="text-muted text-xs d-block mt-1">Cantidad promedio de residuos en kilogramos por día</small>
    </div>
    <div class="col-md-6 form-group">
        {!! Form::label('status', 'Estado *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::select('status', ['ACTIVO' => 'Activo', 'INACTIVO' => 'Inactivo'], null, [
    'class' => 'form-control rounded-xl custom-select-appearance',
    'required'
]) !!}
    </div>
</div>

{!! Form::hidden('coordinates', isset($zone) ? $zone->coordinates : '[]', ['id' => 'hiddenCoordinates']) !!}
{!! Form::hidden('area', null, ['id' => 'hiddenArea']) !!}

<div class="form-group mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="font-weight-bold text-xs uppercase text-secondary tracking-wider mb-0">Mapa interactivo de la zona
            *</label>
        <span class="badge badge-primary px-2.5 py-1" id="badgeCoordsCount"
            style="font-size: 11px; border-radius: 50px;">0 Puntos Registrados</span>
    </div>

    <div class="card border mb-2 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-light py-2 px-3">
            <span class="font-weight-bold text-xs uppercase text-secondary tracking-wider"><i
                    class="fas fa-list mr-1"></i> Coordenadas del Perímetro</span>
        </div>
        <div class="card-body p-2 bg-white" style="max-height: 140px; overflow-y: auto;" id="containerCoordinatesList">
            <p class="text-muted text-xs text-center my-2" id="textNoCoords">No has seleccionado ningún punto en el mapa
                todavía.</p>
            <div class="row no-gutters" id="wrapperCoordsItems"></div>
        </div>
    </div>

    <div id="zoneMapCanvas" class="w-100 shadow-sm border"
        style="height: 380px; border-radius: 12px; position: relative; overflow: hidden;"></div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <small class="text-muted text-xs"><i class="fas fa-info-circle mr-1"></i> Haz clics consecutivos directamente en
            el mapa para trazar el perímetro.</small>
        <button type="button" class="btn btn-xs btn-outline-danger font-weight-bold px-2" id="btnClearMap"
            style="font-size: 11px; border-radius: 10px; display: none;">
            <i class="fas fa-trash-alt mr-1"></i> Reiniciar Trazado Completo
        </button>
    </div>
</div>

<style>
    /* TRUCO PARA ANCHAR EL MODAL DINÁMICAMENTE */
    .modal-dialog {
        max-width: 850px !important;
        width: 850px !important;
    }

    .rounded-xl {
        border-radius: 10px !important;
    }

    .text-xs {
        font-size: 0.75rem;
    }

    .tracking-wider {
        letter-spacing: 0.06em;
    }

    .custom-select-appearance {
        padding: 0.45rem 1rem;
        height: calc(2.5rem + 2px);
        border: 1px solid #ced4da;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, .05);
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }

    .custom-select-appearance:focus {
        border-color: #2e5ea6;
        box-shadow: 0 0 0 3px rgba(46, 94, 150, 0.15);
    }

    .coord-item-badge {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .coord-item-badge:hover {
        background-color: #f1f3f5;
        border-color: #ced4da;
    }
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

<script>
    $(document).ready(function () {
        $('.modal-dialog').css({
            'max-width': '850px',
            'width': '850px'
        });

        // --- 1. LÓGICA DEL MAPA (LEAFLET + TURF) ---
        const defaultLat = -6.7622;
        const defaultLng = -79.8394;
        let map, activePolygon = null, markerPoints = [], coordinateList = [];

        // ID de la zona actual si estamos editando (evita que choque consigo misma)
        const currentZoneId = "{{ isset($zone) ? $zone->id : '' }}";
        let existingPolygonsGeoJSON = [];

        function initZoneMap() {
            if (map) return;

            map = L.map('zoneMapCanvas').setView([defaultLat, defaultLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Cargar capas de fondo e historial de puntos asignados
            loadExistingZonesOnMap();
            loadExistingCoordinates();

            map.on('click', function (e) {
                const lat = parseFloat(e.latlng.lat.toFixed(6));
                const lng = parseFloat(e.latlng.lng.toFixed(6));

                // Evaluar colisión matemática antes de insertar el punto
                if (isPointInsideExistingZone(lat, lng)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Área Restringida',
                        text: 'El punto seleccionado se encuentra dentro del perímetro de otra zona registrada.',
                        confirmButtonColor: '#2e5ea6'
                    });
                    return;
                }

                addCoordinatePoint(lat, lng);
            });
        }

        // Recupera todas las zonas activas vía AJAX y las pinta de un color aleatorio distintivo
        function loadExistingZonesOnMap() {
            $.ajax({
                url: "{{ route('admin.zone.mapdata') }}",
                type: "GET",
                success: function (zones) {
                    const colors = ['#3388ff', '#20c997', '#fd7e14', '#6f42c1', '#e83e8c', '#17a2b8', '#ffc107'];

                    zones.forEach((zone, index) => {
                        // Si es modo edición, ignoramos la misma geometría que estamos corrigiendo
                        if (currentZoneId && zone.id == currentZoneId) return;

                        if (zone.coords.length >= 3) {
                            const randomColor = colors[index % colors.length];

                            // Renderizado visual en el mapa
                            const poly = L.polygon(zone.coords, {
                                color: randomColor,
                                fillColor: randomColor,
                                fillOpacity: 0.12,
                                weight: 2,
                                dashArray: '4, 6'
                            }).addTo(map);

                            poly.bindPopup(`<strong>Zona Registrada:</strong> ${zone.name}`);

                            // Estructuración GeoJSON para cálculos con Turf.js [Longitud, Latitud]
                            const geoJsonCoords = zone.coords.map(c => [c[1], c[0]]);
                            geoJsonCoords.push(geoJsonCoords[0]); // Cerramos anillo geométrico

                            existingPolygonsGeoJSON.push({
                                name: zone.name,
                                polygon: turf.polygon([geoJsonCoords])
                            });
                        }
                    });
                },
                error: function (err) {
                    console.error("Error al cargar el mapa base de zonas residenciales:", err);
                }
            });
        }

        // Valida con Turf si el punto cae dentro del polígono indexado
        function isPointInsideExistingZone(lat, lng) {
            if (existingPolygonsGeoJSON.length === 0) return false;

            const currentPoint = turf.point([lng, lat]);

            for (let i = 0; i < existingPolygonsGeoJSON.length; i++) {
                const isInside = turf.booleanPointInPolygon(currentPoint, existingPolygonsGeoJSON[i].polygon);
                if (isInside) return true;
            }
            return false;
        }

        function addCoordinatePoint(lat, lng) {
            coordinateList.push({ lat, lng });

            const vertexMarker = L.circleMarker([lat, lng], {
                radius: 5, color: '#071D38', fillColor: '#ffffff', fillOpacity: 1, weight: 2
            }).addTo(map);

            markerPoints.push(vertexMarker);
            drawZonePolygon();
            syncCoordinatesToForm();
        }

        function drawZonePolygon() {
            const latLngs = coordinateList.map(pt => [pt.lat, pt.lng]);
            if (activePolygon) map.removeLayer(activePolygon);
            if (latLngs.length > 0) {
                activePolygon = L.polygon(latLngs, { color: '#dc3545', fillColor: '#dc3545', fillOpacity: 0.25, weight: 3 }).addTo(map);
            }
        }

        function syncCoordinatesToForm() {
            $('#hiddenCoordinates').val(JSON.stringify(coordinateList));

            // Calcular área
            if (coordinateList.length >= 3) {

                const polygonCoords = coordinateList.map(p => [p.lng, p.lat]);

                // cerrar el polígono
                polygonCoords.push([
                    coordinateList[0].lng,
                    coordinateList[0].lat
                ]);

                const polygon = turf.polygon([polygonCoords]);

                // área en metros cuadrados
                const area = turf.area(polygon);

                $('#hiddenArea').val(area);

                console.log("Área:", area, "m²");
            } else {
                $('#hiddenArea').val(0);
            }

            $('#badgeCoordsCount').text(`${coordinateList.length} Puntos Registrados`);

            if (coordinateList.length > 0) {
                $('#btnClearMap').fadeIn(100);
                $('#textNoCoords').hide();

                let htmlItems = '';
                coordinateList.forEach((pt, index) => {
                    htmlItems += `
                        <div class="col-md-4 p-1">
                            <div class="d-flex align-items-center justify-content-between p-1.5 px-2 coord-item-badge">
                                <span class="text-muted font-weight-bold text-xs mr-1" style="font-size:11px;">P${index + 1}:</span>
                                <span class="text-dark text-xs font-mono" style="font-size:11px; letter-spacing:-0.5px;">${pt.lat}, ${pt.lng}</span>
                                <button type="button" class="btn btn-link text-danger p-0 ml-2 btnDeleteSingleCoord" data-index="${index}" title="Eliminar este punto">
                                    <i class="fas fa-trash-alt" style="font-size: 11px;"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                $('#wrapperCoordsItems').html(htmlItems);
            } else {
                $('#btnClearMap').fadeOut(100);
                $('#textNoCoords').show();
                $('#wrapperCoordsItems').html('');
            }
        }

        $(document).on('click', '.btnDeleteSingleCoord', function () {
            const targetIndex = parseInt($(this).data('index'));

            if (markerPoints[targetIndex]) {
                map.removeLayer(markerPoints[targetIndex]);
            }

            coordinateList.splice(targetIndex, 1);
            markerPoints.splice(targetIndex, 1);

            drawZonePolygon();
            syncCoordinatesToForm();
        });

        function loadExistingCoordinates() {
            try {
                const rawData = $('#hiddenCoordinates').val();
                if (rawData && rawData !== '[]') {
                    const parsed = JSON.parse(rawData);
                    parsed.forEach(pt => addCoordinatePoint(pt.lat, pt.lng));
                    if (activePolygon) map.fitBounds(activePolygon.getBounds());
                }
            } catch (err) { console.error(err); }
        }

        $('#btnClearMap').click(function () {
            markerPoints.forEach(m => map.removeLayer(m));
            if (activePolygon) map.removeLayer(activePolygon);
            markerPoints = []; coordinateList = []; activePolygon = null;
            syncCoordinatesToForm();
        });

        setTimeout(function () {
            initZoneMap();
            if (map) { map.invalidateSize(); }
        }, 400);


        // --- 2. LÓGICA DE COMBOBOX ENCADENADOS (AJAX) ---
        if (!$('#cmbDepartment').val()) {
            $('#cmbProvince').html('<option value="">Seleccione...</option>');
            $('#cmbDistrict').html('<option value="">Seleccione...</option>');
        }

        $('#cmbDepartment').change(function () {
            const depId = $(this).val();
            $('#cmbProvince').html('<option value="">Cargando...</option>').val('');
            $('#cmbDistrict').html('<option value="">Seleccione...</option>').val('');

            if (!depId) {
                $('#cmbProvince').html('<option value="">Seleccione...</option>');
                return;
            }

            $.ajax({
                url: "{{ route('admin.locations.provinces', 'id') }}".replace('id', depId),
                type: "GET",
                success: function (res) {
                    let options = '<option value="">Seleccione...</option>';
                    res.forEach(prov => {
                        options += `<option value="${prov.id}">${prov.name}</option>`;
                    });
                    $('#cmbProvince').html(options);
                }
            });
        });

        $('#cmbProvince').change(function () {
            const provId = $(this).val();
            $('#cmbDistrict').html('<option value="">Cargando...</option>').val('');

            if (!provId) {
                $('#cmbDistrict').html('<option value="">Seleccione...</option>');
                return;
            }

            $.ajax({
                url: "{{ route('admin.locations.districts', 'id') }}".replace('id', provId),
                type: "GET",
                success: function (res) {
                    let options = '<option value="">Seleccione...</option>';
                    res.forEach(dist => {
                        options += `<option value="${dist.id}">${dist.name}</option>`;
                    });
                    $('#cmbDistrict').html(options);
                }
            });
        });
    });
</script>