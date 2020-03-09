// ************************************************************************************************************************************* //
// ************************************************************************************************************************************* //
Logger = function (){			
	var that = this;
	
	var options = arguments[0] || {};
	vendorPhone = options.vendorPhone || ''; 
	urlLog = options.urlLog || '192.168.1.7/book_display/page_log.php';
	id_book = options.id_book || '';
	
	// *********************** Función que envía el log *************************** //
	//Recibe como parámetro el string del código 	
	that.registerLog = function(parPageNumber){
		$.ajax({
			url: urlLog,
			data: {
				page_number: parPageNumber,
				id_book: id_book,
				vendor_phone: vendorPhone,
				_a_: 'ra'
			},
			type: 'get',
			dataType: 'json'
		}); 
	
	};
	// ********************* FIN Función que envía el log ************************* //
	
	// *********************** Función que envía el log *************************** //
	//Recibe como parámetro el string del código 	
	that.registrarVenta = function(parShopCartItems){
		$.ajax({
			url: urlLog,
			data: {
				id_book: id_book,
				vendor_phone: vendorPhone,
				_a_: 'rv',
				items: JSON.stringify(parShopCartItems),
			},
			type: 'get',
			dataType: 'json'
		}); 
	
	};
	// ********************* FIN Función que envía el log ************************* //
	
};	//Logger			      
// ************************************************* FIN ShopCart ******************************************************* // 

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
