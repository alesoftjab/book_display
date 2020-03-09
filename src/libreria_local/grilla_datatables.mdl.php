<?php
class GrillaDataTables
{
	protected $qrystr;
	
	public function GrillaDataTables()
	{
		$this->qrystr = '';
	}

	/* 
	Método abstracto. 
	* Construir la consulta con o sin los filtros en pantalla para que puede ser ejecutada.
	* 
	* Parámetros
	* arrFiltros: es un array de parámetros
	* 
	* Retorno: void 
	*/
	public function construirConsulta($arrFiltros)
	{
	}
	/* 
	Ejecutar la consulta sin aplicar filtros y sin obtener registros. Es solamente para saber la cantidad total de registros sin aplicar filtros
	*/
	public function getNumeroFilas($arrFiltros)
	{
		global $conn;

		try 
		{
			$this->construirConsulta($arrFiltros);
			$qrystr = " SELECT COUNT(*) AS _cuenta_ FROM($this->qrystr) AS _tabla_cuenta_";
			$data = $conn->query($qrystr);
			$result = $data->fetchAll(PDO::FETCH_ASSOC);
//~ var_dump($result);			
			return($result[0]['_cuenta_']);
		} catch(PDOException $e) 
		{
			echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
		}
	}
	
	/* 
	Ejecutar la consulta y devolver los registros obtenidos. 
	*/
	public function getFilas($arrFiltros)
	{
		global $conn;

		try 
		{
			$this->construirConsulta($arrFiltros);
			$data = $conn->query($this->qrystr);
			return($data->fetchAll(PDO::FETCH_ASSOC));
		} catch(PDOException $e) 
		{
			echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$this->qrystr";
		}
  }
}

?>
