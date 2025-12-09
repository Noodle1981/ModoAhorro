@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h2 class="h4 mb-0"><i class="fas fa-user-md mr-2"></i>Diagnóstico de Salud Térmica</h2>
                    <p class="mb-0 small mt-2">Responde estas preguntas para evaluar la aislación de tu casa</p>
                </div>
                <div class="card-body p-5">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('thermal.store', $entity) }}" method="POST">
                        @csrf

                        <!-- 1. Techo -->
                        <h5 class="text-gray-800 mb-3"><i class="fas fa-home mr-2 text-primary"></i>1. ¿Cómo es tu techo?</h5>
                        <div class="form-group mb-4 pl-3">
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="roof1" name="roof_type" class="custom-control-input" value="sheet_metal" {{ old('roof_type', $entity->thermal_profile['roof_type'] ?? '') == 'sheet_metal' ? 'checked' : '' }} required>
                                <label class="custom-control-label" for="roof1">Chapa (Metal)</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="roof2" name="roof_type" class="custom-control-input" value="concrete_slab" {{ old('roof_type', $entity->thermal_profile['roof_type'] ?? '') == 'concrete_slab' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="roof2">Losa (Hormigón)</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="roof3" name="roof_type" class="custom-control-input" value="wood_tiles" {{ old('roof_type', $entity->thermal_profile['roof_type'] ?? '') == 'wood_tiles' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="roof3">Tejas / Madera</label>
                            </div>
                            
                            <div class="custom-control custom-checkbox mt-3">
                                <input type="checkbox" class="custom-control-input" id="roof_insulation" name="roof_insulation" {{ old('roof_insulation', $entity->thermal_profile['roof_insulation'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="roof_insulation">¿Tiene aislación térmica? (Membrana, cielorraso con lana de vidrio, etc.)</label>
                            </div>
                        </div>

                        <hr>

                        <!-- 2. Ventanas -->
                        <h5 class="text-gray-800 mb-3"><i class="fas fa-window-maximize mr-2 text-primary"></i>2. ¿Cómo son tus ventanas?</h5>
                        <div class="form-group mb-4 pl-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="small text-muted text-uppercase font-weight-bold">Tipo de Vidrio</label>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="win1" name="window_type" class="custom-control-input" value="single_glass" {{ old('window_type', $entity->thermal_profile['window_type'] ?? '') == 'single_glass' ? 'checked' : '' }} required>
                                        <label class="custom-control-label" for="win1">Vidrio Simple (Común)</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="win2" name="window_type" class="custom-control-input" value="dvh" {{ old('window_type', $entity->thermal_profile['window_type'] ?? '') == 'dvh' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="win2">Doble Vidrio (DVH)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted text-uppercase font-weight-bold">Marcos</label>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="frame1" name="window_frame" class="custom-control-input" value="aluminum" {{ old('window_frame', $entity->thermal_profile['window_frame'] ?? '') == 'aluminum' ? 'checked' : '' }} required>
                                        <label class="custom-control-label" for="frame1">Metal / Aluminio</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="frame2" name="window_frame" class="custom-control-input" value="wood" {{ old('window_frame', $entity->thermal_profile['window_frame'] ?? '') == 'wood' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="frame2">Madera</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="frame3" name="window_frame" class="custom-control-input" value="pvc" {{ old('window_frame', $entity->thermal_profile['window_frame'] ?? '') == 'pvc' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="frame3">PVC</label>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-control custom-checkbox mt-3 bg-light p-3 rounded border border-warning">
                                <input type="checkbox" class="custom-control-input" id="drafts" name="drafts_detected" {{ old('drafts_detected', $entity->thermal_profile['drafts_detected'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label text-warning font-weight-bold" for="drafts">
                                    <i class="fas fa-wind mr-1"></i> ¿Sientes "chifletes" o entra aire por los bordes?
                                </label>
                            </div>
                        </div>

                        <hr>

                        <!-- 3. Orientación y Ventilación -->
                        <h5 class="text-gray-800 mb-3"><i class="fas fa-compass mr-2 text-primary"></i>3. Orientación y Ventilación</h5>
                        <div class="form-group mb-4 pl-3">
                            <label class="font-weight-bold">¿Cómo está orientada la fachada principal de la vivienda?</label>
                            <p class="small text-muted mb-2">Piensa en dónde da el sol al mediodía (Norte) o si tienes ventanas enfrentadas.</p>
                            
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="ori1" name="orientation" class="custom-control-input" value="norte_sur" {{ old('orientation', $entity->thermal_profile['orientation'] ?? '') == 'norte_sur' ? 'checked' : '' }} required>
                                <label class="custom-control-label" for="ori1">Eje Norte-Sur (Ideal)</label>
                                <small class="d-block text-muted ml-4">Sol en invierno / Fresco en verano</small>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="ori2" name="orientation" class="custom-control-input" value="este_oeste" {{ old('orientation', $entity->thermal_profile['orientation'] ?? '') == 'este_oeste' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="ori2">Eje Este-Oeste</label>
                                <small class="d-block text-muted ml-4">Sol bajo y caluroso por la mañana/tarde</small>
                            </div>
                            <div class="custom-control custom-radio mb-3">
                                <input type="radio" id="ori3" name="orientation" class="custom-control-input" value="diagonal" {{ old('orientation', $entity->thermal_profile['orientation'] ?? '') == 'diagonal' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="ori3">Diagonal / Otra</label>
                            </div>

                            <div class="custom-control custom-checkbox mt-3 bg-light p-3 rounded border border-success">
                                <input type="checkbox" class="custom-control-input" id="south_win" name="south_window" {{ old('south_window', $entity->thermal_profile['south_window'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label text-success font-weight-bold" for="south_win">
                                    <i class="fas fa-wind mr-1"></i> ¿Tenés ventanas al SUR para ingreso de aire fresco?
                                </label>
                            </div>
                        </div>

                        <hr>

                        <!-- 4. Sombra -->
                        <h5 class="text-gray-800 mb-3"><i class="fas fa-tree mr-2 text-primary"></i>4. Sombra y Entorno</h5>
                        <div class="form-group mb-4 pl-3">
                            <label>¿Cuánta sombra recibe tu casa por árboles o edificios vecinos?</label>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="sun1" name="sun_exposure" class="custom-control-input" value="high" {{ old('sun_exposure', $entity->thermal_profile['sun_exposure'] ?? '') == 'high' ? 'checked' : '' }} required>
                                <label class="custom-control-label" for="sun1">Ninguna (Sol directo todo el día)</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="sun2" name="sun_exposure" class="custom-control-input" value="medium" {{ old('sun_exposure', $entity->thermal_profile['sun_exposure'] ?? '') == 'medium' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sun2">Parcial (Sombra de árboles/edificios algunas horas)</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="sun3" name="sun_exposure" class="custom-control-input" value="low" {{ old('sun_exposure', $entity->thermal_profile['sun_exposure'] ?? '') == 'low' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sun3">Mucha Sombra (Muy protegido del sol)</label>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                Calcular Diagnóstico <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
