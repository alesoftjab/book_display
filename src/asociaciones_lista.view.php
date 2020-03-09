<meta name="viewport" content="width=device-width, initial-scale=1">


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.18/b-1.5.6/b-html5-1.5.6/r-2.2.2/sc-2.0.0/datatables.min.css"/>
 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/b-1.5.6/b-html5-1.5.6/r-2.2.2/sc-2.0.0/datatables.min.js"></script>

<!--
------------------------------------------------------------------------------------------------------------------------------------
-->

<!-- Font Awesome -->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">

<!-- BootstrapModal by Ale -->
<script type="text/javascript" language="javascript" src="js/BootstrapModal.js"></script>

<!-- Es un Suggest -->
<script src="js/bootstrap-typeahead.js"></script>

<!-- Es para el datepicker -->
<script
			  src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
			  integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
			  crossorigin="anonymous"></script>
			  
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  
<!-- Es para hacer Confirm -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>bootbox.setLocale('es')</script>

<!-- CSS local -->
<link href="css/site.css" rel="stylesheet">

<style>

</style>

<script type="text/javascript" language="javascript" >

$(document).ready(function() {
	var $tipo_asociacion = 1;  //1: Herramienta por miembro
	var $tipo_vista = 'T';       //1: Todo
	var groupColumn = 3;
	
	var dataTable =  $('#Asociaciones_grid').DataTable( {
		serverSide: true,
		responsive: true,
		language: {"url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"},
		ajax:{
				url :"index.php", // json datasource
				data: function ( d ) {
					d._c_ = "asociaciones";
					d._a_ = "get_lista";
					d.tipo_vista = $tipo_vista;
					d.id_gran_tipo_asoc = $tipo_asociacion;
					d.id_entidad_propietaria = $('#id_entidad_propietaria').val();
					d.filtro_txt = $('#filtro_txt').val();
					d.token= "<?php echo $_ent->getToken();?>";
				},
				type: "post",  // method  , by default get
				error: function(){  // error handling
					$(".Asociaciones_grid-error").html("");
					$("#Asociaciones_grid").append('<tbody class="Asociaciones_grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#Asociaciones_grid_processing").css("display","none");
				}
		},

		"columns": [

			{ "data": "estado", "name": "estado", "title": "Est",
				"render" : function ( url, type, full) { 
					
					var estado = full['estado'];
					var clase_ver = '';
					
					if(estado == '')
					{
						var btnMostrar =  '<button type="button" class="boton_accion btn ' + clase_ver + ' btn-sm" accion="dar">'+
																'<span aria-hidden="true"><i class="far fa-square fa-2x"></i></span>'+
															'</button>&nbsp;';
					}
					else
					{
						var btnMostrar =  '<button type="button" class="boton_accion btn ' + clase_ver + ' btn-sm" accion="quitar">'+
																'<span aria-hidden="true"><i class="far fa-check-square fa-2x"></i></span>'+
															'</button>&nbsp;';
					}
						
					return '<div class="botonera" style="vertical-align:middle;text-align: left;">'+
										btnMostrar+
									'</div>';

				}
			},
			{ "data": "id", "name": "id", "title": "ID"},	
			{ "data": "paq", "name": "paq", "title": "Nombre"},	
			{ "data": "tipo_desc", "name": "tipo_desc", "title": "Tipo"},	
		],
			
		"columnDefs" : [
			{ "orderable": false, "targets": "_all" },
			{ "visible": false, "targets": groupColumn }
		],
		dom: 'rti',
		scrollY: '70vh',
		deferRender: true,
		scrollCollapse: false,
		scroller: {
				loadingIndicator: true
		},
		buttons: [
		],

		"drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;
 
            api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        '<tr class="group font-weight-bold"><td colspan="5">'+group+'</td></tr>'
                    );
 
                    last = group;
                }
            } );		
		}		
		
						
	});

	
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //
	// ******************************************* Botones de Acción ************************************************ //
	$('#Asociaciones_grid tbody').on( 'click', '.boton_accion', function () {
		var cell = dataTable.cell( this.parentNode.parentNode );
		//Si la cell no tiene index() es porque es un groupButton y hay que subir un nivel más
		if(cell.index() === undefined)
		  cell = dataTable.cell( this.parentNode.parentNode.parentNode );
		//Si la cell SIGUE sin tener tiene index() es porque es una opción interna de un groupButton y hay que subir un nivel más
		if(cell.index() === undefined)
		  cell = dataTable.cell( this.parentNode.parentNode.parentNode.parentNode );
		var fila = dataTable.row( cell.index().row );
		var data = fila.data();
		//var id_cliente = data.id_cliente;

		var accion = $(this).attr('accion');
		var modo_inicio = '';
		var modo_fin = '';
		var confirmar_accion = false;
		var titulo_ventana = '';      
		var controlador = '';

		//alert(accion + ' sobre ' + id_cliente );

		switch (accion) {
		case 'dar':
			modo_inicio = 'd';             //Directo
			modo_fin = '';								//
			confirmar_accion = false;      //No
			accion = accion;
			controlador = 'asociaciones';
		break;
		case 'quitar':
			modo_inicio = 'd';             //Directo
			modo_fin = '';								//
			confirmar_accion = false;      //No
			accion = accion;
			controlador = 'asociaciones';
		break;
		
		default:
			alert("Acción " + accion + " no soportada");
		}
		
		// ********************************************************** //
		if(modo_inicio == 'av') //ABRIR VENTANA
		{
			if(confirmar_accion == true)
			{
				bootbox.confirm({
					message: "Está seguro?", 
					size: "small",
					callback:function(result) {
						if(result==true)
							dataTable.accionVentanaFila(fila, controlador, accion, titulo_ventana, modo_fin);
					}
					})
			}
			else
			  dataTable.accionVentanaFila(fila, controlador, accion, titulo_ventana, modo_fin);
		}
		else if(modo_inicio == 'd') //DIRECTO
		{
			if(confirmar_accion == true)
			{
				bootbox.confirm({
					message: "Está seguro?", 
					size: "small",
					callback:function(result) {
						if(result==true)
							dataTable.accionDirectaFila(fila, controlador, accion);
					}
				})
			}
			else
			  dataTable.accionDirectaFila(fila, controlador, accion);
		}
		else if(modo_inicio == 'l') //LINK
		{
			if(confirmar_accion == true)
			{
				bootbox.confirm({
					message: "Está seguro?", 
					size: "small",
					callback:function(result) {
						if(result==true)
							dataTable.accionLinkFila(fila, controlador, accion);
					}
				})
			}
			else
			  dataTable.accionLinkFila(fila, controlador, accion);			
		}
		else if(modo_inicio == 'nv') //LINK
		{
			if(confirmar_accion == true)
			{
				bootbox.confirm({
					message: "Está seguro?", 
					size: "small",
					callback:function(result) {
						if(result==true)
							dataTable.accionNuevaVentana(fila, controlador, accion);
					}
				})
			}
			else
			  dataTable.accionNuevaVentana(fila, controlador, accion);			
		}
	} );
	// ***************************************** FIN Botones de Acción ********************************************** //
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //
			
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //

	//accionDirectaFila
	dataTable.accionDirectaFila = function(fila, controlador, accion){

		var data = fila.data();
		var id_gran_tipo_asoc = $tipo_asociacion;
		var tipo_vista = $tipo_vista;
		var id_entidad_propietaria = $('#id_entidad_propietaria').val();
		var id_entidad_poseida = data.id;

		$.ajax({
			cache: false,
			type: "post",
			url: "index.php",
			data: {
				'_c_': controlador,	
				'_a_': accion,	
				'tipo_vista': tipo_vista,
				'id_gran_tipo_asoc': id_gran_tipo_asoc,
				'id_entidad_propietaria': id_entidad_propietaria,
				'id_entidad_poseida': id_entidad_poseida,
				'randomica': Math.random(),			
				"token": "<?php echo $_ent->getToken();?>"
			},
			success: function(data){
				dataTable.row( fila ).invalidate();
				var respuesta = $.parseJSON(data);
				if(respuesta.estado!=true)
				{
					alert('Error: ' + respuesta.descripcion);	
				}
				
				//Si vino una fila enviarla a la tabla, de lo contrario eliminar la fila actual
				if(respuesta.row)
					dataTable.row( fila ).data(respuesta.row);
				else
				{
					fila.remove().draw();
				}

			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(xhr.status);
				alert(thrownError);
			}
		});			
	}
	
	// ************************************************************************************************************** //
	//accionVentanaFila
	dataTable.accionVentanaFila = function(fila, controlador, accion, titulo_ventana, modo_fin){

		var data = fila.data();
		//~ var id_cliente = data.id_cliente;

		new BstrapModal({
			title: titulo_ventana, 
			body: 'CARGANDO...',
			onClose: function() {
				if(modo_fin == 'rf')
				{
					dataTable.recargarFila(fila);
				}
			}
		}).Show('index.php?token=<?php echo $_ent->getToken();?>&_c_='+ controlador + '&_a_=' + accion + '&id_cliente=' + id_cliente);
	
	}
	
	// ************************************************************************************************************** //
	//accionLinkFila
	dataTable.accionLinkFila = function(fila, controlador, accion){

		var data = fila.data();
		//~ var id_cliente = data.id_cliente;

		window.location.href = "index.php?token=<?php echo $_ent->getToken();?>&_c_=" + controlador + "&_a_=" + accion + "&randomica=" + Math.random() + "&id_cliente=" + id_cliente;
	
	}

	// ************************************************************************************************************** //
	//nuevaVentana
	dataTable.accionNuevaVentana = function(fila, controlador, accion){

		var data = fila.data();
		//~ var id_cliente = data.id_cliente;

		window.open('index.php?token=<?php echo $_ent->getToken();?>&_c_=' + controlador + '&_a_=' + accion + '&randomica=' + Math.random() + '&id_cliente=' + id_cliente);
	
	}
	
	// ************************************************************************************************************** //
	//recargarFila
	dataTable.recargarFila = function(fila){
		var data = fila.data();
		//~ var id_cliente = data.id_cliente;
		//alert(id_cliente);
		$.ajax({
			cache: false,
			type: "post",
			url: "index.php",
			data: {
				'_c_': 'Asociaciones',	
				'_a_': 'getUno',	
				//~ 'id_cliente': id_cliente,	
				//~ 'filtro_unico': id_cliente,	
				'randomica': Math.random(),			
				"token": "<?php echo $_ent->getToken();?>"
			},
			success: function(data){
				dataTable.row( fila ).invalidate();
				var respuesta = $.parseJSON(data);
				dataTable.row( fila ).data(respuesta);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(xhr.status);
				alert(thrownError);
			}
		});				
	}
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //
	// ******************************************* Botones GRAL ************************************************ //
	$('#botonera_gral').on( 'click', '.boton_gral', function () {

		var accion_gral = $(this).attr('accion_gral');
		var modo_inicio = '';
		var modo_fin = '';
		var confirmar_accion = false;
		var titulo_ventana = '';      
		var controlador = '';
		
		var accion = accion_gral;
		
		switch (accion_gral) {
		case 'regresar':
			window.history.back();
		break;
		}
	} );
	// ***************************************** FIN Botones GRAL ********************************************** //	
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //
	// ******************************************* Botones TIPO VISTA ************************************************ //
	$('#botonera_tipo_vista').on( 'click', '.boton_tipo_vista', function () {
		$tipo_vista = $(this).attr('tipo_vista');
		$('#dropdownTipoVista').html(this.text);
		dataTable.draw();	
	} );

	$('#botonera_tipo_asociacion').on( 'click', '.boton_tipo_asociacion', function () {
		$tipo_asociacion = $(this).attr('tipo_asociacion');
		$('#dropdownTipoAsociacion').html(this.text);

		$('#id_entidad_propietaria').val('');
		$('#id_entidad_propietaria_search').val('');

		dataTable.draw();	
	} );

	//Botón ACEPTAR
	$('#submitForm').click(function (e) {
		e.preventDefault();
		dataTable.draw();
	});

	//Evento de tecleo en el SUGGEST
	$('#id_entidad_propietaria_search').typeahead({
		ajax: {
			url: "index.php?token=<?php echo $_ent->getToken(); ?>&_c_=asociaciones&_a_=getListaPropietarios",
			timeout: 30,
			displayField: "paq",
			triggerLength: 3,
			method: "get",
			loadingClass: "loading-circle",
			preDispatch: function (query) {
				//~ showLoadingMask(true);
				var id_gran_tipo_asoc = $tipo_asociacion;
				return {
					search: query,
					id_gran_tipo_asoc: id_gran_tipo_asoc
				}
			},
		},
		items: 200,
		displayField: 'paq',
		valueField: 'id',	
		item: '<li><a href="#"></a></li>',
		onSelect: function(item){
			$('#id_entidad_propietaria').val(item.value);
			dataTable.draw();
		}
	});


	// ************************************************************************************************************** //
	// ************************************************************************************************************** //

	$('[data-toggle="tooltip"]').tooltip();  

}
);


</script>
<style>
	.navbar {
		min-height: 5vh;
		max-height: 5vh;
		height: 5vh;
	}
	.date_range_filter {
		position: relative; z-index:100;
	}
	div.header {
			margin: 10px auto;
			line-height:50px;
			max-width:760px;
	}
	div.wrapper {
			height:70vh;
	}

	tr.group,
	tr.group:hover {
			background-color: #ddd !important;
	}	
	
	.toolbar {
			float: left;
	}	
	
</style>


<form action="" class="form-inline">
	<div id='botonera_gral' class="col-md-1 m-0 text-left m-2">
		<button type="button" class="boton_gral btn btn-outline-dark btn-sm" accion_gral="regresar">
			<span class="glyphicon glyphicon-modal-window" aria-hidden="true"><i class="fas fa-arrow-circle-left"></i> Regresar</span>
		</button>
	</div>
	
	<div id='botonera_tipo_vista' class="col-md-1 m-0 text-left m-2">
		<button type="button" class="dropdown-toggle btn btn-secondary btn-sm" id="dropdownTipoVista" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			Ver Todo
		</button>
		<div class="dropdown-menu" aria-labelledby="dropdownTipoVista">
			<a class="dropdown-item boton_tipo_vista" href="#" tipo_vista="T">Ver Todo</a>
			<a class="dropdown-item boton_tipo_vista" href="#" tipo_vista="A">Ver lo Asociado</a>
		</div>
	</div>

	<div id='botonera_tipo_asociacion' class="col-md-2 m-0 text-left m-2">
		<button type="button" class="dropdown-toggle btn btn-secondary btn-sm" id="dropdownTipoAsociacion" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			Herramienta por Miembro
		</button>
		<div class="dropdown-menu" aria-labelledby="dropdownTipoAsociacion">
<?php
	$tipos = '';
	foreach ($tipos_asociaciones as $valor) {
		$tipos .="<a class=\"dropdown-item boton_tipo_asociacion\" href=\"#\" tipo_asociacion=\"" . $valor['id_gran_tipo_asoc'] . "\">" . $valor['nombre'] . "</a>";
	}
	echo"$tipos";
?>					
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group">
			<input required id="id_entidad_propietaria_search" type="text" class="form-control" placeholder="Propietario..." autocomplete="off" />
			<div class="input-group-append">
				<input required readonly name="id_entidad_propietaria" id="id_entidad_propietaria" class="input-group-text " size=10 value="<?php echo $_ent->member;?>">
			</div>
		</div>
	</div>

	<div class="col-md-2 m-0 text-left m-2">
		<input id="filtro_txt" type="text" class="form-control" placeholder="Texto a Filtrar..." autocomplete="off" />
	</div>

	<div class="col-md-1 m-0 text-right m-2">
		<button type=button class="btn btn-primary" id=submitForm>Aceptar</button>
	</div>
</form>


<div class="wrapper ml-2 mr-2 ">

	<table id="Asociaciones_grid"  cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
		<thead>
			<tr>
			</tr>
		</thead>
	</table>

</div>		

<script>





</script>
