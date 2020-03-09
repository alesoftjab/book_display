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
	var dataTable =  $('#usuarios-grid').DataTable( {
		serverSide: true,
		responsive: true,
		dom: 'B<"toolbar">frti',
		scrollY: '70vh',
		deferRender: true,
		scrollCollapse: false,
		scroller: {
				loadingIndicator: true
		},
		language: {"url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"},
		ajax:{
				url :"index.php", // json datasource
				data: {
					"_c_": "usuarios",
					"_a_": "getLista",
					"token": "<?php echo $_ent->getToken();?>"
				},
				type: "post",  // method  , by default get
				error: function(){  // error handling
					$(".usuarios-grid-error").html("");
					$("#usuarios-grid").append('<tbody class="usuarios-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#usuarios-grid_processing").css("display","none");
				}
		},

		"columns": [
			{ "data": "cod_member", "name": "cod_member", "title": "ID"},
			{ "data": null, "name": "Acciones", "title": "Acciones",
				"render" : function ( url, type, full) { 
										
										return '<div class="botonera" style="vertical-align:middle;text-align: center;">'+
															
															'<button type="button" class="boton_accion btn btn-light " accion="form_abm">'+
																'<i class="fas fa-edit"></i>' + ' Modificar'+
															'</button>'+
															'<button type="button" class="boton_accion btn btn-light " accion="blanquear">'+
																'<i class="fas fa-lock-open"></i>' + ' Blanquear'+
															'</button>'+

														'</div>';
				}
			},
			{ "data": "apellido", "name": "apellido", "title": "Apellido"},
			{ "data": "nombres", "name": "nombres", "title": "Nombres"},
			{ "data": "tipo_doc", "name": "tipo_doc", "title": "Tipo Doc"},
			{ "data": "nro_doc", "name": "nro_doc", "title": "Nro Doc"},
			{ "data": "profile", "name": "profile", "title": "Perfil"},
			{ "data": "cliente", "name": "cliente", "title": "Cliente"},
		],
			
		"columnDefs" : [
			{ "orderable": false, "targets": "_all" }
		],
		buttons: [
			{
				text: '<i class="fas fa-chevron-circle-left"></i> Regresar',
				className: 'btn btn-primary ',
				action: function ( e, dt, node, config ){
					window.history.back();
				}
			},
			{
				text: '<i class="fas fa-plus-square"></i> Nuevo usuario',
				className: 'btn btn-primary ',
				action: function ( e, dt, node, config ) {
					var controlador = 'usuarios';
					var accion = 'form_abm';
					new BstrapModal({
						title: 'Gestión de usuarios', 
						body: 'CARGANDO...',
						onClose: function() {
							dataTable.draw();
						}
					}).Show('index.php?token=<?php echo $_ent->getToken();?>&_c_='+ controlador + '&_a_=' + accion);
				}
			}
		]
		
						
	} );

	
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //
	// ******************************************* Botones de Acción ************************************************ //
	$('#usuarios-grid tbody').on( 'click', '.boton_accion', function () {
		var cell = dataTable.cell( this.parentNode.parentNode );
		var fila = dataTable.row( cell.index().row );
		var data = fila.data();
		var cod_member = data.cod_member;

		var accion = $(this).attr('accion');
		var modo_inicio = '';
		var modo_fin = '';
		var confirmar_accion = false;
		var titulo_ventana = '';      
		var controlador = '';

		//alert(accion + ' sobre ' + cod_member );

		switch (accion) {
		case 'form_abm':
			modo_inicio = 'av';             //AbrirVentana
			modo_fin = 'rf';								//RecargarFila
			confirmar_accion = false;      //No
			titulo_ventana = 'Modificar usuario';      //
			controlador = 'usuarios';
		break;
		case 'blanquear':
			modo_inicio = 'd';             //AbrirVentana
			confirmar_accion = true;      //No
			controlador = 'usuarios';
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
		var cod_member = data.cod_member; 

		$.ajax({
			cache: false,
			type: "post",
			url: "index.php",
			data: {
				'_c_': controlador,	
				'_a_': accion,	
				'cod_member': cod_member,	
				'filtro_unico': cod_member,	
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
		var cod_member = data.cod_member;

		new BstrapModal({
			title: titulo_ventana, 
			body: 'CARGANDO...',
			onClose: function() {
				if(modo_fin == 'rf')
				{
					dataTable.recargarFila(fila);
				}
			}
		}).Show('index.php?token=<?php echo $_ent->getToken();?>&_c_='+ controlador + '&_a_=' + accion + '&cod_member=' + cod_member);
	
	}
	
	// ************************************************************************************************************** //
	//accionLinkFila
	dataTable.accionLinkFila = function(fila, controlador, accion){

		var data = fila.data();
		var cod_member = data.cod_member;

		window.location.href = "index.php?token=<?php echo $_ent->getToken();?>&_c_=" + controlador + "&_a_=" + accion + "&randomica=" + Math.random() + "&cod_member=" + cod_member;
	
	}
	
	// ************************************************************************************************************** //
	//recargarFila
	dataTable.recargarFila = function(fila){
		var data = fila.data();
		var cod_member = data.cod_member;
		//alert(cod_member);
		$.ajax({
			cache: false,
			type: "post",
			url: "index.php",
			data: {
				'_c_': 'usuarios',	
				'_a_': 'getUno',	
				'cod_member': cod_member,	
				'filtro_unico': cod_member,	
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
	
	div.wrapper {
			height:90vh;
	}

	tr.group,
	tr.group:hover {
			background-color: #ddd !important;
	}	
	thead {
			color: lightyellow;
			background-color: gray;
	}	
	
	.toolbar {
			float: left;
	}	

	div.dataTables_length {
			display: none;
	}	
</style>



<div class="wrapper p-2">

	<table id="usuarios-grid"  cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
		<thead>
			<tr>
			</tr>
		</thead>
	</table>

</div>		

