@extends('layout')

@section('title', 'Inicio')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedes-Tuberculosis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/Home.css?v=1.0') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row" >
            <h1 id="titulo">Tuberculosis (TB)</h1> <hr id="h">
        </div>

        <div class="row">
            <p id="textoA">Es una enfermedad infecciosa que suele afectar a los pulmones. Es tratable, y por lo tanto se puede evitar la muerte</p>
        </div>
        <div class="row" id="FondoP1">
            <div class="col-12 col-md-4 text-center">
                <img class="Pf img-fluid" src="/images/logoColor.png" alt="Logo">
            </div>
            <div class="col-12 col-md-8">
                <h3 id="textoMedio">¿Qué es la Tuberculosis?</h3>
                <p id="textoMedio">
                    La tuberculosis es una enfermedad infecciosa causada por Mycobacterium tuberculosis, una bacteria que
                    casi siempre afecta a los pulmones. Se transmite de persona a persona a través del aire. Los síntomas de la tuberculosis
                    activa incluyen tos, dolores torácicos, debilidad, pérdida de peso, fiebre y sudores nocturnos. En las personas sanas,
                    la infección no suele causar síntomas, porque el sistema inmunitario de la persona actúa para bloquear la bacteria.
                </p>
            </div>
        </div>
      
        <div class="row" id="fondoD">
          
            <div class="col-md-6" id="fondoBacte">
               
                <div class="info-section mb-4">
                    <div class="section-header">
                        <h1 class="til">Causada por...</h1>
                    </div>
                    <div class="d-flex align-items-center mt-3">
                        <div class="icon-circle me-3">
                            <img class="bac" src="/images/bacterias.png" alt="Logo">
                           
                        </div>
                        <div>
                            <h6 class="til">BACTERIA</h6>
                            <p class="mb-0" id="text">• Mycobacterium tuberculosis</p>
                        </div>
                    </div>
                </div>


                <div class="info-section">
                    <div class="section-header">
                        <h5 class="til">Se transmite...</h5>
                    </div>
                    <div class="mt-3 ">
                        <p id="text">...de persona a persona a través del aire.</p>
                        <div class="text-center mt-3">
                            <img class="tos" src="/images/tos.png" alt="Logo">
                        </div>
                    </div>
                </div>
            </div>

   
            <div class="col-md-6">

                <div class="info-section mb-4">
                    <div class="section-header">
                        <h5 class="til">Síntomas</h5>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="symptom-icon me-4">
                            <img class="sin"  src="/images/medico.png" alt="Logo">
                        
                        </div>
                        <div>
                            <ul id="text" class="list-unstyled">
                                <li>• tos</li>
                                <li>• esputo (a veces con sangre)</li>
                                <li>• dolor torácico</li>
                                <li>• debilidad</li>
                                <li>• pérdida de peso</li>
                                <li>• fiebre</li>
                                <li>• sudoración nocturna</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3 ">
                        <p id="text">En personas sanas la infección suele ser asintomática, dado que su sistema inmunitario actúa formando una barrera alrededor de la bacteria</p>
                    </div>
                </div>


                <div class="info-section">
                    <div class="section-header">
                        <h5 class="til">Tratamiento y precaución</h5>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="treatment-icon me-4" id="icon-circle">
                        <img class="sin"  src="/images/tableta.png" alt="Logo">
                        </div>
                        <div>
                            <ul id="text" class="list-unstyled">
                                <li>• Puede tratarse con antibióticos durante 6 meses</li>
                                <li>• Es muy importante que los pacientes con TB conozcan su estado de VIH</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
@endsection