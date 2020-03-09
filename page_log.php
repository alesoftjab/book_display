<?php
require_once 'src/libreria_local/setupfolder/db_param.php';
require_once 'src/libreria_local/setupfolder/sys_param.php';

$_c_ = 'books';

$nombre_controlador = $_c_;

require_once 'src/libreria_local/entorno_general.php';
require_once 'src/libreria_local/funciones_locales.php';

$_ent = new EntornoGeneral();//Inicializa el entorno e INTERPRETA lo que venga desde el cliente.

// Creamos el controlador
require_once "src/$nombre_controlador.ctrlr.php";

//Pasa el nombre del controlador a una frase con espacios
$nombre_controlador = str_replace("_"," ", $nombre_controlador);
//Convierte a maysculas la primera letra de cada palabra
$nombre_clase_controlador .= str_replace(" ","",ucwords(" " . $nombre_controlador)) . "Ctrlr";

$_controller = new $nombre_clase_controlador;

// ***************************************************************
// Obtenemos la accin a ejecutar
$_accion = $_REQUEST['_a_'];
$_ejecutar_accion = false;
switch ($_accion)
{
	case 'ra':
		$_ejecutar_accion = true;
		$nombre_metodo_accion = 'registrarAcceso';
	break;
	case 'rv':
		$_ejecutar_accion = true;
		$nombre_metodo_accion = 'registrarVenta';
	break;
	default:
		$_ejecutar_accion = false;
}

if($_ejecutar_accion == true)
{
	// Llama la accion
	$_controller->$nombre_metodo_accion();
}

?>
