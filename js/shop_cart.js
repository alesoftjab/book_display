// ************************************************************************************************************************************* //
// ************************************************************************************************************************************* //
ShopCart = function (){			
	var that = this;
	
	var options = arguments[0] || {};
	data = options.data || []; 
	vendorPhone = options.vendorPhone || ''; 
	shopCartItems = [];
	that.logger = options.logger || null;

	pasoTemplates = options.pasoTemplates || 'templates';
	
	// *********************** Función que abre un diálogo para agregar un ítem *************************** //
	//Recibe como parámetro el string del código 	
	this.showAddItemDialog = function(parCode){
		
		var cmbProducts = $("#__cmbProducts");

		//Vaciar el combo
		cmbProducts.find('option').remove().end();
		
//~ console.log(data);		
		//Filtrar la lista de items buscando el codigo parCode
		var itemsCoincidentes = data.filter(row => row['codigo_parte'] == parCode);
//~ console.log(itemsCoincidentes);		

		//Rellenar combo
		$.each(itemsCoincidentes, function() {
			cmbProducts.append($("<option />").val(this.codigo_completo).text(this.descripcion));
		});
		
		//Abrir el modal
		$('#addItemModal').modal('show');

		//Seleccionar el primer elemento del combo
		cmbProducts.selectedIndex = 0;
		
		$("#__cantidad").val(1);
		
		that.actualizarSeleccion(); 
	
	};
	// ********************* FIN Función que abre un diálogo para agregar un ítem ************************* //
	
	// ************************** Función que muestra el contenido del carro ****************************** //
	this.viewShopCart = function(parCode){
		//Abrir el modal
		$('#viewShopCart').modal('show');
		
		//Esta única línea renderiza todos los ítems usando map
		$('#shopCartList').html(shopCartItems.map(shopCartItem).join(''));		
		
		// **************** Eventos a ejecutar cuando cambian los inputs de los items ******** //
		// ************** CAMBIO DE CANTIDAD
		$("#shopCartList .input_cantidad").on('input',function(e){
			var itemIndex = $(this).attr("itemIndex");
			itemCantidad = $('#__cantidad' + itemIndex).val();
//~ console.log('camb ' + itemIndex);
			//Validación básica
			if(1*itemCantidad < 0)
			  itemCantidad = 0;
			$('#__cantidad' + itemIndex).val(itemCantidad);
			
			//Cálculo de subtotal
			itemPrecio = $('#__precio_unitario' + itemIndex).val();
			itemSubtotal = itemCantidad * itemPrecio;
			$('#txtSubtotal' + itemIndex).val(itemSubtotal.toFixed(2));
			shopCartItems[itemIndex].cantidad = itemCantidad;
//~ console.log(shopCartItems);
			
			that.calcularTotales();
		});
		
		// ************** ELIMINACIÓN
		$("#shopCartList .btnEliminar").on('click',function(e){
			var itemIndex = $(this).attr("itemIndex");
//~ console.log('elim ' + itemIndex);
			
			$('#shopCartItem' + itemIndex).remove();
			that.calcularTotales();
			shopCartItems.splice(itemIndex, 1);
			//re-renderizar ítems
			//~ $('#shopCartList').html(shopCartItems.map(shopCartItem).join(''));		
			that.viewShopCart();
//~ console.log(shopCartItems);
		});
		
		// ************** FINALIZAR COMPRA
		$("#__submitShopCart").off("click");  //Esta línea elimina los eventos click anteirores.
		$("#__submitShopCart").on('click',function(e){
			var carroTextificado = that.textificarCarro();
			//~ console.log(carroTextificado);
			//~ BstrapModal.Close();


			var isMobile = {
				Android: function() {
				return navigator.userAgent.match(/Android/i);
				},
				BlackBerry: function() {
				return navigator.userAgent.match(/BlackBerry/i);
				},
				iOS: function() {
				return navigator.userAgent.match(/iPhone|iPad|iPod/i);
				},
				Opera: function() {
				return navigator.userAgent.match(/Opera Mini/i);
				},
				Windows: function() {
				return navigator.userAgent.match(/IEMobile/i);
				},
				any: function() {
				return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
				}
			};

			var text = carroTextificado;
			var phone = vendorPhone;
			var message = encodeURIComponent(text);

			if( isMobile.any() ) {
				//mobile device
				var whatsapp_API_url = "whatsapp://send";
			} else {
				//desktop
				var whatsapp_API_url = "https://web.whatsapp.com/send";
			}
//~ console.log(whatsapp_API_url+'?phone=' + phone + '&text=' + message);			
			window.open(whatsapp_API_url+'?phone=' + phone + '&text=' + message );

			//Loggear
			if(that.logger != null){
				that.logger.registrarVenta(shopCartItems);
			}

			
		});
		
		// ************** FIN Eventos a ejecutar cuando cambian los inputs de los items ****** //
		
		that.calcularTotales();

	};
	// ************************ FIN Función que muestra el contenido del carro **************************** //
	
	// ************************** Función que comparte el link por WA ****************************** //
	this.shareLink = function(parCode){
		//Abrir el modal
		$('#shareLink').modal('show');
		


		
		// ************** ENVIAR LINK
		$("#__submitShareLink").on('click',function(e){
			var urlTextificado = window.location.href;
			//~ BstrapModal.Close();


		var isMobile = {
			Android: function() {
			return navigator.userAgent.match(/Android/i);
			},
			BlackBerry: function() {
			return navigator.userAgent.match(/BlackBerry/i);
			},
			iOS: function() {
			return navigator.userAgent.match(/iPhone|iPad|iPod/i);
			},
			Opera: function() {
			return navigator.userAgent.match(/Opera Mini/i);
			},
			Windows: function() {
			return navigator.userAgent.match(/IEMobile/i);
			},
			any: function() {
			return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
			}
		};

		var text = urlTextificado;
		var vendor_phone = $('#__contact_phone').val();
		var message = encodeURIComponent(text + '&vendor_phone=' + vendor_phone);

		if( isMobile.any() ) {
			//mobile device
			var whatsapp_API_url = "whatsapp://send";
		} else {
			//desktop
			var whatsapp_API_url = "https://web.whatsapp.com/send";
		}
		//~ $(this).attr( 'href', whatsapp_API_url+'?phone=' + phone + '&text=' + message );
		window.open(whatsapp_API_url+'?&text=' + message );
			
		});
		
	// ************************ FIN Función que comparte el link por WA *********************************** //
		
		that.calcularTotales();

	};
	// ************************ FIN Función que muestra el contenido del carro **************************** //

	// *************************** Función que representa al carro como texto ***************************** //
	this.textificarCarro = function(){
		var texto = 'Hola. Ya he decidido qué productos quiero pedir. Acá va el detalle: ' + '//' + String.fromCharCode(13);
		$.each(shopCartItems, function() {
			texto = texto + this.cantidad + ' x ' + 
							this.codigo + ' - ' +
							this.nombre + ' - ' +
							' $' + this.subtotal + '//' + String.fromCharCode(11) + String.fromCharCode(13);
		});
		
		texto = texto + "--------------------------" + '//' + String.fromCharCode(11) + String.fromCharCode(13);
		texto = texto + "TOTAL GENERAL: $ " + $('#__precio_total').val();
		return texto;
	};
	// ************************* FIN Función que representa al carro como texto *************************** //

	// ******************************** Función que muestra los totales *********************************** //
	this.calcularTotales = function(){
		var cantidadTotal = 0;
		var precioTotal = 0;
		$("#shopCartList .shopCartItem").each(function(index, obj) {
			itemIndex = $(this).attr("itemIndex");
//~ console.log(itemIndex);
			
			itemCantidad = $('#__cantidad' + itemIndex).val();
			cantidadTotal += 1*itemCantidad;			
			itemPrecio = $('#__precio_unitario' + itemIndex).val();
			itemSubtotal = itemCantidad * itemPrecio;
			precioTotal += 1*itemSubtotal;
		});
		$('#__cantidad_total').val(cantidadTotal.toFixed(2));
		$('#__precio_total').val(precioTotal.toFixed(2));
	};
	// ****************************** FIN Función que muestra los totales ********************************* //

	// ************************* Función que obtiene los ítems desde la vista ***************************** //
	//~ this.getShopCartItemsFromView = function(){
		//~ shopCartItems = [];
		//~ $("#shopCartList .shopCartItem").each(function(index, obj) {
			//~ itemIndex = $(this).attr("itemIndex");
//~ console.log(itemIndex);
			
			//~ itemCantidad = $('#__cantidad' + itemIndex).val();
			//~ cantidadTotal += 1*itemCantidad;			
			//~ itemPrecio = $('#__precio_unitario' + itemIndex).val();
			//~ itemSubtotal = itemCantidad * itemPrecio;
			//~ precioTotal += 1*itemSubtotal;
		//~ });
		//~ $('#__cantidad_total').val(cantidadTotal.toFixed(2));
		//~ $('#__precio_total').val(precioTotal.toFixed(2));
	//~ };
	// *********************** FIN Función que obtiene los ítems desde la vista *************************** //

	// ************* Actualizar los datos mostrados según el producto y cantidad ingresados *************** //
	this.actualizarSeleccion = function(){
		var cmbProducts = $("#__cmbProducts");
		var cantidad = $("#__cantidad").val() * 1;
		var selectedValue = cmbProducts.find('option:selected').val();
		//Filtrar la lista de items buscando el codigo elegido
		var precio = 0;
		var rowEncontrada = data.find(row => row['codigo_completo'] == selectedValue);
		if(rowEncontrada)
		  precio = 1 * rowEncontrada.precio_unitario;
		$("#txtPrecioUnitario").val(precio.toFixed(2));
		var subtotal = (precio * cantidad);
		$("#txtSubtotal").val(subtotal.toFixed(2));
	};
	// *********** FIN Actualizar los datos mostrados según el producto y cantidad ingresados ************* //
	
	// ************************************** Agregar Ítem al carro *************************************** //
	this.agregarItem = function(){
		var nombre = $("#__cmbProducts").find('option:selected').text();
		var codigo = $("#__cmbProducts").find('option:selected').val();
		var cantidad = $("#__cantidad").val() * 1;
		var precio = $("#txtPrecioUnitario").val() * 1;
		var subtotal = $("#txtSubtotal").val() * 1;
		
		if(codigo == undefined ||codigo == '' || cantidad<=0)
		{
			$('#error').html('<div class=\"alert alert-danger\"><strong>Error: </strong>Debe elegir un Producto y cargar una cantidad</div>');	
			$('#error').show();
		}
		else
		{
			shopCartItems.push({
				'nombre': nombre,
				'codigo': codigo,
				'cantidad': cantidad,
				'precio': precio,
				'subtotal': subtotal
			});	
			$('#addItemModal').modal('hide');
		}			
//~ console.log(shopCartItems);
		
	};
	// ************************************ FIN Agregar Ítem al carro ************************************* //
	
	// *********************************************************************** //
	//Crear div contenedor del template addItem
	var _addItem_template = document.createElement('div');
	_addItem_template.setAttribute("id", "_addItem_template");
	document.body.appendChild(_addItem_template);
	
	//Cargar template de addItem
	$( "#_addItem_template" ).load( 
		pasoTemplates + "/addItem.html", 
		{'rand': Math.random()},
		function(){
			// ************** Enlazar eventos ***************** //
			$("#__cmbProducts").on("change", function(event) { 
				that.actualizarSeleccion();
			});
			$("#__cantidad").on("change", function(event) { 
				that.actualizarSeleccion();
			});
			$("#__submitForm").on("click", function(event) { 
				that.agregarItem();
			});
			// ************ FIN Enlazar eventos *************** //
		}
	);
	
	// *********************************************************************** //
	//Crear div contenedor del template viewShopCart
	var _viewShopCart_template = document.createElement('div');
	_viewShopCart_template.setAttribute("id", "_viewShopCart_template");
	document.body.appendChild(_viewShopCart_template);

	//Cargar template de viewShopCart
	$( "#_viewShopCart_template" ).load( 
		pasoTemplates + "/viewShopCart.html", 
		{'rand': Math.random()},
		function(){
			// ************** Enlazar eventos ***************** //
			//~ $("#__cmbProducts").on("change", function(event) { 
				//~ that.actualizarSeleccion();
			//~ });
			// ************ FIN Enlazar eventos *************** //
		}
	);
	
	// *********************************************************************** //
	//Crear div contenedor del template shopCartItem
	var _shopCartItem_template = document.createElement('div');
	_shopCartItem_template.setAttribute("id", "_shopCartItem_template");
	document.body.appendChild(_shopCartItem_template);

	//Cargar template de viewShopCart
	$( "#_shopCartItem_template" ).load( 
		pasoTemplates + "/shopCartItem.html", 
		{'rand': Math.random()},
		function(){
			// ************** Enlazar eventos ***************** //
			//~ $("#__cmbProducts").on("change", function(event) { 
				//~ that.actualizarSeleccion();
			//~ });
			// ************ FIN Enlazar eventos *************** //
		}
	);
	
	// *********************************************************************** //
	//Crear div contenedor del template shareLink
	var _shareLink_template = document.createElement('div');
	_shareLink_template.setAttribute("id", "_shareLink_template");
	document.body.appendChild(_shareLink_template);

	//Cargar template de shareLink
	$( "#_shareLink_template" ).load( 
		pasoTemplates + "/shareLink.html", 
		{'rand': Math.random()},
		function(){
			// ************** Enlazar eventos ***************** //
			//~ $("#__cmbProducts").on("change", function(event) { 
				//~ that.actualizarSeleccion();
			//~ });
			// ************ FIN Enlazar eventos *************** //
		}
	);
	
	
};	//ShopCart			      
// ************************************************* FIN ShopCart ******************************************************* // 

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
