<?php
//******************************************* CLASE PEDIDO *************************************************
//*********************************************************************************************************
class GestorLogCambio
{
	var $debug;
	var $objetoInicial;
	var $objetoFinal;
  var $accion;
  var $campos_excluidos;
  var $diferencias;
  var $tabla;
  var $tipo_objeto;
  var $campo1;
  var $campo2;
  var $campo3;
  var $campo4;
  var $campo5;
  
	// ******************************************************************************************* //
	// ******************************************************************************************* //
	// ************** Constructor ****************
	function GestorLogCambio($tabla,$par_tipoObjeto='')
	{
  	$this->objetoInicial=array();
  	$this->objetoFinal=array();
  	$this->diferencias=array();
  	$this->campos_excluidos=array();
  	$this->tabla=$tabla;
  	if($par_tipoObjeto=='')
  	  $this->tipoObjeto=$tabla;
  	else  
      $this->tipoObjeto=$par_tipoObjeto;

  	$this->campo1='';
  	$this->campo2='';
  	$this->campo3='';
  	$this->campo4='';
  	$this->campo5='';

		$this->debug=false;
	}
	// ************ Fin Constructor **************

	// *********************************************************
	function obtenerObjeto($filtro_unico)
	{
  	global $conn;
  	global $sesion;
  	global $usuario;

    //Obtener el registro requerido.
		$qrystr = " SELECT *
    	          FROM $this->tabla 
    	          WHERE $filtro_unico
    	        ";
 //~ echo "$qrystr<br />";

		$data = $conn->query($qrystr);
		$row = $data->fetch(PDO::FETCH_ASSOC);
    return $row;
	}
	// ************************************************

	// *********************************************************
	function obtenerInicial($filtro_unico)
	{
    $this->objetoInicial = $this->obtenerObjeto($filtro_unico);
	}
	// ************************************************

	// *********************************************************
	function obtenerFinal($filtro_unico)
	{
    $this->objetoFinal = $this->obtenerObjeto($filtro_unico);
    $this->obtenerDiferencias();
	}
	// ************************************************

	// *********************************************************
	function obtenerDiferencias()
	{
    if(!is_array($this->objetoFinal))//Si el objeto Final es nulo, no hay diferencias válidas porque es una BAJA.
      $this->diferencias=array();
    else                               //El objeto Final no es nulo => comparar campo a campo el inicial con el final.
    {
//~ echo"this->objetoInicial <br>";
//~ var_dump($this->objetoInicial);
      foreach($this->objetoFinal AS $campo => $valor)
      {
				if(!in_array($campo,$this->campos_excluidos))
        {
					if($valor<>$this->objetoInicial[$campo])
						$this->diferencias[$campo]=array('i'=>$this->objetoInicial[$campo],'f'=>$valor);
        }
      }
    }
	}
	// ************************************************

	// *********************************************************
	function loguearDiferencias($id_objeto,$campo1='',$campo2='',$campo3='',$campo4='',$campo5='')
	{
  	global $conn;
  	global $sesion;
  	global $_ent;

    if($campo1<>'')
      $this->campo1=$campo1;
    if($campo2<>'')
      $this->campo2=$campo2;
    if($campo3<>'')
      $this->campo3=$campo3;
    if($campo4<>'')
      $this->campo4=$campo4;
    if($campo5<>'')
      $this->campo5=$campo5;

//~ var_dump($this);
    if($this->accion=='');
    {
      if(!is_array($this->objetoInicial))
        $this->accion=1;  //Alta
      else
   	    if(!is_array($this->objetoFinal))
          $this->accion=2;  //Baja
        else
          $this->accion=3;  //Modif
    }
    
		$ip=$_ent->getIP();
    //Almacenar Log.
		$qrystr = "INSERT INTO log_objetos (
                  `tipo_objeto` ,`id_objeto` ,accion,
                  `detalle` ,
                  `fecha` ,`momento` ,`usuario` ,
                  `campo1` ,`campo2` ,`campo3` ,
                  `campo4` ,`campo5`,`ip`
                  )
                  VALUES (
                  '$this->tipoObjeto', '$id_objeto','$this->accion',
                  '". str_replace("'","\'",var_export($this->diferencias,true)) ."',
                  NOW(), NOW(), '".$_ent->member."',
                  '$this->campo1', '$this->campo2', '$this->campo3',
                  '$this->campo4', '$this->campo5','".$ip."'
                  );
    	        ";
		$count = $conn->exec($qrystr);
	}
	// ************************************************

}
// ************************************************}//end of class
//******************************************* FIN CLASE ***************************************************
//*********************************************************************************************************
/*
Ejemplo:
$gc= new GestorLogCambio('productos');
...
...
...
...
$gc->obtenerInicial("id_vd='$id_vd'");
...
...
...
$gc->obtenerFinal("id_vd='$id_vd'");
$gc->loguearDiferencias($id_vd);
...
...

*/

?>
