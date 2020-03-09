<?php
require_once 'src/asociaciones.mdl.php';

class AsociacionesCtrlr
{
	
	private $model;
	
	// ********************************** CONSTRUCTOR **************************************** //
	public function __CONSTRUCT()
	{
		$this->model = new Asociaciones();
	}
	
	// ********************************** Index **************************************** //
	public function Index()
	{
		global $_ent;

		$tipos_asociaciones = $this->model->getTiposAsociaciones();
		
		require_once 'header.view.php';
		require_once 'src/asociaciones_lista.view.php';
		require_once 'footer.view.php';
		
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
		$arrFiltrosObligatorios['id_gran_tipo_asoc'] = $requestData['id_gran_tipo_asoc'];
		$arrFiltrosObligatorios['id_entidad_propietaria'] = $requestData['id_entidad_propietaria'];
		$arrFiltrosObligatorios['tipo_vista'] = $requestData['tipo_vista'];

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
		//~ var_dump($requestData);

		//echo"llega |". $requestData['filtro_unico']."|<br>";
		
		$arrFiltrosOpcionales = array();
		if($requestData['filtro_unico'] <> '')
		  $arrFiltrosOpcionales['filtro_unico'] = $requestData['filtro_unico'];
		if($requestData['filtro_txt'] <> '')
		  $arrFiltrosOpcionales['filtro_txt'] = $requestData['filtro_txt'];
		if($requestData['id_entidad_poseida'] <> '')
		  $arrFiltrosOpcionales['id_entidad_poseida'] = $requestData['id_entidad_poseida'];
		
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
	Este método devuelve una lista de datos en json para que sean representados en la vista que está en el cliente
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
	Este método devuelve un único elemento y puede imprimirlo en json para que sea representados en la vista que está en el cliente o utilizad por otro método
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
	
	// ************************ getListaPropietarios ******************************* //
	public function getListaPropietarios()
	{
		$requestData = $_REQUEST;
//~ var_dump($requestData);

//~ echo"llega |". $requestData['filtro_unico']."|<br>";
		
		$arrFiltros = array();
		if($requestData['query'] <> '')
		  $arrFiltros['query'] = $requestData['query'];
		if($requestData['id_gran_tipo_asoc']*1 <> 0)
		  $arrFiltros['id_gran_tipo_asoc'] = $requestData['id_gran_tipo_asoc'];
		else
		{
			echo"ERROR: No se definió el id_gran_tipo_asoc";
			return;	
		}
			
		// *********** PAGINACIÓN ****************** //
		if($requestData['start'] == '')
		  $start=0;
		else
			$start = $requestData['start'];
		if($requestData['length'] == '')
		  $length=200;
		else
			$length = $requestData['length'];
		$arrPaginacion = array('start' => $start, 'length' => $length); 
//~ var_dump($arrFiltros);
		$resultados = $this->model->getListaPropietarios($arrFiltros, $arrPaginacion);
//~ var_dump($resultados);		
		
		// ************************ PREPARAR ALGUNOS VALORES DE COLUMNAS *************************//
		foreach($resultados AS &$row)
		{
			//Asignar htmlentities para que JSON no falle
			foreach($row AS &$campo)
				$campo =  htmlentities( (string) $campo,ENT_COMPAT | ENT_HTML401,"ISO8859-1");
			//FIN Asignar htmlentities para que JSON no falle

		}
		// ********************** FIN PREPARAR ALGUNOS VALORES DE COLUMNAS ***********************//
		
		$json_data = $resultados;

		echo json_encode($json_data);  // send data as json format
	}
	
	// ************************ Dar ******************************* //
	public function dar()
	{
		global $_ent;
		$requestData      = $_REQUEST;

		$id_gran_tipo_asoc = $requestData['id_gran_tipo_asoc'];
		$id_entidad_propietaria = $requestData['id_entidad_propietaria'];
		$id_entidad_poseida = $requestData['id_entidad_poseida'];
		
		//Dar
		$arr_ret = $this->model->dar($id_gran_tipo_asoc, $id_entidad_propietaria, $id_entidad_poseida);

		//Llamar al modelo para obtener los datos con los datos que vinieron como parámetros
		$row = $this->getUno(false);
		
		$json_data = array(
					"estado"         => $arr_ret['estado'],   	//  
					"descripcion"    => $arr_ret['descripcion'],  			// 
					"row"            => $row      // total data array
					);
		//~ header('Content-Type: application/json');			
		header('Content-Type: text/html; charset=ISO-8859-1'); 

		echo json_encode($json_data);  // send data as json format

	}

	// ************************ Quitar ******************************* //
	public function quitar()
	{
		global $_ent;
		$requestData      = $_REQUEST;

		$id_gran_tipo_asoc = $requestData['id_gran_tipo_asoc'];
		$id_entidad_propietaria = $requestData['id_entidad_propietaria'];
		$id_entidad_poseida = $requestData['id_entidad_poseida'];
		
		//Dar
		$arr_ret = $this->model->quitar($id_gran_tipo_asoc, $id_entidad_propietaria, $id_entidad_poseida);

		//Llamar al modelo para obtener los datos con los datos que vinieron como parámetros
		$row = $this->getUno(false);

		$json_data = array(
					"estado"         => $arr_ret['estado'],   	//  
					"descripcion"    => $arr_ret['descripcion'],  			// 
					"row"            => $row      // total data array
					);
		//~ header('Content-Type: application/json');			
		header('Content-Type: text/html; charset=ISO-8859-1'); 

		echo json_encode($json_data);  // send data as json format

	}

	

}
