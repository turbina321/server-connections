<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ConnectionsController extends Controller
{
    public function ejecutaPrecioMetal(Request $request){
        try {
            $json = json_encode($request->input());
            $ojso = json_decode($json, true);
            $tipo = $request->get('tipo');
            $sucursales = $ojso["data"];
            $bandera = 0;
            $data = array();
            $ok = true;
            $cadenaEjec = "";
            $texto = "";
            $miSucursal = "";
            $arregloSust = $sucursales;
            $contador = 0;
            foreach($sucursales as $sucursal){
                $precios = $sucursal["precio"];
                $conexion = $sucursal["conexion"];
                $bandera = 0;
                $ok = 1;
                $miSucursal = $sucursal["nombre"];

                unset($arregloSust[$contador]);
                foreach($precios as $precio){
                    $id_kilataje = $precio["id"];
                    $miprecio = $precio["precio"];
                    $ejecuta = $this->actualizar($conexion, $miprecio, $id_kilataje, $tipo);
                    if(!$ejecuta){
                        $bandera = 1;
                    }
                }
                if($bandera == 1){
                    $ok = 0;
                    $texto = "Error de conexión";
                }else{
                    $ok = 1;
                    $texto = "Aplicado";
                }
                
                $objeto = array(
                    "sucursal" => $sucursal["nombre"],                    
                    "ok" => $ok,
                    "texto" => $texto
                );
                array_push($data, $objeto);
                $contador ++;
            }
            return response()->json([
                'data' => $data,
                'texto' => $cadenaEjec,
                'ok' => 1
            ], 200);
        } catch (\Throwable $th) {
            $objeto2 = array(
                "sucursal" => $miSucursal,                    
                "ok" => 2,
                "texto" => "Error de conexión"
            );
            array_push($data, $objeto2);
            foreach($arregloSust as $sust){
                $objeto3 = array(
                    "sucursal" => $sust["nombre"],                    
                    "ok" => 3,
                    "texto" => "Pendiente"
                );
                array_push($data, $objeto3);
            }

            return response()->json([
                'mensaje' => $th->getMessage(). ' '.$th->getLine(),
                'pendientes' => $arregloSust,
                'data' => $data,
                'texto' => $cadenaEjec,
                'ok' => false
            ], 200);
        }
    }

    public function actualizar($conexion, $precio, $id_kilataje, $tipo){
        try {

            if($tipo == 1 || $tipo == 2){
                app('db')->connection($conexion)->update('update precioskilataje set Precio = ? 
                where IDKilataje = ? and IDHechura = ?', 
                [$precio, $id_kilataje, $tipo]);
                if($id_kilataje == 32){
                    app('db')->connection($conexion)->update('update precioskilataje set Precio = ? 
                    where IDKilataje = ? and IDHechura = ?', 
                    [100, 14, $tipo]);
                }
            }
            if($tipo == 3){
                app('db')->connection($conexion)->update('update precioskilatajeventa set Precio = ? 
                where IDKilataje = ? and IDHechura = 1', 
                [$precio, $id_kilataje]);
                app('db')->connection($conexion)->update('update precioskilatajeventa set Precio = ? 
                where IDKilataje = ? and IDHechura = 2', 
                [$precio, $id_kilataje]);

                app('db')->connection($conexion)->update('update precioskilatajeremate set Precio = ? 
                where IDKilataje = ? and IDHechura = 1', 
                [$precio, $id_kilataje]);
                app('db')->connection($conexion)->update('update precioskilatajeremate set Precio = ? 
                where IDKilataje = ? and IDHechura = 2', 
                [$precio, $id_kilataje]);

                app('db')->connection($conexion)->update('update precioskilatajefinanzas 
                set Precio = ? 
                where (IDKilataje = ? and IDHechura = 1) OR (IDKilataje = ? and IDHechura = 2)', 
                [$precio, $id_kilataje, $id_kilataje]);

            }     
                
            return true;
        } catch (Exception $th) {
            
            return false;
        } 
    }
    public function getSucursales(){
        try {
            $data = array(
                array(
                    "id" => 1,
                    "nombre" => "Mérida",
                    "conexion"=> "mysql",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 2,
                    "nombre" => "Umán",
                    "conexion"=> "mysql2",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 3,
                    "nombre" => "Progreso (exmaria)",
                    "conexion"=> "mysql3",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 4,
                    "nombre" => "Progreso (PM)",
                    "conexion"=> "mysql4",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 5,
                    "nombre" => "Motul (PM)",
                    "conexion"=> "mysql5",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 6,
                    "nombre" => "Izamal (exKeyla)",
                    "conexion"=> "mysql6",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 7,
                    "nombre" => "Izamal (Dando)",
                    "conexion"=> "mysql7",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 8,
                    "nombre" => "Tekax (Dando)",
                    "conexion"=> "mysql8",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 9,
                    "nombre" => "Tekax (exKeyla)",
                    "conexion"=> "mysql9",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 10,
                    "nombre" => "Ticul",
                    "conexion"=> "mysql10",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 11,
                    "nombre" => "Tizimin (PM)",
                    "conexion"=> "mysql11",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 12,
                    "nombre" => "Valladolid",
                    "conexion"=> "mysql12",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 13,
                    "nombre" => "Campeche (MC)",
                    "conexion"=> "mysql13",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 14,
                    "nombre" => "Campeche (SE)",
                    "conexion"=> "mysql14",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 15,
                    "nombre" => "Campeche (PM)",
                    "conexion"=> "mysql15",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 16,
                    "nombre" => "Hecelchakan",
                    "conexion"=> "mysql16",
                    "visible"=> true,
                    "primo"=> 1,
                ),
                array(
                    "id" => 17,
                    "nombre" => "Champotón",
                    "conexion"=> "mysql17",
                    "visible"=> true,
                    "primo"=> 1,
                )
            );        
            $kilatajes = array(
                array(
                    "id" => 33,
                    "kilataje" => "6 kilates",
                    "precio" => 0
                ),
                array(
                    "id" => 1,
                    "kilataje" => "Ro. 10 kilates",
                    "precio" => 0
                ),
                array(
                    "id" => 2,
                    "kilataje" => "Ama. 10 kilates",
                    "precio" => 0
                ),
                array(
                    "id" => 3,
                    "kilataje" => "12 kilates",
                    "precio" => 0
                ),
                array(
                    "id" => 22,
                    "kilataje" => "18 kilates",
                    "precio" => 0
                ),
                array(
                    "id" => 21,
                    "kilataje" => "21 kilates",
                    "precio" => 0
                ),
                array(
                    "id" => 32,
                    "kilataje" => "24 kilates",
                    "precio" => 0
                )
                );
            
            return response()->json([
                'data' => $data,
                'kilatajes' => $kilatajes,
                'ok' => true
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => 'Ha ocurrido un problema con el servidor',
                'ok' => false
            ], 500);        }
        
    }
}
