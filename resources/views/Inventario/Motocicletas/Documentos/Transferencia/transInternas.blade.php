@extends('Index.base')
@section('title', 'Transferencias Internas')
@section('content')
    <div class="container-fluid">
        @include('Index.componentes.status')
        <div class="d-flex bd-highlight">
            <div class="p-2 flex-grow-1 bd-highlight">
                <a href="{{route('transferencias_internas.index')}}" class="btn btn-dark">
                    <i class="fa fa-arrow-left" aria-hidden="true"> Volver Atras</i>
                </a>
            </div>
            <div class="p-2 bd-highlight">
                <div id="SeccionBotonesTransferencia">
                    @foreach($trans1 as $info)
                        @if($info->estado_c == 3)
                            @if($info->estado  == 2)
                                <button class="btn btn-outline-danger" onclick="DenegarTransInterna()">Rechazar Transferencia</button>
                                <button class="btn btn-outline-success" onclick="AceptarTransInterna()">Aceptar Transferencia</button>
                            @endif
                        @endif
                    @endforeach
                        @foreach($trans1 as $info)
                            @if($info->estado_c == 3)
                                @if($info->estado  == 1)
                                    <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#ModalDeclinarTransferencia"
                                            data-cod="{{$info->cod_transferencia}}">
                                        Declinar Transferencia
                                    </button>
                                    <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#ModalTransferenciaExitosa"
                                            data-cod="{{$info->cod_transferencia}}">
                                        Aceptar Transferencia
                                    </button>
                                @endif
                            @endif
                        @endforeach
                </div>
            </div>
        </div>
        <hr>
        <h4 class="text-center">Datos de la Transferencia</h4>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Fecha Solicitada</th>
                    <th>Usuario Creacion</th>
                    <th>Encargado de Enviar</th>
                    <th>Almacen de Origen</th>
                    <th>Fecha de Solucion</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trans1 as $info)
                    <input type="text" id="codTransferencia" value="{{$info->cod_transferencia}}" hidden>
                    <tr>
                        <td>{{$info->cod_transferencia}}</td>
                        <td>{{$info->fecha_solicitada}}</td>
                        <td>{{$info->usuario}}</td>
                        <td>{{$info->nombre_col}}</td>
                        <td>{{$info->nombre_suc}}</td>
                        <td id="FechaSolucionTransferencia">{{$info->fecha_decision}}</td>
                        <td id="EstadoTransferencia">
                        @if($info->estado_c == 3 || $info->estado_c == 5)
                            @if($info->estado  == 2)
                                <span class="badge badge-warning">Sin Solucion</span>
                            @elseif($info->estado == 3)
                                <span class="badge badge-danger">Denegada x Supervisor</span>
                            @elseif($info->estado == 1)
                                <span class="badge badge-success">Aceptada sin Recibir</span>
                            @endif
                        @else
                            @if($info->estado_c  == 1)
                                    <span class="badge badge-success">Transferencia Exitosa</span>
                            @elseif($info->estado_c == 2)
                                    <span class="badge badge-danger">Declinada x Gerente</span>
                            @endif
                        @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Almacen de Destino</th>
                    <th>Encargado de Recibir</th>
                    <th>Usuario de Solucion</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trans2 as $info)
                    <tr>
                        <input type="text" value="{{$info->id}}" id="IdTransferenciaInterna" hidden>
                        <td>{{$info->nombre_suc}}</td>
                        <td>{{$info->nombre_col}}</td>
                        <td id="UsuarioDecision">{{$info->usuario}}</td>
                        <input type="text" value="{{Auth::user()->id}}" hidden id="InputIdUsuario">
                        <input type="text" value="{{Auth::user()->usuario}}" hidden id="InputUsuario">
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="overflow-y: scroll; height: 200px">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Chasis</th>
                    <th>Motor</th>
                    <th>Color</th>
                    <th>Ano</th>
                </tr>
                </thead>
                <tbody>
                @foreach($trans3 as $info)
                    <tr>
                        <td><a href="/inventario/motocicletas/ficha/{{$info->id_moto}}" target="_blank">{{$info->id_moto}}</a></td>
                        <td>{{$info->nombre}}</td>
                        <td>{{$info->nombre_mod}}</td>
                        <td>{{$info->chasis}}</td>
                        <td>{{$info->motor}}</td>
                        <td>{{$info->color}}</td>
                        <td>{{$info->ano}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <div class="modal fade" id="ModalDeclinarTransferencia" tabindex="-1" role="dialog" aria-labelledby="ModalDeclinarTransferencia" aria-hidden="true">
        @include('Inventario.Motocicletas.Documentos.Transferencia.Modals.ModalDeclinarTransferencia')
    </div>
    <div class="modal fade bd-example-modal-xl" id="ModalTransferenciaExitosa" tabindex="-1" role="dialog" aria-labelledby="ModalTransferenciaExitosa" aria-hidden="true">
        @include('Inventario.Motocicletas.Documentos.Transferencia.Modals.ModalTransferenciaExitosa')
    </div>
@endsection