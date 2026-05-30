@extends('adminlte::page')

@section('title', 'Dashboard RSU')

@section('content_header')
@stop

@section('content')
<div class="container-fluid pb-4">
    <div class="p-2"></div>
    <div class="card bg-primary text-white text-center shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #0056b3, #007bff);">
        <div class="card-body py-4">
            <h2 class="font-weight-bold mb-2">⭐ ¡Bienvenido al Sistema RSU!</h2>
            <p class="lead mb-3">Sistema de Gestión de Residuos Sólidos Urbanos - Municipalidad José Leonardo Ortiz</p>
            <div class="d-inline-flex align-items-center bg-white text-dark px-3 py-1 rounded-pill small">
                <i class="fas fa-info-circle mr-2 text-primary"></i>
                <span>Utiliza el menú lateral para navegar entre los diferentes módulos del sistema</span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm" style="border-radius: 8px;">
        <div class="card-header bg-info text-white font-weight-bold py-2 d-flex align-items-center" style="border-top-left-radius: 8px; border-top-right-radius: 8px;">
            <i class="fas fa-info-circle mr-2"></i> Sistema de Gestión de Residuos Sólidos Urbanos
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5 text-center mb-4 mb-md-0">
                    <div class="img-thumbnail shadow-sm p-0 mb-3 overflow-hidden" style="border-radius: 6px;">
                        <img src="{{ asset('images/palacio.jpg') }}" alt="Municipalidad JLO" class="img-fluid w-100" style="object-fit: cover; min-height: 220px;">
                    </div>
                    <span class="btn btn-primary font-weight-bold px-3 shadow-sm btn-sm" style="border-radius: 4px;">
                        <i class="fas fa-building mr-1"></i> Gobierno Local
                    </span>
                </div>

                <div class="col-md-7">
                    <h3 class="text-primary font-weight-bold mb-3 d-flex align-items-center">
                        <i class="fas fa-city mr-2 text-info"></i> Municipalidad Distrital de José Leonardo Ortiz
                    </h3>
                    <p class="text-muted text-justify mb-4" style="line-height: 1.6;">
                        Sistema integral para la gestión eficiente de los residuos sólidos urbanos del distrito, optimizando rutas de recolección, administrando personal y mejorando la calidad del servicio hacia la comunidad josefina.
                    </p>

                    <div class="row mb-4">
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center p-2 rounded hover-shadow" style="background-color: #f8f9fa;">
                                <i class="fas fa-users-cog fa-2x text-dark mr-3" style="width: 40px; text-align: center;"></i>
                                <span class="font-weight-bold text-secondary">Gestión de Personal</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center p-2 rounded hover-shadow" style="background-color: #f8f9fa;">
                                <i class="fas fa-truck fa-2x text-primary mr-3" style="width: 40px; text-align: center;"></i>
                                <span class="font-weight-bold text-secondary">Control de Vehículos</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center p-2 rounded hover-shadow" style="background-color: #f8f9fa;">
                                <i class="fas fa-map-marked-alt fa-2x text-warning mr-3" style="width: 40px; text-align: center;"></i>
                                <span class="font-weight-bold text-secondary">Planificación de Rutas</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center p-2 rounded hover-shadow" style="background-color: #f8f9fa;">
                                <i class="fas fa-user-clock fa-2x text-success mr-3" style="width: 40px; text-align: center;"></i>
                                <span class="font-weight-bold text-secondary">Seguimiento de Asistencia</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <div class="d-flex align-items-center p-2 rounded hover-shadow" style="background-color: #f8f9fa;">
                                <i class="fas fa-file-signature fa-2x text-danger mr-3" style="width: 40px; text-align: center;"></i>
                                <span class="font-weight-bold text-secondary">Gestión de Contratos</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center p-2 rounded hover-shadow" style="background-color: #f8f9fa;">
                                <i class="fas fa-umbrella-beach fa-2x text-purple mr-3" style="width: 40px; text-align: center;"></i>
                                <span class="font-weight-bold text-secondary">Control de Vacaciones</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                        <span class="badge badge-success px-3 py-2 mr-2 mb-2 shadow-sm" style="border-radius: 20px; font-size: 0.85rem;">
                            <i class="fas fa-leaf mr-1"></i> Eco-Friendly
                        </span>
                        <span class="badge badge-info px-3 py-2 mr-2 mb-2 shadow-sm" style="border-radius: 20px; font-size: 0.85rem;">
                            <i class="fas fa-sync mr-1"></i> Sostenible
                        </span>
                        <span class="badge badge-warning text-dark px-3 py-2 mb-2 shadow-sm" style="border-radius: 20px; font-size: 0.85rem; font-weight: bold;">
                            <i class="fas fa-heart text-dark mr-1"></i> Responsable
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .text-purple { color: #6f42c1; }
        .hover-shadow:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            transform: translateY(-1px);
            transition: all 0.2s ease-in-out;
        }
    </style>
@stop

@section('js')
    <script> console.log("Panel RSU cargado correctamente en AdminLTE."); </script>
@stop