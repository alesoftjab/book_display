var BstrapModal = function (parOpciones={}) {
	var title = parOpciones.title || "Título"; 
	var body = parOpciones.body || "Contenido"; 
	var buttons = parOpciones.buttons || [{ Value: "Cerrar", Css: "btn-secondary", Callback: function (event) { BstrapModal.Close(); } }];
	
	var GetModalStructure = function () {
		var that = this;
		that.Id = BstrapModal.Id = Math.random();
		var buttonshtml = "";
		for (var i = 0; i < buttons.length; i++) {
			buttonshtml += "<button type='button' class='btn " + 
			(buttons[i].Css||"") + "' name='btn" + that.Id + 
			"'>" + (buttons[i].Value||"CLOSE") + 
			"</button>";
		}
						
		return "<div class='modal fade bd-example-modal-lg' tabindex='-1' role='dialog' aria-labelledby='myLargeModalLabel' aria-hidden='true' name='dynamiccustommodal' data-backdrop='static' data-keyboard='false' id='" + that.Id + "'>" + 
							"<div class='modal-dialog modal-lg'>" +
								"<div class='modal-content'>" +

									"<div class='modal-header'>" +
										"<h5 class='modal-title'>" + title + "</h5>" +
										"<button type='button' class='close' data-dismiss='modal' aria-label='Close'>" +
											"<span aria-hidden='true'>&times;</span>" +
										"</button>" +
									"</div>" +
									"<div class='modal-body'>" + body +
									"</div>" +
									"<div class='modal-footer'>" + buttonshtml +
									"</div>" +
								"</div>" +
							"</div>" +
						"</div>";
						
	}();
	
	BstrapModal.Delete = function () {
		var modals = document.getElementsByName("dynamiccustommodal");
		if (modals.length > 0) document.body.removeChild(modals[0]);
	};
	BstrapModal.onClose = parOpciones.onClose || function () {
		//alert('cerrando');
	};    
	BstrapModal.Close = function () {
		$(document.getElementById(BstrapModal.Id)).modal('hide');
		BstrapModal.Delete();
		BstrapModal.onClose();
	};    
	BstrapModal.cargarUrl = function (parUrl) {
		$(document.getElementById(BstrapModal.Id)).find('.modal-body').load(parUrl + '&_id_modal=' + BstrapModal.Id);
	};
	this.Show = function (parUrl) {
		var url = parUrl || ""; 
		
		BstrapModal.Delete();
		document.body.appendChild($(GetModalStructure)[0]);
		var btns = document.querySelectorAll("button[name='btn" + BstrapModal.Id + "']");
		for (var i = 0; i < btns.length; i++) {
			btns[i].addEventListener("click", buttons[i].Callback || BstrapModal.Close);
		}
		$(document.getElementById(BstrapModal.Id)).modal('show');
		if(url != '')
		{
		  BstrapModal.cargarUrl(url);
		}
	};
};
