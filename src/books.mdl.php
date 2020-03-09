<?php
require_once 'src/libreria_local/grilla_datatables.mdl.php';

class Books extends GrillaDataTables
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
				$str_filtro_txt = " AND (b.titulo LIKE('%$filtro_txt%') OR c.nombre LIKE('%$filtro_txt%'))";
		}

		// ************ FIN Parámetros opcionales **************** //
		
		// ************** Paginación ***************** //
		$_paginacion_ = "";
		if(isset($arrFiltros['_start_']) AND isset($arrFiltros['_length_']))
			$_paginacion_ =" LIMIT ".$arrFiltros['_start_']." ,".$arrFiltros['_length_']."  "; 
		
		// ************ FIN Paginación *************** //

		$this->qrystr = " SELECT b.*,
												IFNULL(COUNT(DISTINCT(bd.id_book_det)), 0) AS paginas, c.nombre AS cliente
											FROM `books` AS b 
												LEFT JOIN books_det As bd ON b.id_book = bd.id_book
												LEFT JOIN clientes As c ON b.id_cliente = c.id_cliente
											WHERE 1
											$str_filtro_fecha
											$str_filtro_unico
											$str_filtro_txt
											  AND ((SELECT m1.id_cliente FROM member AS m1 WHERE m1.cod_member='$_ent->member' ) ) IN(0, b.id_cliente)
											GROUP BY b.id_book
											ORDER BY b.fecha DESC, b.id_book DESC
											$_paginacion_ ";		

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
	public function grabar($par_id_book, $par_titulo, $par_book_topics, $par_url_image_base, $par_pageHeight, $par_pageWidth, $par_home_url)
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
																	pageWidth='$par_pageWidth',
																	home_url='$par_home_url'
								 		WHERE id_book='$id_book';";

				$data = $conn->query($qrystr_u);
			}
			else
			{
				//inserto libro
				//~ $data = $conn->query("INSERT INTO `books`(`fecha`, `id_book`, `id_comprobante`,usuario) 
																						//~ VALUES ('$fecha','$id_book',0,'$_ent->member')");

				$qrystr = "SELECT (IFNULL(MAX(id_book_ver),0)+1) AS id_book_ver FROM `books` WHERE id_cliente=(SELECT id_cliente FROM member WHERE cod_member='$_ent->member')";
				$data = $conn->query($qrystr);
				$result = $data->fetch(PDO::FETCH_ASSOC);
				$id_book_ver = $result['id_book_ver'];

				$data = $conn->query("INSERT INTO `books`(id_book_ver,
																									`fecha`, `id_cliente`, `titulo`, 
																									`member`, `momento`, `book_topics`, 
																									`url_image_base`, `pageHeight`, `pageWidth`, `home_url`
																									) VALUES (
																									'$id_book_ver',
																									NOW(),(SELECT id_cliente FROM member WHERE cod_member='$_ent->member'),'$par_titulo', 
																									'$_ent->member', NOW(), '$par_book_topics', 
																									'$par_url_image_base', '$par_pageHeight', '$par_pageWidth', '$par_home_url'
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

	// ************************ registrarAcceso ******************************* //
	public function registrarAcceso($par_id_book, $par_pagina, $par_ip, $par_telefono_vendedor)
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
			
			if(1*$id_book == 0)
				throw new Exception("No se especificó el id_book");

			$pagina = $conn->quote($par_pagina);
			//Le volvemos a sacar las comillas al $id_casino
			$pagina = 1 * str_replace("'","", $pagina );

			$telefono_vendedor = $conn->quote($par_telefono_vendedor);
			//Le volvemos a sacar las comillas al $telefono_vendedor
			$telefono_vendedor = str_replace("'","", $telefono_vendedor );

			$ip = $conn->quote($par_ip);
			//Le volvemos a sacar las comillas al $ip
			$ip = str_replace("'","", $ip );

			$qrystr_u ="INSERT INTO `books_det_accesos`(
										`id_book`, `id_book_det`, `momento`, 
										`fecha`, `telefono_vendedor`, `ip`
										) VALUES (
										'$id_book',(SELECT id_book_det FROM books_det WHERE id_book='$id_book'ORDER BY id_book_det LIMIT $pagina, 1 ),NOW(),
										NOW(),'$telefono_vendedor','$ip')";
//~ echo"qrystr_u: $qrystr_u<br>";
			$data = $conn->query($qrystr_u);
			
			$arr_ret['descripcion'] = "$count registro(s) afectado(s)";
		} catch(PDOException $e)
		{
			$arr_ret = array("estado" => false, "descripcion"=>$e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}

	// ************************ addPage ******************************* //
	public function addPage($par_id_book, $par_image, $par_url_image_base, $par_pageHeight, $par_pageWidth, $par_page_topics='', $par_id_section=0)
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

			$image = $conn->quote($par_image);
			//Le volvemos a sacar las comillas al $image
			$image = str_replace("'","", $image );

			$page_topics = $conn->quote($par_page_topics);
			//Le volvemos a sacar las comillas al $page_topics
			$page_topics = str_replace("'","", $page_topics );

			$id_section = $conn->quote($par_id_section);
			//Le volvemos a sacar las comillas al $id_section
			$id_section = str_replace("'","", $id_section );

			$url_image_base = $conn->quote($par_url_image_base);
			//Le volvemos a sacar las comillas al $url_image_base
			$url_image_base = str_replace("'","", $url_image_base );

			$pageHeight = $conn->quote($par_pageHeight);
			//Le volvemos a sacar las comillas al $pageHeight
			$pageHeight = str_replace("'","", $pageHeight );

			$pageWidth = $conn->quote($par_pageWidth);
			//Le volvemos a sacar las comillas al $pageWidth
			$pageWidth = str_replace("'","", $pageWidth );

			$data = $conn->query("INSERT INTO `books_det`(
															`id_book`, `image`, `page_topics`, `id_section`
															) VALUES (
															'$id_book','$image','$page_topics','$id_section'
															)");
			$id_book_det = $conn->lastInsertId();
		
			$arr_ret['descripcion'] = "$count registro(s) afectado(s)";
			
			//Actualizar datos en book
			$qrystr_u ="UPDATE books SET
																url_image_base='$url_image_base', 
																pageHeight='$pageHeight', 
																pageWidth='$pageWidth'
									WHERE id_book='$id_book';";

			$data = $conn->query($qrystr_u);
				
			
			
		} catch(PDOException $e)
		{
			$arr_ret = array("estado" => false, "descripcion"=>$e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}


	// ************************ getBookData ******************************* //
	/*
	Este método devuelve un array de datos que se usarán en las páginas
	*/ 
	public function getBookData($par_id_book)
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	
		try 
		{
			$id_book = $conn->quote($par_id_book);
			$qrystr = " SELECT b.*, 
										bd.id_book_det, bd.id_book, bd.image, bd.page_topics, bd.id_section,
										bda.id_area, bda.x, bda.y, bda.width, bda.height, bda.rotation, bda.codigo 
									FROM books AS b
									  INNER JOIN books_det AS bd ON b.id_book = bd.id_book 
									  LEFT JOIN books_det_areas AS bda ON bd.id_book_det = bda.id_book_det 
									WHERE b.id_book=$id_book
									ORDER BY bd.id_book_det, bda.id_area";
			$data = $conn->query($qrystr);
			$bookData = array();
			$ult_pag = 0;
			$nro_pag = -1;
			while($row = $data->fetch(PDO::FETCH_ASSOC))
			{
//~ var_dump($row);				
				//Si es la primera iteración establecer los valores de book
				if($nro_pag == -1)
				{
					$bookData['id_book'] = $row['id_book']; 
					$bookData['titulo'] = $row['titulo']; 
					$bookData['pageHeight'] = $row['pageHeight']; 
					$bookData['pageWidth'] = $row['pageWidth']; 
				}
				//Cada vez que haya una nueva página establecer los valores generales de esa página
				if($ult_pag <> $row['id_book_det'])
				{
					$nro_pag++;
					$ult_pag = $row['id_book_det'];
					$bookData['pages'][$nro_pag]['id_book_det'] = $row['id_book_det']; 
					$bookData['pages'][$nro_pag]['image'] = $row['url_image_base'] . '/' . $row['image']; 
					$bookData['pages'][$nro_pag]['ClickAreas'] = array(); 
				}
				//Si la fila actual tiene valores de clickArea entocnes agregarla a la página actual
				if(1 * $row['id_area'] <> 0)
				{
					$bookData['pages'][$nro_pag]['ClickAreas'][] = array("x" => $row['x'], "y" => $row['y'],"width" => $row['width'],"height" => $row['height'], "rotation" => $row['rotation'], "codigo" => $row['codigo']); 
				}
			}
			
			$arr_ret['bookData'] = $bookData;
//~ var_dump($bookData);exit;			
		} catch(PDOException $e) 
		{
			echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			$arr_ret = array("estado" => false, "descripcion"=> $e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}

	// ************************ setBookData ******************************* //
	/*
	Este método graba los detalles de páginas y clickAreas que vienen desde el editor
	*/ 
	public function setBookData($par_id_book, $par_bookData)
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
					if($clickArea['deleted'] <> true)
					{ 
						$qrystr = " INSERT INTO `books_det_areas` (`id_book_det`, `x`, `y`, `width`, `height`, `rotation`, `codigo`
																											) VALUES(
																												'$id_book_det', '$clickArea[x]', '$clickArea[y]', '$clickArea[width]', '$clickArea[height]', '$clickArea[rotation]', '$clickArea[codigo]');";
						$data = $conn->query($qrystr);
					}
				}
			}
		} catch(PDOException $e) 
		{
			//~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			$arr_ret = array("estado" => false, "descripcion"=> $e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}
	// ************************ registrarVenta ******************************* //
	/*
	Este método graba los detalles de una venta
	*/ 
	public function registrarVenta($par_id_book, $par_ip, $par_telefono_vendedor, $par_items)
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	
		try 
		{
			$id_book = $conn->quote($par_id_book);
			//Le volvemos a sacar las comillas al $id_book
			$id_book = str_replace("'","", $id_book );

			if(1*$id_book == 0)
				throw new Exception("No se especificó el id_book");

			$telefono_vendedor = $conn->quote($par_telefono_vendedor);
			//Le volvemos a sacar las comillas al $telefono_vendedor
			$telefono_vendedor = str_replace("'","", $telefono_vendedor );

			$ip = $conn->quote($par_ip);
			//Le volvemos a sacar las comillas al $ip
			$ip = str_replace("'","", $ip );

			$items = $par_items;
			
//~ var_dump($items);exit;		

			foreach($items AS $item)
			{
				$codigo = $conn->quote($item['codigo']);
				$cantidad = $conn->quote($item['cantidad']);
				$precio = $conn->quote($item['precio']);
				$nombre = $conn->quote($item['nombre']);
				$qrystr = " INSERT INTO `books_ventas`(
												`id_book`, `id_producto`, `cantidad`, 
												`preciounidad`, `nombre`, 
												`momento`, `fecha`, `telefono_vendedor`, `ip`
												) VALUES (
												'$id_book',$codigo,$cantidad,
												$precio, $nombre,
												NOW(),NOW(),'$telefono_vendedor','$ip'
												)
				";
				$data = $conn->query($qrystr);
			}
		} catch(PDOException $e) 
		{
			//~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			$arr_ret = array("estado" => false, "descripcion"=> $e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}
	
	// ************************ importarPrecios ******************************* //
	/*
	Este método graba precios desde un csv
	*/ 
	public function importarPrecios($par_id_book, $tmpFileName, $par_strseparador)
	{
		global $conn;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	
		try 
		{
			$id_book = $conn->quote($par_id_book);
			//Le volvemos a sacar las comillas al $id_book
			$id_book = str_replace("'","", $id_book );

			$strseparador = $conn->quote($par_strseparador);
			//Le volvemos a sacar las comillas al $strseparador
			$strseparador = str_replace("'","", $strseparador );

			$qrystr = " DELETE FROM books_items_shopcart WHERE id_book='$id_book' ";
			$data = $conn->query($qrystr);
			
			$fp = fopen ($tmpFileName,"r");
			while ($data = fgetcsv ($fp, 100000, $strseparador))
			{
				$num = count ($data);
				$qrystr2="INSERT INTO books_items_shopcart ( id_book, codigo_parte, codigo_completo, descripcion, precio_unitario 
										) VALUES (
										'$id_book','".rtrim(ltrim($data[0]))."','".rtrim(ltrim($data[1]))."','".rtrim(ltrim($data[2]))."','".rtrim(ltrim($data[3]))."')";
				$data = $conn->query($qrystr2);
				//echo "$qrystr2";
			}
			fclose ($fp);
			
		} catch(PDOException $e) 
		{
			//~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			$arr_ret = array("estado" => false, "descripcion"=> $e->getMessage() . "<br>Consulta:<br>$qrystr");
		}

		return($arr_ret);
	}
}

?>
