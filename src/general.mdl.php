<?php

class GeneralMdl
{
	/* 
	
	*/

	public function getDatosId($tabla, $campo_clave, $id)
	{
		global $conn;
		
		//if($id>0)
			$where = " WHERE $campo_clave=$id";

		$qrystr = "	SELECT * FROM $tabla $where";		
										 
		try 
		{
			$data = $conn->query($qrystr);
			$filas = $data->fetchAll(PDO::FETCH_ASSOC);

			$arr_ret = array("estado" => true, "descripcion"=>'', 'datos' => $filas);	
			
			return $arr_ret;
			
		} 
		catch(PDOException $e) 
		{
			//~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			$arr_ret = array("estado" => false, "descripcion"=>'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr", 'datos' => array());	
		}										 
		return($arr_ret);
	}


	public function getDatosUsuario($arrFiltrosObligatorios=array(), $arrFiltrosOpcionales=array())
	{
		global $conn;
		
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltros = array_merge($arrFiltrosObligatorios, $arrFiltrosOpcionales);
		// *********** FIN Rellenado de parámetros obligatorios ****************** //

		// ************** Parámetros obligatorios ***************** //
		$cod_member = $conn->quote($arrFiltros['cod_member']);
		// ************ FIN Parámetros obligatorios *************** //

		// ************** Parámetros opcionales ***************** //
		// ************ FIN Parámetros opcionales **************** //
		

		$qrystr = "	SELECT m.cod_member, 
								IF( me.id_usuario IS NULL , m.apellido, CONCAT( me.apellido, ', ', me.nombres ) ) AS nombre, 
								if( m.cod_member = m.escalafon3, 'Institución', IF( m.cod_member = m.escalafon2, 'Dependencia', IF( m.cod_member = m.escalafon1, 'Servicio', 'Usuario' ) ) ) AS escal, 
								CONCAT( '(', u.abreviatura, ') - ', u.abreviatura_texto ) AS ubicacion, 
								m1.apellido AS servicio, m2.apellido AS dependencia, m3.apellido AS institucion,
								me.nro_doc, m.direccion, m.localidad, m.provincia, m.cp
									FROM member AS m
									INNER JOIN member AS m1 ON m.escalafon1 = m1.cod_member
									INNER JOIN member AS m2 ON m.escalafon2 = m2.cod_member
									INNER JOIN member AS m3 ON m.escalafon3 = m3.cod_member
									LEFT JOIN ubicaciones_dosimetros AS u ON m.ubicacion_dosimetro = u.id_ubicacion
									LEFT JOIN member_ext AS me ON m.id_persona = me.id_usuario
								WHERE m.cod_member = $cod_member ";		
										 
		try 
		{
			$data = $conn->query($qrystr);
			$row_member = $data->fetch(PDO::FETCH_ASSOC);

			$arr_ret = array("estado" => true, "descripcion"=>'', 'datos' => $row_member);	
			
			return $arr_ret;
			
		} 
		catch(PDOException $e) 
		{
			//~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			$arr_ret = array("estado" => false, "descripcion"=>'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr", 'datos' => array());	
		}										 
		return($arr_ret);
	}

	// ************************ getTools ******************************* //
	/*
	*/
	public function getTools()
	{
		global $conn;
		global $_ent;
	  $arr_ret = array("estado" => true, "descripcion"=>'');
		try
		{
			//Obtener las herramientas que tiene asociadas el operario
			$qrystr = " SELECT *
									from asociaciones
									WHERE id_gran_tipo_asoc=1 
										and id_entidad_propietaria = '$_ent->member'
									ORDER BY contador_accesos DESC";

			$data = $conn->query($qrystr);
			
			$tools_usuario = array();
			while($row_tool_member = $data->fetch(PDO::FETCH_ASSOC))
				$tools_usuario[] = $row_tool_member["id_entidad_poseida"];

			//Iterar para todas las herramientas que existan
			$qrystr = " SELECT t.*, tt.descripcion_tipo_tool, tt.icono_tipo
									FROM `tool` AS t
									INNER JOIN tool_tipo AS tt ON t.tipo_tool=tt.tipo_tool_id
									WHERE t.estado_activo=1
									ORDER BY t.tipo_tool, t.tool_desc";
			$data = $conn->query($qrystr);

			$arr_tools = array();
			while($row_tool = $data->fetch(PDO::FETCH_ASSOC))
			{
				if(in_array($row_tool['tool_cod'], $tools_usuario))
				  $arr_tools[] = $row_tool;
			}
						
			return($arr_tools);
		} catch(PDOException $e)
		{
			echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
		}

		return($arr_ret);
	}
}
?>
