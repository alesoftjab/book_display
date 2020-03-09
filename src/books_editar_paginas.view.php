<script src="https://unpkg.com/konva@2.4.2/konva.min.js"></script>
<script src="js/book_display.js"></script>
<script src="js/shop_cart.js"></script>
<script src="js/logger.js"></script>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, user-scalable=no">

<title>Book Display</title>

<style>

</style>

<div class="row py-1 flex-shrink-0" style="background-color: darkGray;">
	<div class="col">
		<div id="editor" >
			<select name="mode" id="mode" onchange="cambiarModo(this.value);">
				<option value=v>Vista</option>
				<option value=e>Edición</option>
			</select>
			
			<button id="nuevo" name="nuevo" value="nuevo" onclick="iniciarNuevoShape();">Nuevo</button>
			<button id="nuevo" name="nuevo" value="nuevo" onclick="setBookData();">Grabar</button>
			<button id="nuevo" name="nuevo" value="nuevo" onclick="viewShopCart();"><span aria-hidden="true"><i class="fas fa-shopping-cart "></i> Ver Carro</span></button>
			<button id="nuevo" name="nuevo" value="nuevo" onclick="shareLink();"><span aria-hidden="true"><i class="fas fa-share-alt "></i> Compartir</span></button>
		</div>
	</div>
</div>
<div id="div_container"  class="row flex-fill d-flex justify-content-start overflow-auto" style="background-color: DimGray;">
			
			
</div>			
			
<!--
<div id="div_container" style="background-color:gray; min-width: 400px; min-height: 400px"></div>
-->

<script>
	
$(document).ready(function() {
	var logger;
	var shopCart;
	var bookDisplay;
	var id_book = '<?php echo $id_book;?>';

	function getURLParam(name){
		 if(name=(new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search))
				return decodeURIComponent(name[1]);
	}
	
	$('#mode').val('e');
	
	// ************************************************************************** //
	function getData () {
		$.ajax({
			cache: false,
			type: 'post',
			url: "index.php?_c_=books&_a_=get_book_data&token=<?php echo $_ent->getToken();?>&id_book=" + id_book,
			success: function (response) {
				var respuesta = $.parseJSON(response);
				
				//Si hubo errores de lógica de negocio mostrarlos
				if(respuesta.estado!=true)
				{
					alert('Error: ' + respuesta.descripcion);	
				}
				else  //Sin errores de lógica de negocio
				{
					var data = respuesta.bookData;
					
					// ********************************************** //
					//Crear un objeto logger
					logger = new Logger({
						id_book: data.id_book, 
						vendorPhone: '',
						urlLog: 'https://192.168.1.7/book_display/page_log.php'
					});
					// **************************************************************** //

					getShopCartData (logger);
					
					var page = 1*getURLParam("page");
					if(page != 0)
					  page = page-1;
					// ********************************************** //
					//Crear un objeto visualization con los datos que vinieron desde el servidor
					bookDisplay = new BookDisplay({
						mode: 'e',
						page: page,
						container: document.getElementById('div_container'),
						data: data, 
						onClickedArea: function(codigo){
							//~ alert('Código ' + codigo + ' seleccionado');
							shopCart.showAddItemDialog(codigo);
						},
						logger: logger
					});
					
					// **************************************************************** //
					cambiarModo = function(par_modo){
						bookDisplay.changeMode(par_modo);	
					};		

					iniciarNuevoShape = function(){
						bookDisplay.startShapeGeneration();
					};		

					viewShopCart = function(){
						shopCart.viewShopCart();
					};		

					shareLink = function(){
						shopCart.shareLink();
					};		

					setBookData = function(){
						$.ajax({
							cache: false,
							type: 'post',
							url: "index.php",
							data: {
								'_c_': 'books',	
								'_a_': 'set_book_data',	
								'id_book': id_book,
								'bookData': JSON.stringify(bookDisplay.data),
								'randomica': Math.random(),			
								"token": "<?php echo $_ent->getToken();?>"
							},
							success: function (response) {					
								var respuesta = $.parseJSON(response);
								
								//Si hubo errores de lógica de negocio mostrarlos
								if(respuesta.estado!=true)
								{
									alert('Error: ' + respuesta.descripcion);	
								}
								else  //Sin errores de lógica de negocio
								{
								}
							},
							error: function (xhr, ajaxOptions, thrownError) {
								alert(xhr.status);
								alert(thrownError);
							}								
						});
					};
					// **************************************************************** //
					
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(xhr.status);
				alert(thrownError);
			}
		});
		
	}
	
	getData();
	
	// ****************************************************************************************** //
	function getShopCartData (logger) {
		var vendor_phone = getURLParam("vendor_phone");

		$.ajax({
			cache: false,
			type: 'post',
			url: "index.php",
			data: {
				'_c_': 'shop_carts',	
				'_a_': 'get_shop_cart_data',	
				'id_book': id_book,
				'randomica': Math.random(),			
				"token": "<?php echo $_ent->getToken();?>",
				<?php
				if($_REQUEST['vendor_phone']<>'')
				  echo"'vendorPhone': " . $_REQUEST['vendor_phone'] . ",";
				?>
			},

			success: function (response) {
				var respuesta = $.parseJSON(response);
				
				//Si hubo errores de lógica de negocio mostrarlos
				if(respuesta.estado!=true)
				{
					alert('Error: ' + respuesta.descripcion);	
				}
				else  //Sin errores de lógica de negocio
				{
					var scdata = respuesta.shopCartData;
//~ console.log(scdata);					

					// ********************************************** //
					//Crear un objeto visualization con los datos que vinieron desde el servidor
					shopCart = new ShopCart({
						data: scdata, 
						vendorPhone: vendor_phone,
						logger: logger
					});
					// **************************************************************** //
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(xhr.status);
				alert(thrownError);
			}
		});
		
	}	
	
	//~ getShopCartData();
	// ****************************************************************************************** //
	
	// Listen for resize changes
	window.addEventListener("resize", function() {
		// Get screen size (inner/outerWidth, inner/outerHeight)
		//~ bookDisplay.reScale();	
					//Crear un objeto visualization con los datos que vinieron desde el servidor
					getData();
		
	}, false);

});
</script>
