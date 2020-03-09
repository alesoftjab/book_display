<?php
require_once 'src/general.mdl.php';
$mdlGeneral = new GeneralMdl();

$rows_tools = $mdlGeneral->getTools($_ent->member);

?>

<div class="container">
	<br>
	<!-- Main component for a primary marketing message or call to action -->
	<div class="jumbotron p-4 pb-1 m-0 mb-1 mt-2 text-center">
		<h1>Bienvenido a BOOK DISPLAY</h1>
		<p>Esta aplicación lo asistirá en todo lo relativo a la Administración Book Display</p>
	</div>
</div> <!-- /container -->

<!--
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
-->
    
<style>
    .card-custom {
        max-width: 30%;
        min-width: 30%;
    }
</style>

<div class="container" id="botonera_home">

<?php
$ult_tipo = 0;
foreach ($rows_tools as $row_tool) {
	if($ult_tipo <> $row_tool['tipo_tool'])
	{
		if($ult_tipo <> 0)
		{
			echo"
				</div>
			</div>
			";
		}
		echo"
			<div class=\"card\">
				<div class=\"card-header bg-info text-white\">
					<i class=\"" . $row_tool['icono_tipo'] . "\"></i> ". $row_tool['descripcion_tipo_tool'] ."
				</div>
				<div class=\"row card-body justify-content-around\">		
		";
		
		$ult_tipo = $row_tool['tipo_tool'];
	}
	$str_accion = '';
	if($row_tool['accion'] <> '')
		$str_accion = "&_a_=" . $row_tool['accion'];
	//~ echo"<a class=\"dropdown-item\" href=\"index.php?token=" . $_ent->getToken() . "&_c_=" . $row_tool['controlador'] . "$str_accion\"><i class=\"" . $row_tool['icono'] . "\"></i> " . $row_tool['tool_desc'] . "</a>";
	echo"
			<button class=\"card card-custom bg-light my-2 p-2 pt-1 boton_home\" controlador=\"" . $row_tool['controlador'] . "\" accion=\"" . $row_tool['accion'] . "\">
				<i class=\"" . $row_tool['icono'] . " fa-2x m-2\"></i>
					" . $row_tool['tool_desc'] . "
			</button>
	";	
}

?>


</div>



<script>
// ************************************************************************************************************** //
// ************************************************************************************************************** //
// ******************************************* Botones GRAL ************************************************ //
$('#botonera_home').on( 'click', '.boton_home', function () {

	var accion = $(this).attr('accion');
	var controlador = $(this).attr('controlador');
	if(accion == '')
	  accion = 'index';
	window.location.href = "index.php?token=<?php echo $_ent->getToken();?>&_c_=" + controlador + "&_a_=" + accion + "&randomica=" + Math.random();

} );
// ***************************************** FIN Botones GRAL ********************************************** //	
// ************************************************************************************************************** //
// ************************************************************************************************************** //
// ************************************************************************************************************** //


</script>
