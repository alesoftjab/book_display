<?php
require_once 'src/general.mdl.php';


class GeneralCtrlr
{
	
	private $model;
	
	// ********************************** CONSTRUCTOR **************************************** //
	public function __CONSTRUCT()
	{
		$this->model = new GeneralMdl();
	}
	
	// ********************************** Index **************************************** //
	public function Index()
	{
		global $_ent;
		$requestData = $_REQUEST;
		
		require_once 'header.view.php';
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
		$arrFiltrosObligatorios['cod_member'] = $requestData['cod_member'];
		$arrFiltrosObligatorios['nivel'] = $requestData['nivel'];
		
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
		if($requestData['search']['value'] <> '')
		  $arrFiltrosOpcionales['search'] = $requestData['search']['value'];
		
		return($arrFiltrosOpcionales);
	}
	
	// ************************ getCampanias ******************************* //
	/*
	Este método interpreta los filtros obligatorios desde el request
	* Retorno: array('nombreFiltro'=>'valorFiltro')
	*/ 
	public function getCampanias()
	{
		global $_ent;
		$requestData = $_REQUEST;

		//Llamar al modelo para obtener los datos con el id_orden_det que vino como parámetro
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();
		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = $this->armarFiltrosOpcionales();
		
		$resultado = $this->model->getCampanias($arrFiltrosObligatorios, $arrFiltrosOpcionales);

		$arr_ret=array();

		if($resultado[estado]==false)
		{
			$arr_ret = array("estado" => false, "descripcion"=>htmlentities( (string) $resultado[descripcion],ENT_COMPAT | ENT_HTML401,"ISO8859-1"), 'datos' => array());	
		}
		else
		{	
			$arr_ret = array("estado" => true, "descripcion"=>'', 'datos' => $resultado[datos]);	
		}

		$json_data_encoded = json_encode($arr_ret);
		
		echo $json_data_encoded;  // send data as json format
		
	}

	public function getCampaniasUsuario()
	{
		global $_ent;
		$requestData = $_REQUEST;

		//Llamar al modelo para obtener los datos con el id_orden_det que vino como parámetro
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();
		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = $this->armarFiltrosOpcionales();
		
		$resultado = $this->model->getCampaniasUsuario($arrFiltrosObligatorios, $arrFiltrosOpcionales);

		$arr_ret=array();

		if($resultado[estado]==false)
		{
			$arr_ret = array("estado" => false, "descripcion"=>htmlentities( (string) $resultado[descripcion],ENT_COMPAT | ENT_HTML401,"ISO8859-1"), 'datos' => array());	
		}
		else
		{	
			$arr_ret = array("estado" => true, "descripcion"=>'', 'datos' => $resultado[datos]);	
		}

		$json_data_encoded = json_encode($arr_ret);
		
		echo $json_data_encoded;  // send data as json format
		
	}

	public function getCampaniasDefault()
	{
		global $_ent;
		$requestData = $_REQUEST;

		//Llamar al modelo para obtener los datos con el id_orden_det que vino como parámetro
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();
		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = $this->armarFiltrosOpcionales();
		
		$resultado = $this->model->getCampaniasDefault($arrFiltrosObligatorios, $arrFiltrosOpcionales);

		$arr_ret=array();

		if($resultado[estado]==false)
		{
			$arr_ret = array("estado" => false, "descripcion"=>htmlentities( (string) $resultado[descripcion],ENT_COMPAT | ENT_HTML401,"ISO8859-1"), 'datos' => array());	
		}
		else
		{	
			$arr_ret = array("estado" => true, "descripcion"=>'', 'datos' => $resultado[datos]);	
		}

		$json_data_encoded = json_encode($arr_ret);
		
		echo $json_data_encoded;  // send data as json format
		
	}

	public function getDatosUsuario()
	{
		global $_ent;
		$requestData = $_REQUEST;

		//Llamar al modelo para obtener los datos con el id_orden_det que vino como parámetro
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();
		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = $this->armarFiltrosOpcionales();
		
		$resultado = $this->model->getDatosUsuario($arrFiltrosObligatorios, $arrFiltrosOpcionales);

		$arr_ret=array();

		if($resultado[estado]==false)
		{
			$arr_ret = array("estado" => false, "descripcion"=>htmlentities( (string) $resultado[descripcion],ENT_COMPAT | ENT_HTML401,"ISO8859-1"), 'datos' => array());	
		}
		else
		{	
			$arr_ret = array("estado" => true, "descripcion"=>'', 'datos' => $this->utf8_encode_recursive($resultado[datos]));
		}

		$json_data_encoded = json_encode($arr_ret);
		
		echo $json_data_encoded;  // send data as json format
		
	}

	public function getDatosArbol()
	{
		global $_ent;
		$requestData = $_REQUEST;

		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();

		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = array('escalafon_deshabilitar'=> $requestData['escalafon_deshabilitar']);
		
		$resultado = $this->model->getDatosArbol($arrFiltrosObligatorios, $arrFiltrosOpcionales);

		$arr_ret=array();

			$arr_ret = array("estado" => true, "descripcion"=>'', 'datos' => array(($this->utf8_encode_recursive($resultado))));	

		$json_data_encoded = json_encode($arr_ret);
		
		echo $json_data_encoded;  // send data as json format
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

	public function getRepresentados()
	{
		global $_ent;
		$requestData = $_REQUEST;

		//Llamar al modelo para obtener los datos con el id_orden_det que vino como parámetro
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();
		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = $this->armarFiltrosOpcionales();
		
		$resultado = $this->model->getRepresentados($arrFiltrosObligatorios, $arrFiltrosOpcionales);

		$arr_ret=array();

		if($resultado[estado]==false)
		{
			$arr_ret = array("estado" => false, "descripcion"=>htmlentities( (string) $resultado[descripcion],ENT_COMPAT | ENT_HTML401,"ISO8859-1"), 'datos' => array());	
		}
		else
		{	
			$arr_ret = array("estado" => true, "descripcion"=>'', 'datos' => $resultado[datos]);	
		}

		$json_data_encoded = json_encode($arr_ret);
		
		echo $json_data_encoded;  // send data as json format
		
	}


}
