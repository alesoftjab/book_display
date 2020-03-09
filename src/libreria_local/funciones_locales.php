<?php

function get_tiene_tool ($member,$controlador, $accion)
{ 
	global $conn;
	global $cfg;
	global $_ent;

	$member 			= $conn->quote($member);
	$controlador 	= $conn->quote($controlador);
	$accion 			= $conn->quote($accion);

	// Primero obtengo el id de funcionalidad del panel de calidad asignado
	$qrystr = "SELECT tool_cod FROM tool WHERE controlador = $controlador AND accion = $accion";
	$data = $conn->query($qrystr);
	$result = $data->fetch(PDO::FETCH_ASSOC);
	$tool_cod = $result['tool_cod'];
	
	// Despues verifico que el member tenga asignada esta funcionalidad
	$qrystr_t = "SELECT id_asociacion FROM asociaciones 
							WHERE id_entidad_poseida = '$tool_cod' AND id_entidad_propietaria = $member AND id_gran_tipo_asoc=1";
	$data_t = $conn->query($qrystr_t);
	$result_t = $data_t->fetchAll(PDO::FETCH_ASSOC);
	
	$cantidad = count($result_t);
	if($cantidad == 1)
		return true;
	else
		return false;
}



function actualizar_cotizacion($jornada,$moneda)
{
	global $conn;
	global $_remote_dbname; 
	
	if($moneda==16) //dolar
		$moneda_buscar=3;
	if($moneda==15) //euro
		$moneda_buscar=2;


	/* Busqueda del valor actual de cotizacion del  */
	$qrystr_cot_d="	SELECT * 
										FROM $_remote_dbname.cotizacion 
									WHERE fecha <=  '$jornada' AND id_moneda_origen='$moneda_buscar' AND id_moneda_referencia = '1' 
									ORDER BY fecha DESC, id_cotizacion DESC 
									LIMIT 1";
	
	$data_cot_d = $conn->query($qrystr_cot_d);
	$result_cot_d = $data_cot_d->fetch(PDO::FETCH_ASSOC);
	$cot_d = $result_cot_d['valor'];

	//actualizar la cotizacion en sistema panio
	$qrystr_u="	UPDATE elementos SET `valor_monetario` = '$cot_d' WHERE id_elemento ='$moneda'";
	$data_u = $conn->query($qrystr_u);

}

function encriptarID($idDesEncriptado,$frase='molnar1878')
{
	global $conn;
	global $cfg;
	global $_ent;
	
	/* Busqueda del valor actual de cotizacion del  */
	$qrystr_encrip="SELECT HEX(AES_ENCRYPT('$idDesEncriptado', '$frase')) AS encrip";
//~ echo"$qrystr_encrip";	
	$data_encrip = $conn->query($qrystr_encrip);
	$result_encrip = $data_encrip->fetch(PDO::FETCH_ASSOC);
	$encrip = $result_encrip['encrip'];

	return($encrip);
}

function desEncriptarID($idEncriptado,$frase='molnar1878')
{
	global $conn;
	global $cfg;
	global $_ent;
	
	/* Busqueda del valor actual de cotizacion del  */
	$qrystr_encrip="SELECT AES_DECRYPT(UNHEX('$idEncriptado'), '$frase') AS encrip";
	
	$data_encrip = $conn->query($qrystr_encrip);
	$result_encrip = $data_encrip->fetch(PDO::FETCH_ASSOC);
	$decrip = $result_encrip['encrip'];

	return($decrip);
}



?>
