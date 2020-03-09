<?php
require_once 'src/libreria_local/grilla_datatables.mdl.php';

class ShopCarts extends GrillaDataTables
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
		//~ $id_orden_base = $conn->quote($arrFiltros['id_orden_base']);
		// ************ FIN Parámetros obligatorios *************** //

		// ************** Parámetros opcionales ***************** //
		$str_filtro_fecha = '';
		if(isset($arrFiltros['filtro_fecha']))
		{ 
			$filtro_fecha = $conn->quote($arrFiltros['filtro_fecha']);
			//Le volvemos a sacar las comillas
			$filtro_fecha = str_replace("'","", $filtro_fecha);
			$str_filtro_fecha = " AND b.fecha LIKE('$filtro_fecha%') ";
		}   

		if(isset($arrFiltros['filtro_unico']))
		{ 
			$filtro_unico = $conn->quote($arrFiltros['filtro_unico']);
			$str_filtro_unico = " AND b.id_book=$filtro_unico ";
		}


		$str_filtro_txt = "";
		if(isset($arrFiltros['filtro_txt']))
		{
			$filtro_txt=strtr($arrFiltros['filtro_txt']," ","%");
			$filtro_txt = $conn->quote($filtro_txt);
			//Le volvemos a sacar las comillas
			$filtro_txt = str_replace("'","", $filtro_txt );

		  if($filtro_txt<>'')
				$str_filtro_txt = " AND b.titulo LIKE('%$filtro_txt%') ";
		}

		// ************ FIN Parámetros opcionales **************** //
		
		// ************** Paginación ***************** //
		$_paginacion_ = "";
		if(isset($arrFiltros['_start_']) AND isset($arrFiltros['_length_']))
			$_paginacion_ =" LIMIT ".$arrFiltros['_start_']." ,".$arrFiltros['_length_']."  "; 
		
		// ************ FIN Paginación *************** //

		$this->qrystr = " SELECT b.*,
												IFNULL(COUNT(DISTINCT(bd.id_book_det)), 0) AS paginas
											FROM `books` AS b 
												LEFT JOIN books_det As bd ON b.id_book = bd.id_book
											WHERE 1
											$str_filtro_fecha
											$str_filtro_unico
											$str_filtro_txt
											GROUP BY b.id_book
											ORDER BY b.fecha DESC, b.id_book DESC
											$_paginacion_ ";		

		//echo"this->qrystr: $this->qrystr<br>";	 
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
	public function getDatosParaForm($par_id_book)
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	
		try 
		{
			$id_book = $conn->quote($par_id_book);
			$qrystr = "SELECT * 
									FROM books 
									WHERE id_book=$id_book";
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
	
	// ************************ grabar ******************************* //
	public function grabar($par_id_book, $par_titulo, $par_book_topics, $par_url_image_base, $par_pageHeight, $par_pageWidth)
	{
		global $conn;
		global $_ent;
		$arr_ret = array("estado" => true, "descripcion"=>'');

		try
		{
			$elemento = $par_elemento;

			$id_book = $conn->quote($par_id_book);
			//Le volvemos a sacar las comillas al $id_book
			$id_book = str_replace("'","", $id_book );

			if($par_id_book*1<>0) //update
			{
				$id_book = $conn->quote($par_id_book);
				//Le volvemos a sacar las comillas al $id_casino
				$id_book = str_replace("'","", $id_book );

				$qrystr_u ="UPDATE books SET
																	titulo='$par_titulo', 
																	book_topics='$par_book_topics', 
																	url_image_base='$par_url_image_base', 
																	pageHeight='$par_pageHeight', 
																	pageWidth='$par_pageWidth'
								 		WHERE id_book='$id_book';";

				$data = $conn->query($qrystr_u);
			}
			else
			{
				//inserto libro
				//~ $data = $conn->query("INSERT INTO `books`(`fecha`, `id_book`, `id_comprobante`,usuario) 
																						//~ VALUES ('$fecha','$id_book',0,'$_ent->member')");
				$data = $conn->query("INSERT INTO `books`(`fecha`, `id_cliente`, `titulo`, 
																									`member`, `momento`, `book_topics`, 
																									`url_image_base`, `pageHeight`, `pageWidth`
																									) VALUES (
																									NOW(),0,'$par_titulo', 
																									'$_ent->member', NOW(), '$par_book_topics', 
																									'$par_url_image_base', '$par_pageHeight', '$par_pageWidth'
																									)");
				$id_book = $conn->lastInsertId();
			}

			$arr_ret['descripcion'] = "$count registro(s) afectado(s)";
		} catch(PDOException $e)
		{
			$arr_ret = array("estado" => false, "descripcion"=>$e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}


	// ************************ getShopCartData ******************************* //
	/*
	Este método devuelve un array de datos que se usarán en las páginas
	*/ 
	public function getShopCartData($par_id_book)
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	

		try 
		{
			$id_book = $conn->quote($par_id_book);
			$qrystr = " SELECT
										`codigo_parte`, `codigo_completo`, `descripcion`, `precio_unitario`
									FROM `books_items_shopcart` 
									WHERE `id_book`=$id_book";
			$data = $conn->query($qrystr);
			$bookData = array();
			$ult_pag = 0;
			$nro_pag = -1;
			$rows = $data->fetchAll(PDO::FETCH_ASSOC);
			
			$arr_ret['shopCartData'] = $rows;
//~ var_dump($shopCartData);exit;			
		} catch(PDOException $e) 
		{
			echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			$arr_ret = array("estado" => false, "descripcion"=> $e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}

	// ************************ setShopCartData ******************************* //
	/*
	Este método graba los detalles de páginas y clickAreas que vienen desde el editor
	*/ 
	public function setShopCartData($par_id_book, $par_bookData)
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	
		try 
		{
			$id_book = $conn->quote($par_id_book);
			$pages = $par_bookData['pages'];
//~ var_dump($pages);exit;		
	 
			$qrystr = " DELETE bda 
									FROM books_det AS bd  
									  INNER JOIN books_det_areas AS bda ON bd.id_book_det = bda.id_book_det AND bd.id_book=$id_book
								";
			$data = $conn->query($qrystr);

			foreach($pages AS $page)
			{
				$id_book_det = $page['id_book_det'];
				foreach($page['ClickAreas'] AS $clickArea)
				{
					 
					$qrystr = " INSERT INTO `books_det_areas` (`id_book_det`, `x`, `y`, `width`, `height`, `rotation`, `codigo`
																										) VALUES(
																											'$id_book_det', '$clickArea[x]', '$clickArea[y]', '$clickArea[width]', '$clickArea[height]', '$clickArea[rotation]', '$clickArea[codigo]');";
					$data = $conn->query($qrystr);
				}
			}
		} catch(PDOException $e) 
		{
			//~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			$arr_ret = array("estado" => false, "descripcion"=> $e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}
}

?>
