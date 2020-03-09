<?php
require_once 'src/libreria_local/grilla_datatables.mdl.php';
require_once 'src/asociaciones.mdl.php';

class usuarios extends GrillaDataTables
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
		
		// ************** Parámetros obligatorios ***************** //
		// ************ FIN Parámetros obligatorios *************** //

		// ************** Parámetros opcionales ***************** //
		$str_filtro_txt = "";
		if(isset($arrFiltros['search']))
		{ 
			$filtro_txt=strtr($arrFiltros['search']," ","%");
			$filtro_txt_num = $conn->quote($filtro_txt);
			$filtro_txt="%$filtro_txt%";
			$filtro_txt = $conn->quote($filtro_txt);
			$str_filtro_txt = " AND ((cod_member =$filtro_txt_num) OR (nombres LIKE ($filtro_txt)) OR (apellido LIKE ($filtro_txt))) ";
		}   

		$str_filtro_unico = "";
		if(isset($arrFiltros['filtro_unico']))
		{ 
			$filtro_unico = $conn->quote($arrFiltros['filtro_unico']);
			$str_filtro_unico = " AND cod_member=$filtro_unico ";
		}
		// ************ FIN Parámetros opcionales **************** //
		
		// ************** Paginación ***************** //
		
		$_paginacion_ = "";
		if(isset($arrFiltros['_start_']) AND isset($arrFiltros['_length_']))
			$_paginacion_ =" LIMIT ".$arrFiltros['_start_']." ,".$arrFiltros['_length_']."  "; 
		
		// ************ FIN Paginación *************** //

		$this->qrystr = " SELECT m.cod_member, m.apellido, m.nombres, m.nro_doc, td.descri_tipo_doc AS tipo_doc, p.nombre AS profile, IFNULL(c.nombre, '') AS cliente 
											FROM member AS m
											  INNER JOIN tipo_doc AS td ON m.tipo_doc_id= td.tipo_doc_id
											  INNER JOIN profiles AS p ON m.profile= p.id_profile
											  LEFT JOIN clientes AS c ON m.id_cliente= c.id_cliente
											WHERE 1
											  $str_filtro_unico
											  $str_filtro_txt
											 ORDER BY m.apellido, m.nombres 
											$_paginacion_
										 ";		//
		//~ $this->qrystr = " select id_apertura AS cod_member, usuario AS nom_usuario 
											//~ from aperturas
											//~ WHERE 1
											  //~ $str_filtro_unico
											  //~ $str_filtro_txt
											//~ $_paginacion_
										 //~ ";		

//echo"this->qrystr: $this->qrystr<br>";										 
	}
	
	/* 
	
	*/
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
	public function getDatosParaForm($par_cod_member)
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	
		try 
		{
			$cod_member = $conn->quote($par_cod_member);
			$qrystr = "SELECT * 
									FROM member 
									WHERE cod_member=$cod_member";
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

	// ************************ getTiposDoc ******************************* //
	/*
	Este método devuelve un array de datos que se usarán en formularios
	*/ 
	public function getTiposDoc()
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');
		try
		{
			# Solo los tipos de bancas
			$qrystr = " SELECT *
									FROM tipo_doc
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
	
	// ************************ getProfiles ******************************* //
	/*
	Este método devuelve un array de datos que se usarán en formularios
	*/ 
	public function getProfiles()
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');
		try
		{
			# Solo los tipos de bancas
			$qrystr = " SELECT *
									FROM profiles
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
	
	// ************************ getClientes ******************************* //
	/*
	Este método devuelve un array de datos que se usarán en formularios
	*/ 
	public function getClientes()
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');
		try
		{
			# Solo los tipos de bancas
			$qrystr = " SELECT *
									FROM clientes
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
	

	// ************************ grabar ******************************* //
	/*
	Este método actualiza
	*/ 
	public function grabar($par_cod_member, $par_nuevousuario, $par_apellido, $par_nombres, $par_tipo_doc_id, $par_nro_doc, $par_profile, $par_id_cliente)
	{
		global $conn;
		global $_ent;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	

	  
		try 
		{
			$apellido = $conn->quote($par_apellido);
			$nombres = $conn->quote($par_nombres);
			$tipo_doc_id = $conn->quote($par_tipo_doc_id);
			$nro_doc = $conn->quote($par_nro_doc);
			$profile = $conn->quote($par_profile);
			//Le volvemos a sacar las comillas al $cod_member 
			$profile = str_replace("'","", $profile );
			$id_cliente = $conn->quote($par_id_cliente);
			//Le volvemos a sacar las comillas al $cod_member 
			$id_cliente = str_replace("'","", $id_cliente );
			$nuevousuario = $conn->quote($par_nuevousuario);
			//Le volvemos a sacar las comillas al $nuevousuario 
			$nuevousuario = str_replace("'","", $nuevousuario );
			$cod_member = $conn->quote($par_cod_member);
			//Le volvemos a sacar las comillas al $cod_member 
			$cod_member = str_replace("'","", $cod_member );
			$gc= new GestorLogCambio('member'); // tabla
			$gc->obtenerInicial("cod_member='$cod_member'");
		
			if($cod_member<>'') //update
			{
				$qrystr ="UPDATE member SET apellido=$apellido, nombres=$nombres, tipo_doc_id=$tipo_doc_id, nro_doc=$nro_doc, profile='$profile', id_cliente='$id_cliente' WHERE cod_member='$cod_member'";
				$data = $conn->query($qrystr);
			}
			else //insert
			{
				$nuevousuario = strtoupper($nuevousuario);
				if($nuevousuario <> '')
				{
					//Buscar si ya existe el código nuevo
					$qrystr = " SELECT *
											FROM member
											WHERE cod_member='$nuevousuario'
												";
					// echo $qrystr;
					$data = $conn->query($qrystr);
					$result = $data->fetchAll(PDO::FETCH_ASSOC);
					if(sizeof($result) > 0)
						throw new Exception('El nuevo código de usuario (' . $nuevousuario . ') ya existe. No se permite duplicar');	
					//FIN Buscar si ya existe el código nuevo
					
					$qrystr ="INSERT INTO `member`(cod_member, apellido, nombres, psw,
																				tipo_doc_id, nro_doc, profile, id_cliente
											)VALUES(
																					'$nuevousuario', $apellido, $nombres, '". $nuevousuario . ".bD',
																					$tipo_doc_id, $nro_doc, '$profile', '$id_cliente'
											)";
					$data = $conn->query($qrystr);
					$cod_member = $nuevousuario;
				}
				else
				{
					throw new Exception('No se ingresó el nuevo código de usuario');	
				}
			}
			
			$gc->obtenerFinal("cod_member='$cod_member'");
			$gc->loguearDiferencias($cod_member);

			//Si cambió el profile entonces autoasociar
			if(array_key_exists('profile',$gc->diferencias))
			{
				$ga= new Asociaciones();
				$ga->asociar_x_perfil($profile,$cod_member);
			}

			
		} catch(Exception $e) 
		{
			$arr_ret = array("estado" => false, "descripcion"=>$e->getMessage() . "");	
		}
	  
		return($arr_ret);
	}
	// ************************ contraseniaGrabar ******************************* //
	/*
	Este método actualiza
	*/ 
	public function contraseniaGrabar($par_cod_member, $par_contrasenia)
	{
		global $conn;
		global $_ent;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	

	  
		try 
		{
			$cod_member = $conn->quote($par_cod_member);
			//Le volvemos a sacar las comillas al $cod_member 
			$cod_member = str_replace("'","", $cod_member);
			$contrasenia = $conn->quote($par_contrasenia);
			//Le volvemos a sacar las comillas 
			$contrasenia = str_replace("'","", $contrasenia );

			$gc= new GestorLogCambio('member'); // tabla
			$gc->obtenerInicial("cod_member='$cod_member'");
		
			if($cod_member<>'') //update
			{
				if($contrasenia<>'') //update
				{
					$qrystr ="UPDATE member SET psw='$contrasenia', fecha_psw = CURDATE() WHERE cod_member='$cod_member'";
					$data = $conn->query($qrystr);
				}
				else
				{
					throw new Exception('La contraseña de usuario no puede ser nula');	
				}
			}
			else 
			{
				throw new Exception('El código de usuario no puede ser nulo');	
			}
			
			$gc->obtenerFinal("cod_member='$cod_member'");
			$gc->loguearDiferencias($cod_member);
			
		} catch(Exception $e) 
		{
			$arr_ret = array("estado" => false, "descripcion"=>$e->getMessage() . "");	
		}
	  
		return($arr_ret);
	}


	// ************************ blanquear ******************************* //
	/*
	Este método actualiza
	*/ 
	public function blanquear($par_cod_member)
	{
		global $conn;
		global $_ent;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	

	  
		try 
		{
			$cod_member = $conn->quote($par_cod_member);
			//Le volvemos a sacar las comillas al $cod_member 
			$cod_member = str_replace("'","", $cod_member );
			$gc= new GestorLogCambio('member'); // tabla
			$gc->obtenerInicial("cod_member='$cod_member'");
		
			if($cod_member<>'') //update
			{
				$qrystr ="UPDATE member SET psw='". $cod_member . "SH' WHERE cod_member='$cod_member'";
				$data = $conn->query($qrystr);
			}
			else //insert
			{
				throw new Exception('No se encontro el usuario');	
			}
			
			$gc->obtenerFinal("cod_member='$cod_member'");
			$gc->loguearDiferencias($cod_member);

		} catch(Exception $e) 
		{
			$arr_ret = array("estado" => false, "descripcion"=>$e->getMessage() . "");	
		}
	  
		return($arr_ret);
	}
}
?>
