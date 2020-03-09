<?php
class ArmadosOrdenes
{
	/*
	Construir la consulta con o sin los filtros en pantalla para que puede ser ejecutada.
	*
	* Parámetros
	* arrFiltros: es un array de parámetros
	*
	* Retorno: void
	*/
	public function getBotones($arrFiltros)
	{

		global $conn;
		global $cfg;
		global $_ent;


		if(isset($arrFiltros['search']))
		{
			$filtro_txt=strtr($arrFiltros['search']," ","%");
			$filtro_txt="%$filtro_txt%";
			$filtro_txt = $conn->quote($filtro_txt);
			$filtro_txt1 = " AND ((od.nombreproducto LIKE($filtro_txt)) OR (od.id_vd LIKE ($filtro_txt))) ";
		}

		$this->qrystr = "select";
//~ echo"this->qrystr: $this->qrystr<br>";
		//Llamar al modelo para obtener los datos CON los filtros que vinieron como parámetros desde el formulario
		$arr_ret = array('rows' => $this->getFilas($arrFiltros));
		return($arr_ret);

	}
}
?>
