<?php
require_once 'src/usuarios.mdl.php';

class usuariosCtrlr
{
	
	private $model;
	
	// ********************************** CONSTRUCTOR **************************************** //
	public function __CONSTRUCT()
	{
		$this->model = new usuarios();
	}
	
	// ********************************** Index **************************************** //
	public function Index()
	{
		global $_ent;
		require_once 'header.view.php';
		require_once 'src/usuarios_lista.view.php';
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

//~ echo"llega |". $requestData['filtro_unico']."|<br>";
		
		$arrFiltrosOpcionales = array();
		if($requestData['filtro_unico'] <> '')
		  $arrFiltrosOpcionales['filtro_unico'] = $requestData['filtro_unico'];
		if($requestData['search']['value'] <> '')
		  $arrFiltrosOpcionales['search'] = $requestData['search']['value'];
		
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
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();

		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = $this->armarFiltrosOpcionales();
		
		// *********** PAGINACIÓN ****************** //
		$requestData = $_REQUEST;
		$arrPaginacion = array('start' => $requestData['start'], 'length' => $requestData['length']); 
	  //                          getFilasParaDataTables($arrFiltrosObligatorios=array(), $arrFiltrosOpcionales=array(), $arrPaginacion=array())
		$resultados = $this->model->getFilasParaDataTables($arrFiltrosObligatorios, $arrFiltrosOpcionales, $arrPaginacion);
		
		
		// ************************ PREPARAR ALGUNOS VALORES DE COLUMNAS *************************//
		$this->formatearFilas($resultados['rows']);
		
		$json_data = array(
					"draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
					"recordsTotal"    => intval( $resultados['totalData'] ),  // total number of records
					"recordsFiltered" => intval( $resultados['totalFiltered'] ), // total number of records after searching, if there is no searching then totalFiltered = totalData
					"data"            => $resultados['rows']  // total data array
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
		//Llamar al modelo para obtener los datos con el cod_member que vino como parámetro
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
		
		$cod_member = $requestData['cod_member'];
		$row = $this->model->getDatosParaForm($cod_member);
		$tipos_doc      = $this->model->getTiposDoc();
		$profiles     = $this->model->getProfiles();
		$clientes     = $this->model->getClientes();
		//var_dump($row);
		require_once 'src/usuarios_form_ABM.view.php';
	}
	
	
	public function grabar()
	{
		global $_ent;
		$requestData = $_REQUEST;
		
		$cod_member = $requestData['cod_member'];
		//Actualizar la cantidad
		$arr_ret = $this->model->grabar($cod_member, $requestData['nuevousuario'], $requestData['apellido'], $requestData['nombres'], $requestData['tipo_doc_id'], $requestData['nro_doc'], $requestData['profile'], $requestData['id_cliente']);
		$json_data = array(
					"estado"         => $arr_ret['estado'],   	//  
					"descripcion"    => $arr_ret['descripcion']
					);
		echo json_encode($json_data);  // send data as json format
	}

	public function contrasenia()
	{
		global $_ent;
		$requestData = $_REQUEST;
		
		require_once 'header.view.php';
		require_once 'src/usuarios_contrasenia.view.php';
		require_once 'footer.view.php';

	}
	
	public function contraseniaGrabar()
	{
		global $_ent;
		$requestData = $_REQUEST;
	  $arr_ret = array("estado" => true, "descripcion"=>'');	
		
		if($requestData['contrasenia'] <> $requestData['contrasenia1'])
		{
			$json_data = array(
						"estado"         => false,   	//  
						"descripcion"    => "La contraseña ingresada no coincide con su repetición"
						);
		}
		else
		{
			$arr_ret = $this->model->contraseniaGrabar($_ent->member, $requestData['contrasenia']);
		}

//~ var_dump($arr_ret);exit;

		$json_data = array(
					"estado"         => $arr_ret['estado'],   	//  
					"descripcion"    => $arr_ret['descripcion']
					);
		echo json_encode($json_data);  // send data as json format
	}

	public function getDatos()
	{
		require_once 'src/general.mdl.php';
		$modelo_general = new GeneralMdl();

		global $_ent;
		$requestData = $_REQUEST;
		
		if($requestData['cod_member'] <> '')
		{
			$cod_member = $requestData['cod_member'];
			$resultado = $modelo_general->getDatosId('usuarios','cod_member',$cod_member);

			$arr_ret=array();

			if($resultado[estado]==false)
			{
				$arr_ret = array("estado" => false, "descripcion"=>htmlentities( (string) $resultado[descripcion],ENT_COMPAT | ENT_HTML401,"ISO8859-1"), 'datos' => array());	
			}
			else
			{	
				$arr_ret = array("estado" => true, "descripcion"=>'', 'datos' => $resultado[datos]);	
			}

		}
		else
		{
			$arr_ret = array("estado" => false, "descripcion"=>'Falta el parámetro cod_member');	;
		}

		$json_data_encoded = json_encode($arr_ret);
		echo $json_data_encoded;  // send data as json format
	}

	
	public function blanquear()
	{
		global $_ent;
		$requestData = $_REQUEST;
		
		$cod_member = $requestData['cod_member'];
		//Actualizar la cantidad
		$arr_ret = $this->model->blanquear($cod_member);
		$json_data = array(
					"estado"         => $arr_ret['estado'],   	//  
					"descripcion"    => $arr_ret['descripcion']
					);
		echo json_encode($json_data);  // send data as json format
	}
	
	
}
