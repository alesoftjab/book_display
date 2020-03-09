<?php
require_once 'src/general.mdl.php';
$mdlGeneral = new GeneralMdl();

$rows_tools = $mdlGeneral->getTools($_ent->member);

?>


<!-- Fixed navbar -->
<!--
<nav class="navbar navbar-expand-md fixed-top navbar-light bg-light" id="top-bar">
-->
<nav class="navbar fixed-top navbar-expand-md navbar-dark bg-dark" id="top-bar">
	<a class="navbar-brand text-nowrap" href="index.php?token=<?php echo $_ent->getToken();?>&_c_=home"><i class="fas fa-home"></i> BOOKS</a>

	<ul class="navbar-nav small initialism mr-auto text-nowrap">
		<a class="nav-link small" href="#" role="button" aria-haspopup="true" aria-expanded="false">
			<i class="navbar-text fas fa-user fa-border"> <?php echo $_ent->member;?></i>
		</a>
	</ul>


	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarTogglerDemo01">
		<ul class="navbar-nav mr-auto">

<?php
$ult_tipo = 0;
foreach ($rows_tools as $row_tool) {
	if($ult_tipo <> $row_tool['tipo_tool'])
	{
		if($ult_tipo <> 0)
		{
			echo"
				</div>
			</li>
			";
		}
		echo"
			<li class=\"nav-item dropdown text-nowrap\">
				<a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"navbarDropdown\" role=\"button\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
					<i class=\"" . $row_tool['icono_tipo'] . "\"></i> ". $row_tool['descripcion_tipo_tool'] ."
				</a>
				<div class=\"dropdown-menu\" aria-labelledby=\"navbarDropdown\">
		";
		$ult_tipo = $row_tool['tipo_tool'];
	}
	$str_accion = '';
	if($row_tool['accion'] <> '')
		$str_accion = "&_a_=" . $row_tool['accion'];
	echo"<a class=\"dropdown-item\" href=\"index.php?token=" . $_ent->getToken() . "&_c_=" . $row_tool['controlador'] . "$str_accion\"><i class=\"" . $row_tool['icono'] . "\"></i> " . $row_tool['tool_desc'] . "</a>";
	
}

?>

		</ul>
		<ul class="nav navbar-nav navbar-right">
			<li class="text-nowrap"><a href="index.php?_a_=logout"><i class="fas fa-sign-out-alt"></i></span> Salir</a></li>
		</ul>
	</div>
</nav>

