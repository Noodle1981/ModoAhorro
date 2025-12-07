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
                    <form action="{{ route('thermal.store', $entity) }}" method="POST">
                        @csrf

                        <!-- 1. Techo -->
                        <h5 class="text-gray-800 mb-3"><i class="fas fa-home mr-2 text-primary"></i>1. ¿Cómo es tu techo?</h5>
                        <div class="form-group mb-4 pl-3">
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="roof1" name="roof_type" class="custom-control-input" value="sheet_metal" required>
                                <label class="custom-control-label" for="roof1">Chapa (Metal)</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="roof2" name="roof_type" class="custom-control-input" value="concrete_slab">
                                <label class="custom-control-label" for="roof2">Losa (Hormigón)</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="roof3" name="roof_type" class="custom-control-input" value="wood_tiles">
                                <label class="custom-control-label" for="roof3">Tejas / Madera</label>
                            </div>
                            
                            <div class="custom-control custom-checkbox mt-3">
                                <input type="checkbox" class="custom-control-input" id="roof_insulation" name="roof_insulation">
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
                                        <input type="radio" id="win1" name="window_type" class="custom-control-input" value="single_glass" required>
                                        <label class="custom-control-label" for="win1">Vidrio Simple (Común)</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="win2" name="window_type" class="custom-control-input" value="dvh">
                                        <label class="custom-control-label" for="win2">Doble Vidrio (DVH)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted text-uppercase font-weight-bold">Marcos</label>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="frame1" name="window_frame" class="custom-control-input" value="aluminum" required>
                                        <label class="custom-control-label" for="frame1">Metal / Aluminio</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="frame2" name="window_frame" class="custom-control-input" value="wood">
                                        <label class="custom-control-label" for="frame2">Madera</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="frame3" name="window_frame" class="custom-control-input" value="pvc">
                                        <label class="custom-control-label" for="frame3">PVC</label>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-control custom-checkbox mt-3 bg-light p-3 rounded border border-warning">
                                <input type="checkbox" class="custom-control-input" id="drafts" name="drafts_detected">
                                <label class="custom-control-label text-warning font-weight-bold" for="drafts">
                                    <i class="fas fa-wind mr-1"></i> ¿Sientes "chifletes" o entra aire por los bordes?
                                </label>
                            </div>
                        </div>

                        <hr>

                        <!-- 3. Sol -->
                        <h5 class="text-gray-800 mb-3"><i class="fas fa-sun mr-2 text-primary"></i>3. Exposición al Sol</h5>
                        <div class="form-group mb-4 pl-3">
                            <label>¿Cuánto sol directo reciben tus paredes/ventanas principales en verano?</label>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="sun1" name="sun_exposure" class="custom-control-input" value="high" required>
                                <label class="custom-control-label" for="sun1">Mucho (Todo el día / Orientación Norte-Oeste)</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="sun2" name="sun_exposure" class="custom-control-input" value="medium">
                                <label class="custom-control-label" for="sun2">Moderado (Algunas horas)</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="sun3" name="sun_exposure" class="custom-control-input" value="low">
                                <label class="custom-control-label" for="sun3">Poco (Mucha sombra / Sur)</label>
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
