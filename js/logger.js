// ************************************************************************************************************************************* //
// ************************************************************************************************************************************* //
Logger = function (){			
	var that = this;
	
	var options = arguments[0] || {};
	vendorPhone = options.vendorPhone || ''; 
	urlLog = options.urlLog || '192.168.1.7/book_display/page_log.php';
	id_book = options.id_book || '';
	
	// *********************** Funci�n que env�a el log *************************** //
	//Recibe como par�metro el string del c�digo 	
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
	// ********************* FIN Funci�n que env�a el log ************************* //
	
	// *********************** Funci�n que env�a el log *************************** //
	//Recibe como par�metro el string del c�digo 	
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
	// ********************* FIN Funci�n que env�a el log ************************* //
	
};	//Logger			      
// ************************************************* FIN ShopCart ******************************************************* // 

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
