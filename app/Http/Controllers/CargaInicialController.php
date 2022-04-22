<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CargaInicialExport;
use DateTime;

class CargaInicialController extends Controller
{
    public function cargaInicial(Request $request){
        try {
            $json = json_encode($request->input());
            $ojso = json_decode($json, true);
            $planes = $ojso["listaPlanes"];
            $codigo_sucursal = $request->get('codigo_sucursal');
            $codigo_cliente_general = $request->get('codigo_cliente_general');
            $plan = $request->get('plan');
            $miEstatus = '';
            $now = new DateTime("now");
            $fecha_enajenado = '';
            $conexion = $request->get('codigo_cliente_general');
            // Kilataje
            // tipo de identificación
            // Id identificacion tiene que ser numerico
            // 
            $data = app('db')->connection('mysql18')
            ->select("
            select
                    '1' as 'CodigoClienteGeneral','1' as CodigoSucursal, E.IDCliente as CodigoCliente, C.Nombre,C.ApellidoPaterno,C.ApellidoMaterno, IF(C.Sexo=2,'F',if(C.sexo=1,'M','notiene')) as Sexo, C.FecNac as FechaNacimiento,
                    '' as CUPR, C.Identificacion as TipoIdentificacion, C.NumeroIdentificacion as NumeroIdentificacion,'' as Telefono, C.Tel as TelefonoMovil,concat(C.Direccion,' ',C.NoExterior) as Calle,C.CP as CodigoPostal, 
                    C.Colonia,'' as Beneficiario, '' as Cotitular,'1' as Actividad,'' as Correo, '' as Pais, '' as Estado, '' as Municipio, '' as Ciudad, '' as Observaciones, E.NumContrato as FolioContratoSistemaAnterior,
                    '1' as CodigoTasaIva,E.tasa*4 as TasaInteres, '0' as TasaGastosAdministracion,E.Almacenaje*4 as TasaAlmacenaje, E.Operacion as TasaInteresesMoratorios, E.Periodo*E.VenPeriodo as Periodo,
                    E.Prestamo as saldoPrestamo,E.NumRefrendos as NumeroRefrendos,EPI.FechaRegistroOriginal,date_format( E.Fecha,'%Y-%m-%d') as FechaUltimoMovimiento, if(E.Vencimiento>='2021/12/15','V','R') as Estatus,C.FechaAlta as FechaAltaCliente, curdate() as FechaCorteMigracion,
                    C.Boletas as CantidadContratosHistorial, teb.ImporteContratosHistorial,Tab.ImporteContratosEnajenados,'' as CodigoPlan,'' as FolioResguardo,'' as FolioControl,'0' as ContratoVehiculo,
                    '0' as MontoCargo, ct.RazonSocial,be.RazonSocial,'0' as NumeroPlazosExtra,'0' as AbonosInteresesForzosoPlazosExtra,'1' as CodigoArticulo,concat(convert(e.numcontrato,char),' ',DE.Articulo,' ',DE.Observaciones) as descripcion,
                    DE.Prestamo as ImportePrestamoxArticulo,K.Descripcion as Kilataje, DE.Peso+DE.PesoPiedras as Peso,DE.PesoPiedras as PesodeAplique,DE.CantidadPiedras as NumerodeApliques,'' as NumeroSerie,
                    '1' as TasaValorMercado, '5' as CodigoCalidad, '' as FechaEnajenacion, DE.Avaluo as ImporteAvaluoxArticulo, '0' as ImporteSobrePrestamo, E.Cat,E.Vencimiento,E.DiasCortesia

                    from basedatos.empeno  as E
                    inner join basedatos.detallesempeno as DE on DE.IDEmpeno=E.ID
                    Inner join basedatos.clientes as C on C.id=E.IDCliente
                    inner join basedatos.kilatajes as K on K.Clave=DE.Kilates
                    left join basedatos.clientes as ct on ct.id=E.IDCoTitular
                    left join basedatos.clientes as be on be.id=E.IDBeneficiario
                    left join (select IDCliente, sum(Prestamo) as ImporteContratosEnajenados from basedatos.empeno
                    where Destino=4 group by IDCliente) as tab on tab.IDcliente=C.ID 
                    left join (select IDCliente, sum(PrestamoInicial) as ImporteContratosHistorial from basedatos.empeno
                    where  (origen=1 and cancelado=0) group by IDCliente ) teb on teb.IDcliente=C.ID 
                    left join (select NumContrato, date_format(fecha,'%d/%m/%Y') as FechaRegistroOriginal from basedatos.empeno
                    where  (origen=1 and cancelado=0) group by NumContrato ) EPI on EPI.numcontrato=E.NumContrato 


                    where (E.Destino=0 and E.Cancelado=0)
                    limit 200
            ", 
            []);  

            // print_r( $now[0]->date );
            $bandera = 0;
            $arrPlanes = array(
                array(
                    "id" => 0,
                    "interes" => 0,
                    "almacenaje" => 0,
                    "moratorios" => 0,
                    "periodo" => 0
                )
            );
            $miKila = 0;
            $miIdentificador = 0;

            $fecha_actual = date("d-m-Y");
            foreach($data as $item){
                // TODO:  decimales
                switch ($item->TipoIdentificacion) {
                    case 'CÉDULA PROFESIONAL':
                        $miIdentificador = 2;
                        
                        break;
                    case 'CREDENCIAL PARA VOTAR':
                        $miIdentificador = 1;
                        
                        break;                    
                    case 'CREDENCIAL PARA VOTAR IFE':
                        $miIdentificador = 1;
                        
                        break;
                    case 'CREDENCIAL PARA VOTAR INE':
                        $miIdentificador = 1;
                        
                        break;
                    case 'LICENCIA PARA CONDUCIR':
                        $miIdentificador = 5;
                        
                        break;
                    
                    default:
                        $miIdentificador = $item->TipoIdentificacion;
                        break;
                }
                $item->TipoIdentificacion = $miIdentificador;
                $item->NumeroIdentificacion = (int) filter_var($item->NumeroIdentificacion, FILTER_SANITIZE_NUMBER_INT);  
                $item->TasaValorMercado = 1.3;
                switch ($item->Kilataje) {
                    case 'OROBAJOC':
                        $miKila = 1;
                        
                        break;
                    case '10KROJO':
                        $miKila = 4;
                        
                        break;                    
                    case '10KAMA':
                        $miKila = 5;
                        
                        break;
                    case 'BAJO6':
                        $miKila = 2;
                        
                        break;
                    case '14K':
                        $miKila = 6;
                        
                        break;
                    case '18K':
                        $miKila = 7;
                        
                        break;
                    case '21K':
                        $miKila = 8;
                        
                        break;
                    case '24K':
                        $miKila = 9;
                        
                        break;     
                    default:
                        $miKila = 0;
                        break;
                }
                $item->Kilataje = $miKila;
                $item->CodigoSucursal = $codigo_sucursal;
                $item->CodigoClienteGeneral = $codigo_cliente_general;

                $item->TasaInteres = number_format($item->TasaInteres, 4, '.', '');
                $item->TasaInteresesMoratorios = number_format($item->TasaInteresesMoratorios, 4, '.', '');
                $item->TasaAlmacenaje = number_format($item->TasaAlmacenaje, 4, '.', '');
                

                $fecha = date('Y-m-d H:i:s',strtotime($item->FechaUltimoMovimiento));
                $mod_date2 = strtotime("+".$item->Periodo." day", strtotime($fecha));
                $now2 = new DateTime(date("d-m-Y H:i:s",$mod_date2));

                if($now2  >= $now){
                    $miEstatus = 'V';
                    $fecha_enajenado = date('d/m/Y', strtotime('01-01-1900'));
                }else{
                    $miEstatus = 'R';
                    $fecha_enajenado = strtotime("+".($item->Periodo + 31)." day", strtotime($fecha));
                    $fecha_enajenado = date('d/m/Y', $fecha_enajenado);

                }
                $item->FechaEnajenacion = $fecha_enajenado;
                // $item->Estatus = $miEstatus;
                //TODO: obtener fechas
                //$item->Estatus = 'R';
                //$item->hola = date("d/m/Y",strtotime($fecha_actual."- 152 days"));
                $bandera = 0;
                
                if($plan == 1){
                    // Agregar planes
                    foreach($planes as $obj){
                        if($item->TasaInteresesMoratorios == $obj["moratorios"] and 
                            $item->Periodo == $obj["periodo"] and
                            $item->TasaInteres == $obj["interes"]and 
                            $item->TasaAlmacenaje == $obj["almacenaje"]){
                                $item->CodigoPlan = $obj["id"];
                        }
                    }
                }else{
                    // Obtener planes
                    foreach($arrPlanes as $plan){
                        if($item->TasaInteresesMoratorios == $plan["moratorios"] and 
                            $item->Periodo == $plan["periodo"] and
                            $item->TasaInteres == $plan["interes"]and 
                            $item->TasaAlmacenaje == $plan["almacenaje"]){
                                $bandera = 1;
                        }
                    }
                    if($bandera == 0){
                        $nuevoArr = array(
                            "id" => 0,
                            "interes" => $item->TasaInteres,
                            "almacenaje" => $item->TasaAlmacenaje,
                            "moratorios" => $item->TasaInteresesMoratorios,
                            "periodo" => $item->Periodo
                        );
                        array_push($arrPlanes, $nuevoArr);
                    }
                }
                $item->FechaNacimiento = date('d/m/Y',strtotime($item->FechaNacimiento));
                $item->FechaUltimoMovimiento = date('d/m/Y',strtotime($item->FechaUltimoMovimiento));
                $item->FechaAltaCliente = date('d/m/Y',strtotime($item->FechaAltaCliente));
                $item->FechaCorteMigracion = date('d/m/Y',strtotime($item->FechaCorteMigracion));
                $item->Vencimiento = date('d/m/Y',strtotime($item->Vencimiento));

            }
            if($plan == 1){
                return Excel::download(new CargaInicialExport($data), 'CargaInicial.xlsx');
            }else{
                return response()->json([
                    'planes' => $arrPlanes,
                    'data' => $data,
                    'ok' => true
                ], 500); 
            }
            
        } catch (\Throwable $th) {
            print_r($th->getMessage().' '. $th->getLine());
            return false;
        } 
    }

}
