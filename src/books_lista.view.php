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

<?php
	//establezco variable php que luego usare para mostrar u ocultar botones.
	//~ if (get_tiene_tool ($_ent->member,'books','admin')==true)
		$permitir_accion=1;
?>

<script type="text/javascript" language="javascript" >

$(document).ready(function() {

	var dataTable =  $('#books-grid').DataTable( {
		serverSide: true,
		responsive: true,
		language: {"url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"},
		ajax:{
				url :"index.php", // json datasource
				data: function ( d ) {
					d._c_ = "books";
					d._a_ = "get_lista";
					d.filtro_fecha = $('#filtro_fecha').val();
					d.filtro_txt = $('#filtro_txt').val();
					d.token= "<?php echo $_ent->getToken();?>";
				},
				type: "post",  // method  , by default get
				error: function(){  // error handling
					$(".books-grid-error").html("");
					$("#books-grid").append('<tbody class="books-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#books-grid_processing").css("display","none");
				}
		},

		"columns": [

			{ "data": "id_book_ver", "name": "id_book_ver", "title": "ID"}, 
			{ "data": null, "name": "Acciones", "title": "Acciones",
				"render" : function ( url, type, full) { 
					
					var permitir_accion = '<?php echo $permitir_accion; ?>';
					var boton_modif = '';
									
					if(permitir_accion==1)
					{
						boton_modif ='<button type="button" class="boton_accion btn btn-outline-primary btn-sm" accion="modificar_libro">'+
														'<span aria-hidden="true"><i class="fas fa-pencil-alt"></i> Modificar</span>'+
													'</button>&nbsp;';

						boton_paginas ='<button type="button" class="boton_accion btn btn-outline-primary btn-sm" accion="editar_paginas">'+
														'<span aria-hidden="true"><i class="fas fa-images"></i> Páginas</span>'+
													'</button>&nbsp;';

						boton_exportar ='<button type="button" class="boton_accion btn btn-outline-primary btn-sm" accion="exportar">'+
														'<span aria-hidden="true"><i class="fas fa-upload"></i> Publicar</span>'+
													'</button>&nbsp;';

						boton_importar_precios ='<button type="button" class="boton_accion btn btn-outline-primary btn-sm" accion="importar_precios">'+
														'<span aria-hidden="true"><i class="fas fa-file-csv"></i> Importar $</span>'+
													'</button>&nbsp;';

						boton_exportar_precios_xls ='<button type="button" class="boton_accion btn btn-outline-primary btn-sm" accion="exportar_precios_xls">'+
														'<span aria-hidden="true"><i class="fas fa-file-excel"></i> Exportar $</span>'+
													'</button>&nbsp;';

					}

					return '<div class="botonera" style="vertical-align:middle;text-align: left;">'+
										
										//~ '<button type="button" class="boton_accion btn btn-outline-primary btn-sm" accion="ver_libro">'+
											//~ '<span aria-hidden="true"><i class="fas fa-search "></i> Ver</span>'+
										//~ '</button>&nbsp;'+

										boton_modif+
										
										boton_exportar+
										
										boton_importar_precios+
										
										boton_exportar_precios_xls+

										boton_paginas+
										
									'</div>';

				}
			},
			{ "data": "titulo", "name": "titulo", "title": "Título"},	
			{ "data": "fecha", "name": "fecha", "title": "Fecha"},	
			//~ { "data": "url_image_base", "name": "url_image_base", "title": "URL Base imágenes"},	
			{ "data": "pageHeight", "name": "pageHeight", "title": "Alto"},	
			{ "data": "pageWidth", "name": "pageWidth", "title": "Ancho"},	
			{ "data": "member", "name": "member", "title": "Usuario"},	
			{ "data": "cliente", "name": "cliente", "title": "Cliente"},	
			{ "data": "momento", "name": "momento", "title": "Momento"},	
			{ "data": "book_topics", "name": "book_topics", "title": "Tópicos"},	
			{ "data": "home_url", "name": "home_url", "title": "Home Url"},	

		],
			
		"columnDefs" : [
			{ "orderable": false, "targets": "_all" },
			{ className: 'dt-body-right p-1', "targets": [4,5,6] },
			{ className: 'dt-head-right', "targets": [4,5,6] }
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
	} );

	
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //
	// ******************************************* Botones de Acción ************************************************ //
	$('#books-grid tbody').on( 'click', '.boton_accion', function () {
		var cell = dataTable.cell( this.parentNode.parentNode );
		//Si la cell no tiene index() es porque es un groupButton y hay que subir un nivel más
		if(cell.index() === undefined)
		  cell = dataTable.cell( this.parentNode.parentNode.parentNode );
		//Si la cell SIGUE sin tener tiene index() es porque es una opción interna de un groupButton y hay que subir un nivel más
		if(cell.index() === undefined)
		  cell = dataTable.cell( this.parentNode.parentNode.parentNode.parentNode );
		var fila = dataTable.row( cell.index().row );
		var data = fila.data();

		var accion = $(this).attr('accion');
		var modo_inicio = '';
		var modo_fin = '';
		var confirmar_accion = false;
		var titulo_ventana = '';      
		var controlador = '';


		switch (accion) {
		case 'ver_libro':
			modo_inicio = 'av';             //Link
			modo_fin = '';								//
			confirmar_accion = false;      //No
			accion = 'form_ver';
			titulo_ventana = 'Ver libro';      //
			controlador = 'books';
		break;
		case 'modificar_libro':
			modo_inicio = 'av';             //Link
			modo_fin = 'rf';								//
			confirmar_accion = false;      //No
			accion = 'form_abm';
			titulo_ventana = 'Modificar libro';      //
			controlador = 'books';
		break;		
		case 'editar_paginas':
			modo_inicio = 'l';             //Link
			modo_fin = '';								//
			confirmar_accion = false;      //No
			accion = 'editar_paginas';
			titulo_ventana = 'Editar Páginas';      //
			controlador = 'books';
		break;		
		case 'importar_precios':
			modo_inicio = 'av';             //Link
			modo_fin = '';								//
			confirmar_accion = false;      //No
			accion = 'importar_precios';
			titulo_ventana = 'importar Precios';      //
			controlador = 'books';
		break;		
		case 'exportar_precios_xls':
			modo_inicio = 'nv';             //Link
			modo_fin = '';								//
			confirmar_accion = false;      //No
			accion = 'exportar_precios_xls';
			titulo_ventana = 'Exportar precios';      //
			controlador = 'books';
		break;		
		case 'exportar':
			modo_inicio = 'nv';             //Link
			modo_fin = '';								//
			confirmar_accion = false;      //No
			accion = 'exportar';
			titulo_ventana = 'Exportar';      //
			controlador = 'books';
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
		var id_book = data.id_book; 

		$.ajax({
			cache: false,
			type: "post",
			url: "index.php",
			data: {
				'_c_': controlador,	
				'_a_': accion,	
				'id_book': id_book,	
				'filtro_unico': id_book,	
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
		var id_book = data.id_book;

		var ver = '';
		if(accion=='form_ver')
		{
			accion = 'form_abm'
			ver = '&modo=ver';
		}

		new BstrapModal({
			title: titulo_ventana, 
			body: 'CARGANDO...',
			onClose: function() {
				if(modo_fin == 'rf')
				{
					dataTable.recargarFila(fila);
				}
			}
		}).Show('index.php?token=<?php echo $_ent->getToken();?>&_c_='+ controlador + '&_a_=' + accion + '&id_book=' + id_book + ver);
	
	}
	
	// ************************************************************************************************************** //
	//accionLinkFila
	dataTable.accionLinkFila = function(fila, controlador, accion){

		var data = fila.data();
		var id_book = data.id_book;

		window.location.href = "index.php?token=<?php echo $_ent->getToken();?>&_c_=" + controlador + "&_a_=" + accion + "&randomica=" + Math.random() + "&id_book=" + id_book;
	
	}

	// ************************************************************************************************************** //
	//nuevaVentana
	dataTable.accionNuevaVentana = function(fila, controlador, accion){

		var data = fila.data();
		var id_book = data.id_book;

		window.open('index.php?token=<?php echo $_ent->getToken();?>&_c_=' + controlador + '&_a_=' + accion + '&randomica=' + Math.random() + '&id_book=' + id_book);
	
	}
	
	// ************************************************************************************************************** //
	//recargarFila
	dataTable.recargarFila = function(fila){
		var data = fila.data();
		var id_book = data.id_book;
		//alert(id_book);
		$.ajax({
			cache: false,
			type: "post",
			url: "index.php",
			data: {
				'_c_': 'books',	
				'_a_': 'getUno',	
				'id_book': id_book,	
				'filtro_unico': id_book,	
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
		case 'nuevo_libro':
			var controlador = 'books';
			var accion = 'form_abm';
			new BstrapModal({
				title: 'Nuevo Libro', 
				body: 'CARGANDO...',
				onClose: function() {
					dataTable.draw();
				}
			}).Show('index.php?token=<?php echo $_ent->getToken();?>&_c_='+ controlador + '&_a_=' + accion);
		break;
		case 'imprimir':
			var controlador = 'books';
			var accion = 'imprimir';
			window.open('index.php?token=<?php echo $_ent->getToken();?>&_c_=' + controlador + '&_a_=' + accion);
		break;
		}
	} );
	// ***************************************** FIN Botones GRAL ********************************************** //	
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //
	// ************************************************************************************************************** //
  $("#filtro_fecha").datepicker({
    "onSelect": function(date) {
      minDateFilter = new Date(date).getTime();
      dataTable.draw();
    },
    dateFormat: 'yy-mm-dd' 
  }).keyup(function() {
    minDateFilter = new Date(this.value).getTime();
    dataTable.draw();
  });

  var hoy = new Date();
  var default_hasta = new Date(hoy - (1000 * 60 * 60 * 24 * 1));
	var default_desde = new Date(default_hasta - (1000 * 60 * 60 * 24 * 6)); //7 días

  //Inicializar el datetimepicker de fecha_xls_desde
	$("#fecha_xls_desde").datepicker({
	  "onSelect": function(date) {
	  },
	  dateFormat: 'yy-mm-dd' 
	}).keyup(function() {
	}).datepicker("setDate", default_desde);


	//Inicializar el datetimepicker de fecha_xls_hasta
	$("#fecha_xls_hasta").datepicker({
	  "onSelect": function(date) {
	  },
	  dateFormat: 'yy-mm-dd' 
	}).keyup(function() {
	}).datepicker("setDate", default_hasta);


  //Botón ACEPTAR
	$('#submitSearch').click(function (e) {
		e.preventDefault();
		dataTable.draw();
	});

	// ************************************************************************************************************** //
	// ************************************************************************************************************** //

	$('[data-toggle="tooltip"]').tooltip();  
}
);


</script>
<style>


	.date_range_filter {
		position: relative; z-index:100;
	}
	div.header {
			margin: 10px auto;
			line-height:50px;
			max-width:760px;
	}
	div.wrapper {
			height:90vh;
	}

	tr.group,
	tr.group:hover {
			background-color: #ddd !important;
	}	
	
	.toolbar {
			float: left;
	}	

	.tableList tbody tr.groupTR {
			border: groove medium #BFBFBF;
			font-weight: bold;
	}
	.tableList tbody tr.groupTR td.groupTitle {
			background-color: #BFBFBF;
	}
	.tableList tbody tr.groupTR td.groupTD {
			background-color: #BFBFBF;
			white-space: normal;
	}
	.tableList tbody tr.groupTR td span.groupInfo {
			padding-left: 10px;
			color: blue;
			font-weight: normal;
	}	
	.tableList tbody tr.groupGT {
			border: groove medium #555;
			font-weight: bold;
			color: white;
	}
	.tableList tbody tr.groupGT td.groupTitle {
			background-color: #555;
			color: white;
	}
	.tableList tbody tr.groupGT td.groupTD {
			background-color: #555;
			white-space: normal;
			color: white;
	}
	.tableList tbody tr.groupGT td span.groupInfo {
			padding-left: 10px;
			color: blue;
			font-weight: normal;
	}	
	.tableList tbody tr.groupPP {
			border: groove medium #DBE7EB;
			font-weight: bold;
			color: black;
	}
	.tableList tbody tr.groupPP td.groupTitle {
			background-color: #DBE7EB;
			color: black;
	}
	.tableList tbody tr.groupPP td.groupTD {
			background-color: #DBE7EB;
			white-space: normal;
			color: black;
	}
	.tableList tbody tr.groupPP td span.groupInfo {
			padding-left: 10px;
			color: black;
			font-weight: normal;
	}	
</style>


<div id='botonera_gral' class="botonera_gral row ml-2 mr-2" >

	<div class="col-md-5 m-0 text-left m-2">
																			
		<button type="button" class="boton_gral btn btn-outline-dark btn-sm" accion_gral="regresar">
			<span class="glyphicon glyphicon-modal-window" aria-hidden="true"><i class="fas fa-arrow-circle-left"></i> Regresar</span>
		</button>

<?php
	if ($permitir_accion==1)
	{
		echo"
		<button type='button' class='boton_gral btn btn-outline-primary btn-sm' accion_gral='nuevo_libro'>
			<span class='glyphicon glyphicon-modal-window' aria-hidden='true'><i class='fas fa-plus-circle '></i> Nuevo Libro</span>
		</button>

	";
	}
?>
		
	</div>

	<div class="col m-0 text-right m-2">
  	<input class="date_range_filter date" placeholder="Fecha" type="text" id="filtro_fecha" />
	
		<input id="filtro_txt" type="text" class="" placeholder="Texto a Filtrar..." autocomplete="off" />
	
		<button type=button class="btn btn-primary" id=submitSearch>Aceptar</button>
	</div>

</div>

<div class="wrapper ml-2 mr-2 ">

	<table id="books-grid"  cellpadding="0" cellspacing="0" border="0" class="display tableList" width="100%">
		<thead class="thead-dark">
			<tr>
			</tr>
		</thead>
	</table>

</div>	



