<?php
require_once 'src/libreria_local/grilla_datatables.mdl.php';

class Asociaciones extends GrillaDataTables
{
	/* 
	Construir la consulta con o sin los filtros en pantalla para que puede ser ejecutada.
	* 
	* Parámetros
	* arrFiltros: es un array de parámetros
	* 
	* Retorno: void 
	*/
	public function construirConsulta($arrFiltros)
	{
		global $conn;
		global $cfg;
		global $_ent;
		
		$filtro_no_mostrar = '';
		// ************** Parámetros obligatorios ***************** //
		$id_gran_tipo_asoc = $conn->quote($arrFiltros['id_gran_tipo_asoc']);
		//Le volvemos a sacar las comillas
		$id_gran_tipo_asoc = str_replace("'","", $id_gran_tipo_asoc );		
		$id_entidad_propietaria = $conn->quote($arrFiltros['id_entidad_propietaria']);
		//Le volvemos a sacar las comillas
		$id_entidad_propietaria = str_replace("'","", $id_entidad_propietaria );
		$tipo_vista = $conn->quote($arrFiltros['tipo_vista']);
		//Le volvemos a sacar las comillas
		$tipo_vista = str_replace("'","", $tipo_vista );		
	
		$str_no_mostrar = "";
		if((1*$id_gran_tipo_asoc==0) OR ($id_entidad_propietaria=='') OR ($tipo_vista==''))
			$str_no_mostrar = " AND 1=0 ";
		// ************ FIN Parámetros obligatorios *************** //

		// ************** Parámetros opcionales ***************** //
		if(isset($arrFiltros['filtro_txt']))
		{
			$filtro_txt=strtr($arrFiltros['filtro_txt']," ","%");
			$filtro_txt = $conn->quote($filtro_txt);
			//Le volvemos a sacar las comillas
			$filtro_txt = str_replace("'","", $filtro_txt );
		}
		 
		if(isset($arrFiltros['id_entidad_poseida']))
		{ 
			$id_entidad_poseida = $conn->quote($arrFiltros['id_entidad_poseida']);
			//Le volvemos a sacar las comillas
			$id_entidad_poseida = str_replace("'","", $id_entidad_poseida );
		}
		// ************ FIN Parámetros opcionales **************** //
		
		// ************** Paginación ***************** //
		$_paginacion_ = "";
		if(isset($arrFiltros['_start_']) AND isset($arrFiltros['_length_']))
			$_paginacion_ =" LIMIT ".$arrFiltros['_start_']." ,".$arrFiltros['_length_']."  "; 
		
		// ************ FIN Paginación *************** //

		$qrystr_tipo="SELECT tipo_entidad_pos FROM asoc_gran_tipo WHERE id_gran_tipo_asoc=$id_gran_tipo_asoc";
		
		$data_tipo = $conn->query($qrystr_tipo);
		$row_tipo = $data_tipo->fetch(PDO::FETCH_ASSOC);

		switch($row_tipo['tipo_entidad_pos'])
		{
		case 2:  //Herramienta

			$str_filtro_unico = '';
			if($id_entidad_poseida <> '')
			  $str_filtro_unico = " AND f.tool_cod='$id_entidad_poseida' ";

		  $str_filtro_txt = "";
		  if($filtro_txt<>'')
				$str_filtro_txt = " AND ((f.tool_desc LIKE('%$filtro_txt%')) OR (ft.descripcion_tipo_tool LIKE('%$filtro_txt%'))) ";
	  
			$join_mostrar = " LEFT JOIN ";

			if($tipo_vista=='A')
				$join_mostrar = " INNER JOIN ";

			$this->qrystr = " SELECT f.tool_cod AS id,f.tool_desc AS paq, IFNULL(a.id_entidad_poseida,'') AS estado,ft.descripcion_tipo_tool AS tipo_desc, a.id_gran_tipo_asoc, a.id_entidad_poseida, a.id_entidad_propietaria
									FROM tool AS f               
										INNER JOIN tool_tipo AS ft ON ft.tipo_tool_id = f.tipo_tool  
										$join_mostrar `asociaciones` AS a ON f.tool_cod = a.id_entidad_poseida
										AND a.id_gran_tipo_asoc =$id_gran_tipo_asoc
										AND a.id_entidad_propietaria = '$id_entidad_propietaria'
										
									WHERE 1
										$str_filtro_unico
										$str_filtro_txt
										$str_no_mostrar
									ORDER BY ft.descripcion_tipo_tool,f.tool_desc
										 ";
		break;
		}
		//~ echo"this->qrystr: $this->qrystr<br>";	 
	}
	
	
	public function getFilasParaDataTables($arrFiltrosObligatorios=array(), $arrFiltrosOpcionales=array(), $arrPaginacion=array())
	{

		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltros = $arrFiltrosObligatorios;
		// *********** FIN Rellenado de parámetros obligatorios ****************** //

		//Llamar al modelo para obtener la cantidad de filas datos SIN los filtros que vinieron como parámetros desde el forulario
		$totalData = $this->getNumeroFilas($arrFiltros);
		
		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltros = array_merge($arrFiltros, $arrFiltrosOpcionales);
		// *********** FIN Rellenado de parámetros opcionales ****************** //
		
		//Llamar al modelo para obtener la cantidad de filas CON los filtros que vinieron como parámetros desde el forulario
		$totalFiltered = $this->getNumeroFilas($arrFiltros);
		
		// *********** PAGINACIÓN ****************** //
		if(1*$arrPaginacion['length'] <> 0)
		{
			$arrFiltros['_start_'] = $arrPaginacion['start'];
			$arrFiltros['_length_'] = $arrPaginacion['length'];
		}
		// ********* FIN PAGINACIÓN **************** //
		
		//Llamar al modelo para obtener los datos CON los filtros que vinieron como parámetros desde el forulario
		$arr_ret = array('rows' => $this->getFilas($arrFiltros),
										 'totalData' => $totalData,
										 'totalFiltered' => $totalFiltered,
										);
		return($arr_ret);

	}
	
	// ************************ getDatosParaForm ******************************* //
	/*
	Este método devuelve un array de datos que se usarán en formularios
	*/ 
	public function getDatosParaForm($par_id_cliente)
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	
		try 
		{
			$id_cliente = $conn->quote($par_id_cliente);
			$qrystr = "SELECT * 
									FROM Asociaciones 
									WHERE id_cliente=$id_cliente";
			$data = $conn->query($qrystr);
			$result = $data->fetch(PDO::FETCH_ASSOC);
//~ var_dump($result);			
			return($result);
		} catch(PDOException $e) 
		{
			echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
		}

		return($arr_ret);
	}

	// ************************ getTiposAsociaciones ******************************* //
	/*
	Este método devuelve un array de datos que se usarán en formularios
	*/ 
	public function getTiposAsociaciones()
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');
		try
		{
			# Solo los tipos de bancas
			$qrystr = " SELECT *
									FROM asoc_gran_tipo
										";
			// echo $qrystr;
			$data = $conn->query($qrystr);
			$result = $data->fetchAll(PDO::FETCH_ASSOC);
			// var_dump($result);
			// var_export($result);
			return($result);
		} catch(PDOException $e)
		{
			echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
		}

		return($arr_ret);
	}

	
	/* getListaPropietarios retorna un conjunto de resultados de productos para suggest
	*/
	public function getListaPropietarios($arrFiltros=array(), $arrPaginacion=array())
	{

		global $conn;
		// ************** Parámetros  ***************** //
		if(isset($arrFiltros['query']))
		{ 
			$valor = $conn->quote($arrFiltros['query']);
			//Le volvemos a sacar las comillas
			$valor = str_replace("'","", $valor );
		}   

//~ var_dump($arrFiltros);

		// ************ FIN Parámetros  **************** //
		
		// ************** Paginación ***************** //
		$_paginacion_ =" LIMIT ".$arrPaginacion['start']." ,".$arrPaginacion['length']."  "; 
		// ************ FIN Paginación *************** //

		if($arrFiltros['id_gran_tipo_asoc']*1 <> 0)
		{ 
			$id_gran_tipo_asoc = $arrFiltros['id_gran_tipo_asoc'];
			$id_gran_tipo_asoc = $conn->quote($id_gran_tipo_asoc);
			$qrystr_tipo="SELECT tipo_entidad_prop FROM asoc_gran_tipo WHERE id_gran_tipo_asoc=$id_gran_tipo_asoc";
			
			$data_tipo = $conn->query($qrystr_tipo);
			$row_tipo = $data_tipo->fetch(PDO::FETCH_ASSOC);

			switch($row_tipo['tipo_entidad_prop'])
			{
			case 1://///       MIEMBRO
				$select="SELECT u.cod_member as id,concat(u.apellido,' ',u.nombres,' (',u.cod_member,')') as paq";
				$tabla="FROM member AS u";
				$where="WHERE (cod_member like ('%".$valor."%') OR apellido like ('%".$valor."%') OR nombres like ('%".$valor."%') or nro_doc like ('%".$valor."%'))";
				$order="ORDER by u.cod_member";
				$limit="$_paginacion_";
			break;
			
			case 7://////     PROFILES
				$select=" SELECT id_profile as id,nombre as paq ";
				$tabla=" FROM profiles ";
				$where=" WHERE (nombre like ('%".$valor."%') OR id_profile like ('%".$valor."%')) ";
				$order=" ORDER by nombre ";
				$limit=" $_paginacion_ ";

			break; 
			case 9://////     AMBITO
				$select="SELECT amb.id_area_trabajo as id,amb.nombre_area_trabajo as paq ";
				$tabla=" FROM tbl_ambito AS amb ";
				$where=" WHERE (amb.nombre_area_trabajo like ('%".$valor."%') OR amb.id_area_trabajo like ('%".$valor."%')) ";
				$order=" ORDER by amb.id_area_trabajo ";
				$limit=" $_paginacion_";		  
			break; 	     
			}  	  
			$qrystr = " $select
												$tabla
												$where
												$order
												$limit
											 ";		
//~ echo"this->qrystr: $this->qrystr<br>";		
			// ******** EJECUTAR LA CONSULTA ************ //
			try 
			{
				$data = $conn->query($qrystr);
				return($data->fetchAll(PDO::FETCH_ASSOC));
			} 
			catch(PDOException $e) 
			{
				echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$this->qrystr";
			}
	  }
		else
		{
			echo 'Error - No se especificó id_gran_tipo_asoc';
		}
	}

	// ************************ dar ******************************* //
	public function dar($id_gran_tipo_asoc, $id_entidad_propietaria, $id_entidad_poseida)
	{
		global $conn;
		global $_ent;
		$arr_ret = array("estado" => true, "descripcion"=>'');

		try
		{
			$id_gran_tipo_asoc 	= $conn->quote(utf8_decode($id_gran_tipo_asoc));
			//Le volvemos a sacar las comillas
			$id_gran_tipo_asoc = str_replace("'","", $id_gran_tipo_asoc );
			$id_entidad_poseida = $conn->quote($id_entidad_poseida);
			//Le volvemos a sacar las comillas
			$id_entidad_poseida = str_replace("'","", $id_entidad_poseida );
			$id_entidad_propietaria = $conn->quote($id_entidad_propietaria);
			//Le volvemos a sacar las comillas
			$id_entidad_propietaria = str_replace("'","", $id_entidad_propietaria );

			if((1*$id_gran_tipo_asoc == 0) OR ($id_entidad_poseida == '') OR ($id_entidad_propietaria == ''))
				$arr_ret = array("estado" => false, "descripcion"=>"FALTA UNO O MÁS PARÁMETROS OBLIGATORIOS: id_gran_tipo_asoc: $id_gran_tipo_asoc - id_entidad_poseida: $id_entidad_poseida - id_entidad_propietaria: $id_entidad_propietaria");
			else
			{
				$qrystr_d = "insert INTO asociaciones (id_gran_tipo_asoc,id_entidad_poseida,id_entidad_propietaria) values ('$id_gran_tipo_asoc','$id_entidad_poseida','$id_entidad_propietaria') ";
				//echo $qrystr_d;
				$data = $conn->query($qrystr_d);

				if($id_gran_tipo_asoc==7 or $id_gran_tipo_asoc==9)  //7	Herramienta por Perfil - 9	Verbo por Perfil
				{
					$this->asociar_x_perfil($id_entidad_propietaria,'');
				}

				$arr_ret['descripcion'] = "$count registro(s) afectado(s)";
			}
		} catch(PDOException $e)
		{
			$arr_ret = array("estado" => false, "descripcion"=>$e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}
	
	// ************************ quitar ******************************* //
	public function quitar($id_gran_tipo_asoc, $id_entidad_propietaria, $id_entidad_poseida)
	{
		global $conn;
		global $_ent;
		$arr_ret = array("estado" => true, "descripcion"=>'');

		try
		{
			$id_gran_tipo_asoc 	= $conn->quote(utf8_decode($id_gran_tipo_asoc));
			//Le volvemos a sacar las comillas
			$id_gran_tipo_asoc = str_replace("'","", $id_gran_tipo_asoc );
			$id_entidad_poseida = $conn->quote($id_entidad_poseida);
			//Le volvemos a sacar las comillas
			$id_entidad_poseida = str_replace("'","", $id_entidad_poseida );
			$id_entidad_propietaria = $conn->quote($id_entidad_propietaria);
			//Le volvemos a sacar las comillas
			$id_entidad_propietaria = str_replace("'","", $id_entidad_propietaria );

			if((1*$id_gran_tipo_asoc == 0) OR ($id_entidad_poseida == '') OR ($id_entidad_propietaria == ''))
				$arr_ret = array("estado" => false, "descripcion"=>"FALTA UNO O MÁS PARÁMETROS OBLIGATORIOS: id_gran_tipo_asoc: $id_gran_tipo_asoc - id_entidad_poseida: $id_entidad_poseida - id_entidad_propietaria: $id_entidad_propietaria");
			else
			{
				$qrystr_d = "DELETE FROM asociaciones WHERE id_gran_tipo_asoc='$id_gran_tipo_asoc' AND id_entidad_poseida = '$id_entidad_poseida' AND id_entidad_propietaria='$id_entidad_propietaria' ";
				//echo $qrystr_d;
				$data = $conn->query($qrystr_d);

				if($id_gran_tipo_asoc==7 or $id_gran_tipo_asoc==9)  //7	Herramienta por Perfil - 9	Verbo por Perfil
				{
					$this->asociar_x_perfil($id_entidad_propietaria,'');
				}

				$arr_ret['descripcion'] = "$count registro(s) afectado(s)";
			}
		} catch(PDOException $e)
		{
			$arr_ret = array("estado" => false, "descripcion"=>$e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}

	function asociar_x_perfil($perfil,$member='')
	{
		global $conn;
		global $_ent;
		try
		{

			if($member<>"")
			{
				$filtro_un_member="AND cod_member='$member'";
				$qrystr_delete_asoc="DELETE asociaciones 
									 FROM asociaciones 
									 INNER JOIN member ON member.cod_member=asociaciones.id_entidad_propietaria
									 WHERE member.profile=$perfil AND member.cod_member='$member' AND asociaciones.id_gran_tipo_asoc IN('1','5')";
				$data = $conn->query($qrystr_delete_asoc);
			}
			else
			{
				$qrystr_delete_asoc="
				DELETE asociaciones 
									 FROM asociaciones 
									 INNER JOIN member ON member.cod_member=asociaciones.id_entidad_propietaria
									 WHERE member.profile=$perfil AND asociaciones.id_gran_tipo_asoc IN('1','5')";
				$data = $conn->query($qrystr_delete_asoc);
			}

			$qrystr_member="INSERT IGNORE INTO asociaciones(id_entidad_propietaria,id_entidad_poseida,id_gran_tipo_asoc)
							SELECT m1.cod_member,a.id_entidad_poseida,
							CASE WHEN a.id_gran_tipo_asoc=7 THEN 1 WHEN a.id_gran_tipo_asoc=9 THEN 5 END 
							FROM
							(
							SELECT cod_member
							FROM member
							WHERE profile=$perfil 
							$filtro_un_member
							) AS m1
							INNER JOIN asociaciones AS a
							WHERE a.id_entidad_propietaria=$perfil AND a.id_gran_tipo_asoc IN('7','9')";
			$data = $conn->query($qrystr_member);
		} catch(PDOException $e)
		{
			$arr_ret = array("estado" => false, "descripcion"=>$e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}
}
?>
