<?php

namespace SurtidoraLainez\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SurtidoraLainez\AlmacenAltualPlaca;
use SurtidoraLainez\EntradaMotocicleta;
use SurtidoraLainez\FotosBoleta;
use SurtidoraLainez\FotosPlaca;
use SurtidoraLainez\HistorialUsuario;
use SurtidoraLainez\Http\Requests\SavePlaca;
use SurtidoraLainez\Placa;
use SurtidoraLainez\Salida;
use SurtidoraLainez\Sucursal;

class PlacasController extends Controller
{
    public function ingreso(){
        $motos = EntradaMotocicleta::join('marcas','marcas.id','=','entrada_motocicletas.marca_id')
            ->join('modelos','modelos.id','=','entrada_motocicletas.modelo_id')
            ->join('salidas','salidas.moto_id','=','entrada_motocicletas.id')
            ->join('clientes','clientes.id','=','cliente_id')
            ->join('sucursals','sucursals.id','=','salidas.sucrusal_id')
            ->select('marcas.nombre', 'modelos.nombre_mod','entrada_motocicletas.id','entrada_motocicletas.chasis',
                'entrada_motocicletas.motor','entrada_motocicletas.color', 'entrada_motocicletas.id_moto','entrada_motocicletas.ano',
                'modelos.cilindraje','clientes.nombres','clientes.apellidos','clientes.rtn','clientes.identidad', 'salidas.id as id_sal',
                'sucursals.nombre as nombre_suc', 'salidas.sucrusal_id')
            ->where('entrada_motocicletas.estado_placa',1)
            ->get();
        $almacenes = Sucursal::all();

        return view('Placas.ingreso', compact('motos','almacenes'));
    }

    public function save_ingreso(SavePlaca $request){
        $fotosPlaca = $request->file('FilePlaca');
        $fotosBoleta = $request->file('FileBoleta');
        $nuevahubicacionActual = new AlmacenAltualPlaca();

        $nueva_placa = new Placa();
        $nueva_placa->num_boleta = $request->input('NumeroBoleta');
        $nueva_placa->comprobante = $request->input('Comprobante');
        $nueva_placa->fecha_vencimiento = $request->input('FechaVencimiento');
        $nueva_placa->num_placa = $request->input('Placa');
        $nueva_placa->identificacion = $request->input('Identificacion');
        $nueva_placa->propietario = $request->input('Propietario');
        $nueva_placa->ano = $request->input('Año');
        $nueva_placa->observaciones = $request->input('Observaciones');
        $nueva_placa->estado = 1;
        $nueva_placa->usuario_registrador = $request->input('Usuario');
        $nueva_placa->id_moto = $request->input('IdMoto');
        $nueva_placa->almacen_entrada = $request->input('SelectAlmacen');
        $nueva_placa->num_ingreso = $request->input('NumGuia');
        $nueva_placa->estado_enlazo = $request->input('RadioOpcion');
        $nueva_placa->venta_id = $request->input('IdVenta');
        $nueva_placa->sucursal_final = $request->input('IdSucursal');
        $nueva_placa->save();

        $nuevahubicacionActual->almacen_actual = $request->input('SelectAlmacen');
        $nuevahubicacionActual->placa_id = $nueva_placa->id;
        $nuevahubicacionActual->save();

        $opcion = $request->input('RadioOpcion');
        $usuarios = DB::table('users')->select('usuario')->where('id', $request->input('Usuario'))->get();

        $NuevoHistorial = new HistorialUsuario();
        foreach ($usuarios as $usuario){
            $NuevoHistorial->id_usuario = $request->input('Usuario');
            $NuevoHistorial->usuario = $usuario->usuario;
            $NuevoHistorial->descripcion = 'Registro una boleta con numero'.$request->input('num_boleta').' con referencia a la placa '.$request->input('Placa');
            $NuevoHistorial->codigo = $request->input('Placa');
            $NuevoHistorial->save();
        }

        if ($opcion == 1){
            DB::table('entrada_motocicletas')->where('id', $request->input('IdMoto'))
                ->update(['estado_placa'=>2]);


            $NuevoHistorial = new HistorialUsuario();
            foreach ($usuarios as $usuario){
                $NuevoHistorial->id_usuario = $request->input('Usuario');
                $NuevoHistorial->usuario = $usuario->usuario;
                $NuevoHistorial->descripcion = 'Registro Placa con numero '.$request->input('Placa');
                $NuevoHistorial->codigo = $request->input('Placa');
                $NuevoHistorial->save();
            }
        }



        if ($request->hasFile('FilePlaca')){
            foreach ($request->file('FilePlaca') as $key => $value){
                $nuevaFotoPlaca = new FotosPlaca();
                $foto = $fotosPlaca[$key];
                $nombreFP = time().'-'.rand(1,100).'-'.$foto->getClientOriginalName();
                $nuevaFotoPlaca->nombre = $nombreFP;
                $nuevaFotoPlaca->id_placa = $nueva_placa->id;
                $nuevaFotoPlaca->save();
                $foto->move(public_path().'/Placas/FotosPlacas', $nombreFP);
            }
        }

        if ($request->hasFile('FileBoleta')){
            foreach ($request->file('FileBoleta') as $key => $value){
                $nuevaFotoBoleta = new FotosBoleta();
                $fotoB = $fotosBoleta[$key];
                $nombreFB = time().'-'.rand(1,100).'-'.$fotoB->getClientOriginalName();
                $nuevaFotoBoleta->nombre = $nombreFB;
                $nuevaFotoBoleta->id_placa = $nueva_placa->id;
                $nuevaFotoBoleta->save();
                $fotoB->move(public_path().'/Placas/FotosBoletas', $nombreFB);
            }
        }

        return redirect()->route('placas.ingreso')->with('status','Se ha Guardado Correctamente');
    }

    public function inventario(){
        $placas = Placa::join('salidas','salidas.id','=','placas.venta_id')
            ->join('clientes','clientes.id','=','salidas.cliente_id')
            ->join('sucursals','sucursals.id','=','salidas.sucrusal_id')
            ->join('entrada_motocicletas','entrada_motocicletas.id','=','placas.id_moto')
            ->join('modelos','modelos.id','=','entrada_motocicletas.modelo_id')
            ->select('placas.num_boleta','placas.num_placa','entrada_motocicletas.chasis','modelos.nombre_mod',
                'clientes.nombres','clientes.apellidos','clientes.rtn','salidas.cod_venta','placas.estado_enlazo','salidas.id',
                'placas.estado')
            ->get();
        return view('Placas.inventario', compact('placas'));
    }

    public function info($id){
        $informacion = Placa::join('salidas','salidas.id','=','placas.venta_id')
            ->join('entrada_motocicletas','entrada_motocicletas.id','=','salidas.moto_id')
            ->join('modelos','modelos.id','=','entrada_motocicletas.modelo_id')
            ->join('marcas','marcas.id','=','modelos.marca_id')
            ->join('proveedors','proveedors.id','=','marcas.proveedor_id')
            ->join('tipo_vehiculos','tipo_vehiculos.id','=','modelos.tipovehiculo_id')
            ->join('tipo_ventas','tipo_ventas.id','=','salidas.tipoventa_id')
            ->join('clientes','clientes.id','=','salidas.cliente_id')
            ->join('colaboradors','colaboradors.id','=','salidas.colaborador_id')
            ->join('sucursals','sucursals.id','=','placas.sucursal_final')
            ->select('entrada_motocicletas.id_moto','entrada_motocicletas.chasis','entrada_motocicletas.motor','entrada_motocicletas.color',
                'modelos.nombre_mod','marcas.nombre as nombre_mar','proveedors.nombre as nombre_pro','tipo_vehiculos.nombre_v',
                'tipo_vehiculos.ruedas','entrada_motocicletas.estado_placa','tipo_ventas.nombre as nombre_ven','clientes.nombres',
                'clientes.apellidos','salidas.fecha_salida','colaboradors.nombre as nombre_col','salidas.cod_venta',
                'placas.num_boleta','placas.num_placa','placas.propietario','placas.identificacion','placas.estado_enlazo',
                'sucursals.nombre as nombre_alm','placas.fecha_vencimiento')
            ->where('placas.num_boleta', $id)
            ->get();


        return $informacion;
    }

    public function boleta($codigo){
        $boleta = Placa::join('sucursals','sucursals.id','=','placas.almacen_entrada')
            ->join('salidas','salidas.id','=','placas.venta_id')
            ->join('users','users.id','=','placas.usuario_registrador')
            ->select('placas.num_ingreso','placas.num_boleta','placas.comprobante','placas.fecha_vencimiento',
                'placas.num_placa','placas.identificacion','sucursals.nombre as nombre_alm','placas.propietario',
                'placas.ano','placas.estado','placas.estado_enlazo','placas.observaciones','salidas.cod_venta','users.usuario')
            ->where('placas.num_boleta', $codigo)
            ->get();
        $cliente = Placa::join('salidas','salidas.id','=','placas.venta_id')
            ->join('clientes','clientes.id','=','salidas.cliente_id')
            ->join('sucursals','sucursals.id','=','placas.sucursal_final')
            ->select('clientes.nombres','clientes.apellidos','clientes.rtn','clientes.identidad', 'salidas.sucrusal_id',
                'sucursals.nombre as nombre_suc', 'sucursals.id as id_suc')
            ->where('placas.num_boleta', $codigo)->get();
        $vehiculo = Placa::join('salidas','salidas.id','=','placas.venta_id')
            ->join('entrada_motocicletas','entrada_motocicletas.id','=','salidas.moto_id')
            ->join('modelos','modelos.id','=','entrada_motocicletas.modelo_id')
            ->join('marcas','marcas.id','=','entrada_motocicletas.marca_id')
            ->join('sucursals','sucursals.id','=','salidas.sucrusal_id')
            ->join('proveedors','proveedors.id','=','marcas.proveedor_id')
            ->select('entrada_motocicletas.id_moto','entrada_motocicletas.chasis','entrada_motocicletas.motor','entrada_motocicletas.color',
                'modelos.nombre_mod','proveedors.nombre as nombre_pro','marcas.nombre as nombre_marca','modelos.cilindraje',
                'entrada_motocicletas.estado_placa','sucursals.nombre as nombre_suc')
            ->where('placas.num_boleta', $codigo)->get();
        $almacen = AlmacenAltualPlaca::join('placas','placas.id','=','almacen_altual_placas.placa_id')
            ->join('sucursals','sucursals.id','=','almacen_altual_placas.almacen_actual')
            ->select('sucursals.nombre','sucursals.id')
            ->where('placas.num_boleta', $codigo)->get();
        return view('Placas.FichaBoleta', compact('boleta','cliente', 'vehiculo','almacen'));
    }

    public function transferencia(){
        $almacenes = Sucursal::all();
        return view('Placas.FormularioTransferencia', compact('almacenes'));
    }

    public function sucursales(){
        $almacenes = Sucursal::all();

        return $almacenes;
    }

    public function cargarPlacas($idorigen, $iddestino){
        $boletas = Placa::join('entrada_motocicletas','entrada_motocicletas.id','=','placas.id_moto')
            ->join('salidas','salidas.id','=','placas.venta_id')
            ->join('clientes','clientes.id','=','salidas.cliente_id')
            ->select('placas.id','placas.num_boleta','placas.num_placa','entrada_motocicletas.chasis','placas.estado_enlazo',
                'clientes.nombres')
            ->where('placas.almacen_entrada', $idorigen)->where('placas.sucursal_final', $iddestino)->where('placas.estado', 1)
            ->get();
        return $boletas;
    }

    public function save_transferencia(Request $request){

    }

    public function ver_info($id){
        $info = Placa::join('salidas','salidas.id','=','placas.venta_id')
            ->join('clientes','clientes.id','=','salidas.cliente_id')
            ->join('entrada_motocicletas','entrada_motocicletas.id','=','placas.id_moto')
            ->join('modelos','modelos.id','=','entrada_motocicletas.modelo_id')
            ->join('marcas','marcas.id','=','entrada_motocicletas.marca_id')
            ->select('clientes.nombres','clientes.apellidos','clientes.rtn','entrada_motocicletas.chasis','entrada_motocicletas.motor',
                'entrada_motocicletas.color','marcas.nombre','modelos.nombre_mod')
            ->where('placas.id', $id)->get();

        return $info;
    }

}