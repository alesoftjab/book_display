<?php
require_once 'src/shop_carts.mdl.php';

class ShopCartsCtrlr
{
	
	private $model;
	
	// ********************************** CONSTRUCTOR **************************************** //
	public function __CONSTRUCT()
	{
		$this->model = new ShopCarts();
	}
	
	// ********************************** Index **************************************** //
	public function Index()
	{
		global $_ent;
		require_once 'header.view.php';
		require_once 'src/shop_carts_lista.view.php';
		require_once 'footer.view.php';
		
	}

	// ********************************** Index **************************************** //
	public function xlsShopCarts()
	{
		global $_ent;
		global $conn;

		$requestData = $_REQUEST;
		$fecha_desde = $requestData['fecha_desde'];
		$fecha_hasta = $requestData['fecha_hasta'];

		require_once 'src/shop_carts.xls.view.php';
		
	}

	// ************************ armarFiltrosObligatorios ******************************* //
	/*
	Este método interpreta los filtros obligatorios desde el request
	* Retorno: array('nombreFiltro'=>'valorFiltro')
	*/ 
	public function armarFiltrosObligatorios()
	{
		$requestData = $_REQUEST;

		$arrFiltrosObligatorios = array();

		return($arrFiltrosObligatorios);
	}

	// ************************ armarFiltrosOpcionales ******************************* //
	/*
	Este método interpreta los filtros opcionales desde el request
	* Retorno: array('nombreFiltro'=>'valorFiltro')
	*/ 
	public function armarFiltrosOpcionales()
	{
		$requestData = $_REQUEST;

		
		$arrFiltrosOpcionales = array();
		if($requestData['filtro_unico'] <> '')
		  $arrFiltrosOpcionales['filtro_unico'] = $requestData['filtro_unico'];
		if($requestData['filtro_fecha'] <> '')
		  $arrFiltrosOpcionales['filtro_fecha'] = $requestData['filtro_fecha'];
		if($requestData['tipo_listado'] <> '')
		  $arrFiltrosOpcionales['tipo_listado'] = $requestData['tipo_listado'];
		if($requestData['id_comprobante'] <> '')
		  $arrFiltrosOpcionales['id_comprobante'] = $requestData['id_comprobante'];
		if($requestData['filtro_txt'] <> '')
		  $arrFiltrosOpcionales['filtro_txt'] = $requestData['filtro_txt'];
		return($arrFiltrosOpcionales);
	}
	

	// ************************ formatearFilas ******************************* //
	/*
	Este método formatea algunos datos de las columnas obtenidas desde el modelo. Recibe 
	* Parámetros: $rows es un array de registros. Se toma por referencia
	* Retorno: void. Pero modifica por referencia el valor de $rows
	*/ 
	public function formatearFilas(&$rows)
	{
		foreach($rows AS &$row)
		{
			//Asignar htmlentities para que JSON no falle
			foreach($row AS &$campo)
				$campo =  htmlentities( (string) $campo);
			//FIN Asignar htmlentities para que JSON no falle

			//~ $row['url_imagen'] = $this->getUrlImagen($row["nombre_imagen"]);

		}
	}


	// ************************ getLista ******************************* //
	/*
	Este método devuelve una lista de datos en json para que sean representados en la vista que está en el book
	*/ 
	public function getLista()
	{
		$requestData = $_REQUEST;
		
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();

		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = $this->armarFiltrosOpcionales();
		
		// *********** PAGINACIÓN ****************** //
		$arrPaginacion = array('start' => $requestData['start'], 'length' => $requestData['length']); 

	  //getFilasParaDataTables($arrFiltrosObligatorios=array(), $arrFiltrosOpcionales=array(), $arrPaginacion=array())
		$resultados = $this->model->getFilasParaDataTables($arrFiltrosObligatorios, $arrFiltrosOpcionales, $arrPaginacion);
		

		// ************************ PREPARAR ALGUNOS VALORES DE COLUMNAS *************************//
		$this->formatearFilas($resultados['rows']);
		
		$json_data = array(
					"draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
					"recordsTotal"    => intval( $resultados['totalData'] ),  // total number of records
					"recordsFiltered" => intval( $resultados['totalFiltered'] ), // total number of records after searching, if there is no searching then totalFiltered = totalData
					"data"            => $resultados['rows']   // total data array
					);

		echo json_encode($json_data);  // send data as json format
	}
		
	// ************************ getUno ******************************* //
	/*
	Este método devuelve un único elemento y puede imprimirlo en json para que sea representados en la vista que está en el book o utilizad por otro método
	Parámetros:
	* $parImprimir: Si es true hace echo del registro en formato json
	* Retorno: el registro como array tal como lo devuelve el modelo ( a veces se usa y a veces no )
	*/ 
	public function getUno($parImprimir=true)
	{
		//Llamar al modelo para obtener los datos con el id_orden_det que vino como parámetro
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();

		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = $this->armarFiltrosOpcionales();
		$rows = $this->model->getFilas(array_merge($arrFiltrosObligatorios, $arrFiltrosOpcionales));

		// ************************ PREPARAR ALGUNOS VALORES DE COLUMNAS *************************//
		$this->formatearFilas($rows);

		$row = $rows[0];
		//~ foreach($row AS &$campo)
		  //~ $campo =  htmlentities( (string) $campo);

		$json_data_encoded = json_encode($row);
		
		if($parImprimir==true)
  		echo $json_data_encoded;  // send data as json format
		
		return($row);  // return data as json format
		
	}
	
	public function formAbm()
	{
		global $_ent;
		$requestData = $_REQUEST;

		$id_book    = $requestData['id_book'];
		$row           = $this->model->getDatosParaForm(desEncriptarID($id_book));
				
		//var_dump($row);
		require_once 'src/shop_carts_form_ABM.view.php';
	}


	public function grabar()
	{
		global $_ent;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];
		$id_book = desEncriptarID($id_book);
		
		$titulo=$requestData['titulo']; 
		$book_topics = $requestData['book_topics']; 
		$url_image_base = $requestData['url_image_base']; 
		$height = $requestData['height'];
		$width = $requestData['width'];
		
		//Actualizar las cantidades por elemento
		$arr_ret = $this->model->grabar($id_book, $titulo, $book_topics, $url_image_base, $height, $width);

		$json_data = array(
					"estado"         => $arr_ret['estado'],   	//
					"descripcion"    => $arr_ret['descripcion']
					);
		echo json_encode($json_data);  // send data as json format
	}
	
	
	public function editarPaginas()
	{
		global $_ent;
		$requestData = $_REQUEST;

		$id_book    = $requestData['id_book'];
		$row        = $this->model->getDatosParaForm(desEncriptarID($id_book));
				
		//var_dump($row);
		require_once 'header.view.php';
		require_once 'src/shop_carts_editar_paginas.view.php';
		require_once 'footer.view.php';

	}


	public function getShopCartData()
	{
		global $_ent;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];
		$id_book = desEncriptarID($id_book);
		$retorno = $this->model->getShopCartData($id_book);

		if($retorno[estado]==false)
		{
			$arr_ret = array("estado" => false, "descripcion"=>htmlentities( (string) $retorno[descripcion],ENT_COMPAT | ENT_HTML401,"ISO8859-1"), 'datos' => array());	
		}
		else
		{	
			$arr_ret = array("estado" => true, "descripcion"=>'', 'shopCartData' => $this->utf8_encode_recursive($retorno[shopCartData]));
		}

		echo json_encode($arr_ret);  // send data as json format

	}
	public function setShopCartData()
	{
		global $_ent;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];
		$id_book = desEncriptarID($id_book);
		
		$bookData = json_decode($requestData['bookData'],true);

//~ var_dump($bookData);exit;
		$retorno = $this->model->setShopCartData($id_book, $bookData);
		
		echo json_encode($retorno);  // send data as json format

	}

	public function utf8_encode_recursive($code) 
	{
		if (is_array($code))
		{
			foreach ($code as &$c) 
				$c = $this-> utf8_encode_recursive($c);
			return $code;
		}
		return  utf8_encode( (string) $code);
	}
}
