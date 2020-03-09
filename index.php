<?php
require_once 'src/libreria_local/setupfolder/db_param.php';
require_once 'src/libreria_local/setupfolder/sys_param.php';

// Obtenemos el controlador que queremos cargar
if(!isset($_REQUEST['_c_']))
	$_c_ = 'pantalla_login';
else
	$_c_ = $_REQUEST['_c_'];

$nombre_controlador = $_c_;

if($nombre_controlador == 'pantalla_login')
{
	require_once "login.php";  //Pantalla con usuario, password y m�todo de ingreso
}
else
{

	require_once 'src/libreria_local/entorno_general.php';
	require_once 'src/libreria_local/gestor_log_cambio.php';

	$_ent = new EntornoGeneral();//Inicializa el entorno e INTERPRETA lo que venga desde el cliente.

	switch($_ent->estado)
	{
		case 'login': //Link a index pero ya con el nuevo token.
			header("location:" . $_ent->envLink('index.php?_c_=' . $_c_));
		break;

		case 'cambio_representado': //Link a donde fuera pero ya con el nuevo token que contiene a un representado.
			header("location:" . $_ent->envLink('index.php?_c_=' . $_c_));
		break;

		case 'autentificado': //Construir frameset.
			require_once "src/libreria_local/funciones_locales.php";
			// Creamos el controlador
			require_once "src/$nombre_controlador.ctrlr.php";

			//Pasa el nombre del controlador a una frase con espacios
			$nombre_controlador = str_replace("_"," ", $nombre_controlador);
			//Convierte a may�sculas la primera letra de cada palabra
			$nombre_clase_controlador .= str_replace(" ","",ucwords(" " . $nombre_controlador)) . "Ctrlr";

			$_controller = new $nombre_clase_controlador;

			// ***************************************************************
			// Obtenemos la acci�n a ejecutar
			$_accion = isset($_REQUEST['_a_']) ? $_REQUEST['_a_'] : 'Index';

			//Pasa el nombre de accion a una frase con espacios
			$nombre_accion = str_replace("_"," ", $_accion);
			//Convierte a may�sculas la primera letra de cada palabra
			$nombre_metodo_accion .= str_replace(" ","",ucwords(" " . $nombre_accion));

			// Llama la accion
			$_controller->$nombre_metodo_accion();
		break;

		case 'no_autentificado':// Volver al index con un error.
			header("location:index.php?error=1");
		break;

		case 'logout': // Volver al index.
			//finalizar la sesion
			$_ent->caducarPosta();
			header("location:index.php");
		break;
	}

}

?>
