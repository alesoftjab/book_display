// ************************************************************************************************************************************* //
// ************************************************************************************************************************************* //
BookDisplay = function (){			
	that = this;
	var options = arguments[0] || {};

	that.container = options.container || document.getElementById('container');
	that.data = options.data; 
//~ console.log(that.data);	
//~ alert('width ' + width + ', height ' + height);		
	that.mode = options.mode || 'v';           	//[ v | e ] : vista o edición
	that.zooming = false;          				 			//Es true si la animación de zoom se está ejecutando 
	that.changingPage = false;      						//Es true si la animación de cambio de página se está ejecutando 
	that.inZoom = false;
	that.scaleBy = 1.5;
	that.fpsZoom = 1000/30;   					//30fps;
	that.zoomDuration = 500;   			//segundos
	that.scaleStart = 0
	that.scaleEnd = 0;
	that.zoomStartTime = 0;
	that.zoomEndTime = 0;
	that.idZoom = 0;			
	that.propsEditor = null;
	that.bookPaginator = null;
	that.paginatorHeight = 30;
	that.logger = options.logger || null;
	that.maxPosition = that.data.pages.length - 1;				//	
	that.currentPosition = options.page || 0;				//[ 0 | 1 | 2 ]	
	if(that.currentPosition<0 || that.currentPosition > that.maxPosition)
	  that.currentPosition = 0;
	that.arrShapes = [];
	that.pageWidth = (1*that.data.pageWidth) || 800;
	that.pageHeight = (1*that.data.pageHeight) || 600;
	that.imageWidth = 0;
	that.imageHeight = 0;
	that.pageAspectRatio = that.pageWidth / that.pageHeight;
	that.gutter = 4;								//Espacio entre imágenes
	that.pageChangeThreshold = 0.3;		//Límite de movimiento para cambiar la página [ 0 ... 1] 
	
	that.width;
	that.height;
	that.windowAspectRatio;

	this.reScale= function(){
		that.width = that.container.offsetWidth;
		that.height = that.container.offsetHeight;
		that.windowAspectRatio = that.width / that.height;
		if(that.windowAspectRatio > that.pageAspectRatio){    	
			that.imageHeight = that.height;		//Todo el alto disponible
			that.imageWidth = that.imageHeight * that.pageAspectRatio;
		}
		else{
			that.imageWidth = that.width;			//Todo el ancho disponible
			that.imageHeight = that.imageWidth / that.pageAspectRatio;	
		}
		
			
		if(stage){
		  that.setPage(that.currentPosition);
		  //~ stage.draw();
		}
	};
	this.reScale();
//~ console.log(windowAspectRatio + ' + ' + that.pageAspectRatio + ' | image ' + that.imageWidth + ' x ' + that.imageHeight + ' | window ' + width + ' x ' + height + ' | page ' + that.pageWidth + ' x ' + that.pageHeight);
	
	var stage = new Konva.Stage({
		container: that.container,
		width: that.width,
		height: that.height,
		//~ x: (width - that.imageWidth) / 2,           //Establecer las coordenadas 0,0 al punto superior izquierdo que dejará centrada a las imágenes
		//~ y: (height - that.imageHeight) / 2,
		//draggable: true
	});
	
//~ alert(stage.width() + ', ' + stage.height());
	// ******************************* Layer de imágenes *********************************** //
	var imagesLayer = new Konva.Layer({
		draggable: true,
		//~ x: -that.imageWidth * that.currentPosition
	});
	stage.add(imagesLayer);

	var arrImages = [];
	var arrKonvaImages = [];
	var arrAnims = [];
	var nroFoto;
	var imageLoading = new Image();

	//Precarga de imagen de loading
	imageLoading.src = 'http://simpleicon.com/wp-content/uploads/loading.png';
	//Cuando la imagen de loading se termine de cargar llamar a cargar las imágenes de la posición actual 
	imageLoading.onload = function() {
		that.setPage(that.currentPosition);	
	};	
	
	//Función que carga una imagen. Tiene una imagen con animación ( imagen de loading ) para que se muestre hasta que se termina de cargar
	this.loadImage = function(parPosition){
//~ console.log('LOAD ' + parPosition);				
		// ********* Inicializar la imagen en una imagen de carga en cache para que no tire errores	
		arrKonvaImages[parPosition] = new Konva.Image({
			image: imageLoading,
			x: (that.width - (40 + that.gutter)) / 2 + (that.imageWidth + that.gutter) * (that.currentPosition),
			y: (that.height - 40) / 2,
			width: 40,
			height: 40,
			opacity: parPosition == that.currentPosition? 0.5: 0,
			offset: {
				x: 20,
				y: 20
			}
		});
		// Agregar animación a la imagen de carga para que gire 45 grados por segundo
		var angularSpeed = 45;
		arrAnims[parPosition] = new Konva.Animation(function(frame) {
				var angleDiff = frame.timeDiff * angularSpeed / 1000;
				arrKonvaImages[parPosition].rotate(angleDiff);
		}, imagesLayer);
		arrAnims[parPosition].start();
		imagesLayer.add(arrKonvaImages[parPosition]);
		imagesLayer.draw();
		// ********* FIN Inicializar la imagen en una imagen de carga en cache para que no tire errores	
		
		// ******************** Cargar la imagen de la parPosition
		arrImages[parPosition] = new Image();
		arrImages[parPosition].nro = parPosition;
		arrImages[parPosition].src = that.data.pages[parPosition].image;
		
		//Ejecutar esto cuando la imagen se haya cargado 
		arrImages[parPosition].onload = function() {
			arrAnims[this.nro].stop();                //Detener la animaciónde la imagen de carga 
			arrKonvaImages[this.nro].destroy();				//Eliminar la imagen de carga 
			arrKonvaImages[this.nro] = new Konva.Image({
				x: (that.width - (that.imageWidth + that.gutter)) / 2 + (that.imageWidth + that.gutter) * (this.nro),
				y: (that.height - that.imageHeight) / 2,
				image: this,
				width: that.imageWidth,
				height: that.imageHeight,
				opacity: 0,
				nro: this.nro
			});
			
			// add the image to the layer
			//arrKonvaImages[this.nro].cache();
			imagesLayer.add(arrKonvaImages[this.nro]);
			arrKonvaImages[this.nro].setZIndex(0);

			//Si es la imagen de la posición actual mostrar pasando su opacidad a 1 
			if(this.nro == that.currentPosition){
				arrKonvaImages[this.nro].to({opacity:1, duration:0.3})
			}

			imagesLayer.draw();
		};	
	};	

	//Función que normaliza la vista, posicionando el imagesLayer en la posición actual y sin zomm  
	that.restorePosition = function(){
		that.changingPage = true;
		imagesLayer.draggable(false);
		//Mover imagesLayer a la nueva posición 
		imagesLayer.to({
			x: -((that.currentPosition) * (that.imageWidth + that.gutter)), 
			y: 0, 
			scaleX: 1,
			scaleY: 1,
			easing: Konva.Easings.StrongEaseOut, 
			duration: .7,
			onFinish: function() {
				that.changingPage = false;
				imagesLayer.draggable(true);
			}					
		});
	};
	
	//Función para establecer la página actual
	that.setPage =function(par_position){
		if(that.changingPage == false){
			
			//Loggear
			if(that.logger != null){
				that.logger.registerLog(par_position);
			}

			//Si había abierto un editor destruirlo
			if(that.propsEditor != null){
				that.propsEditor.destroy();
			}
			
			//Establecer la nueva posición
			that.currentPosition = par_position;

			//Establecer la posición de bookPaginator
			that.bookPaginator.updatePosition();

			//Mover imagesLayer a la nueva posición 
			that.restorePosition();
			
			//Si la imagen de la posición no está cargada, cargarla
			if(arrKonvaImages[that.currentPosition] === undefined){
				that.loadImage(that.currentPosition);
			}	
			else{
//~ console.log('mostrar la cargada ' + that.currentPosition);				
				//Si ya estaba cargada mostrarla ( a lo mejor ya se estaba mostrando pero igual se puede llamar ) 
				arrKonvaImages[that.currentPosition].to({
					opacity:1,					
					easing: Konva.Easings.StrongEaseOut,
					duration:.3
				});
				imagesLayer.draw();
			}						

			for(var t = 0; t<arrKonvaImages.length; t++)
			{
				if(t != that.currentPosition){
					if(arrKonvaImages[t] != undefined){
						arrKonvaImages[t].to({
							opacity:0,					
							easing: Konva.Easings.StrongEaseOut,
							duration:.3
						});
					}
				}				
			}
			
			//~ //Si hay una imagen siguiente en dirección ascendente que acabo de dejar apagarla
			//~ if(arrKonvaImages[that.currentPosition + 1] != undefined){
//~ console.log('apagar la mayor a ' + that.currentPosition);				
				//~ arrKonvaImages[that.currentPosition + 1].to({
					//~ opacity:0,					
					//~ easing: Konva.Easings.StrongEaseOut,
					//~ duration:.3
				//~ });
			//~ }
			//~ //Si hay una imagen siguiente en dirección descendente que acabo de dejar apagarla
			//~ if(arrKonvaImages[that.currentPosition - 1] != undefined){
//~ console.log('apagar la menor a ' + that.currentPosition);				
				//~ arrKonvaImages[that.currentPosition - 1].to({
					//~ opacity:0,					
					//~ easing: Konva.Easings.StrongEaseOut,
					//~ duration:.3
				//~ });
			//~ }
			
			
				//~ if(!(arrKonvaImages[that.currentPosition-2] === undefined)){
					//~ arrKonvaImages[that.currentPosition - 2].to({
						//~ opacity:0.5,					
						//~ easing: Konva.Easings.StrongEaseOut,
						//~ duration:.3
					//~ });
				//~ }
				//~ if(!(arrKonvaImages[that.currentPosition+2] === undefined)){
					//~ arrKonvaImages[that.currentPosition + 2].to({
						//~ opacity:0.5,					
						//~ easing: Konva.Easings.StrongEaseOut,
						//~ duration:.3
					//~ });
				//~ }
				//~ if(!(arrKonvaImages[that.currentPosition-2] === undefined)){
					//~ arrKonvaImages[that.currentPosition - 2].to({
						//~ opacity:0.5,					
						//~ easing: Konva.Easings.StrongEaseOut,
						//~ duration:.3
					//~ });
				//~ }
				//~ if(!(arrKonvaImages[that.currentPosition+2] === undefined)){
					//~ arrKonvaImages[that.currentPosition + 2].to({
						//~ opacity:0.5,					
						//~ easing: Konva.Easings.StrongEaseOut,
						//~ duration:.3
					//~ });
				//~ }
			
			
			//Cargar la imagen siguiente en dirección ascendente si no está cargada
			if(that.currentPosition < that.maxPosition)
				if(arrKonvaImages[that.currentPosition + 1] === undefined)
					that.loadImage(that.currentPosition + 1);
					
			//Cargar la imagen siguiente en dirección descendente si no está cargada
			if(that.currentPosition > 0)
				if(arrKonvaImages[that.currentPosition - 1] === undefined)
					that.loadImage(that.currentPosition - 1);

			//Destruir imágenes que no sean las que están en muestra
//~ console.log(arrKonvaImages);						
			//~ arrKonvaImages.forEach(function(konvaImage) {
//~ console.log(konvaImage);				

					//~ if(((konvaImage.attrs.nro*1) != (that.currentPosition)) && ((konvaImage.attrs.nro*1) != (that.currentPosition-1)) && ((konvaImage.attrs.nro*1) != (that.currentPosition+1))){ 
//~ console.log('destruyendo ' + konvaImage.nro);				

						//~ konvaImage.destroy();
					//~ } 
			//~ });
			
			//Destruir las ClickAreas
			for(var shape =0; shape < that.arrShapes.length; shape++)
				that.arrShapes[shape].destroy();               //Destruir el elemento konva
			that.arrShapes = [];                             //Vaciar el array that.arrShapes;

			//Generar las nuevas ClickArea	
			for(var shape =0; shape < that.data.pages[that.currentPosition].ClickAreas.length; shape++){
				var shapeData = that.data.pages[that.currentPosition].ClickAreas[shape];
				if(shapeData.deleted === undefined)
					shapeData.deleted = false;
					
				if(shapeData.deleted == false){
					that.createClickArea({
						x: 1*shapeData.x,
						y: 1*shapeData.y,
						width: 1*shapeData.width,
						height: 1*shapeData.height,
						rotation: 1*shapeData.rotation,
						codigo: shapeData.codigo,
					});
					
				}	
			}
			var strLocation = window.location.href;
			//Si strLocation no contiene "index.php?" agregarlo
			if(window.location.href.search("index.php?") == -1)
				strLocation = strLocation.replace(window.location.pathname,window.location.pathname + 'index.php?');
			var nuevaURL = this.replaceQueryParam('page', (1*that.currentPosition)+1, strLocation);
			history.replaceState('CATALOGO', 'PAGINA', nuevaURL);
		}
	};
	
	this.replaceQueryParam = function(param, newval, search) {
			var regex = new RegExp("([?;&])" + param + "[^&;]*[;&]?");
			var query = search.replace(regex, "$1").replace(/&$/, '');

			return (query.length > 2 ? query + "&" : "?") + (newval ? param + "=" + newval : '');
	}
	
	//Función para pasar a la página siguiente
	that.nextPage =function(){
		that.setPage(that.currentPosition+1);
	};

	//Función para pasar a la página anterior
	that.previousPage =function(){
		that.setPage(that.currentPosition-1);
	};



	// write out drag and drop events
	imagesLayer.on('dragstart', function(e) {
		if(that.changingPage == false){
			if (e.target === imagesLayer){ 
				shapesGroup.hide();
				
				//Si no está en zoom mostrar las páginas laterales
				if(that.inZoom == false)
				{
					if(that.currentPosition > 0){
						arrKonvaImages[that.currentPosition - 1].to({
							opacity:1,					
							easing: Konva.Easings.StrongEaseOut,
							duration:1
						});
					}
					if(that.currentPosition < that.maxPosition){
						arrKonvaImages[that.currentPosition + 1].to({
							opacity:1,					
							easing: Konva.Easings.StrongEaseOut,
							duration:1
						});
					}
				}					
			}	
		}
	});
	// write out drag and drop events
	imagesLayer.on('dragmove', function(e) {
//~ writeMessage(imagesLayer.x());
		
		if (e.target === imagesLayer){ 
			// Eje Y
			if(imagesLayer.y() > 0)
				imagesLayer.y(0);	
			if(imagesLayer.y() + (imagesLayer.height() * imagesLayer.attrs.scaleY) < that.height)
				imagesLayer.y(that.height - (imagesLayer.height() * imagesLayer.attrs.scaleY));	

			
			// Eje X
			//Si está en zoom no permitir ir más allá de los límites de la pantalla
			if(that.inZoom == true)
			{
				//~ if(imagesLayer.x() > 0 + (that.currentPosition * that.imageWidth * imagesLayer.attrs.scaleX))
					//~ imagesLayer.x(0 - (that.currentPosition * that.imageWidth));	
				//~ if(imagesLayer.x() + (imagesLayer.width() * imagesLayer.attrs.scaleX) + (that.currentPosition * that.imageWidth * imagesLayer.attrs.scaleX) < width)
					//~ imagesLayer.x(width - (imagesLayer.width() * imagesLayer.attrs.scaleX) + (that.currentPosition * that.imageWidth * imagesLayer.attrs.scaleX));	
			}
			else      //Si no está en zoom no permitir ir más allá de la mitad del ancho
			{
				if(imagesLayer.x() < -((that.imageWidth / 2) + that.currentPosition * (that.imageWidth + that.gutter)))
					imagesLayer.x(-((that.imageWidth / 2) + that.currentPosition * (that.imageWidth + that.gutter)));
				if(imagesLayer.x() > (that.imageWidth / 2)-(that.currentPosition * (that.imageWidth + that.gutter)))
					imagesLayer.x((that.imageWidth / 2)-(that.currentPosition * (that.imageWidth + that.gutter)));
			}	
		}	
	});
	imagesLayer.on('dragend', function() {
		//shapesLayer.position({x: this.x(),y: this.y()});

		//Si no está en zoom ejecutar el comportamiento de pasar página o re-centrar
		if(that.inZoom == false)
		{
			//Si el x de imagesLayer está fuera del límite de cambio de página entonces cambiar la página
			//Página siguiente
			if((imagesLayer.x() < -(that.pageChangeThreshold * that.imageWidth)-(that.currentPosition * (that.imageWidth + that.gutter))) && (that.currentPosition < that.maxPosition)){     
				that.nextPage();
			}
			//Página anterior
			else if((imagesLayer.x() > (that.pageChangeThreshold * that.imageWidth)-(that.currentPosition * (that.imageWidth + that.gutter))) && (that.currentPosition > 0)){     
				that.previousPage();
			}
			//Si el límite no fue superado entonces retornar a la posición inicial
			else{    
				
				//Mover imagesLayer a la posición y zoom correctos
				that.restorePosition();
				
				//Apagar la imagen de la izquierda
				if(that.currentPosition > 0){
					arrKonvaImages[that.currentPosition - 1].to({
						opacity:0,					
						easing: Konva.Easings.StrongEaseOut,
						duration:.3
					});
				}
				//Apagar la imagen de la derecha
				if(that.currentPosition < that.maxPosition){
					arrKonvaImages[that.currentPosition + 1].to({
						opacity:0,					
						easing: Konva.Easings.StrongEaseOut,
						duration:.3
					});
				}
			}
//~ console.log('currentPosition ' + that.currentPosition);
		}
		
		shapesGroup.show();
		imagesLayer.draw();
	});
	// ***************************** FIN Layer de imágenes ********************************* //

	// ******************************** Layer de shapes ************************************ //
	//~ var shapesLayer = new Konva.Layer();
	//~ stage.add(shapesLayer);

	var shapesGroup = new Konva.Group({
			x: 0,
			y: 0
	});
	//~ shapesGroup.add(new Konva.Rect({
			//~ x: 0,
			//~ y: 0,
			//~ width: 20,
			//~ height: 20,
			//~ fill: "orange",
			//~ stroke: 'black',
			//~ strokeWidth: 2
		//~ })
	//~ );
	imagesLayer.add(shapesGroup);			

	imagesLayer.draw();
	
	// ****************************** Selector de shapes ********************************** //
	stage.on('click tap', function (e) {
		
		
		//Si continúa por acá es proque es un ClickArea
		if (e.target.hasName('ClickArea')) {
		
			//Disparar el evento onClickedArea si está en modo vista 
			if(that.mode == 'v')
				that.onClickedArea(e.target.attrs.codigo);
//~ console.log(e.target.attrs.codigo);

			//Si es modo edición crear un transformer
			if(that.mode == 'e'){
				var shape = e.target;
				// remove old transformers
				stage.find('Transformer').destroy();

				// create new transformer
				var tr = new Konva.Transformer();
				imagesLayer.add(tr);
				tr.attachTo(shape);
										
				//Si había abierto un editor destruirlo
				if(that.propsEditor != null){
					that.propsEditor.destroy();
				}
				//Crear un editor
				that.propsEditor = new PropsEditor({stage: stage, layer: controlsLayer, shape: shape}); 
				//~ var propsEditor = new foo({shape: e.target}); 
				
				imagesLayer.draw();
			}
		}
		//No es un ClickArea, entonces destruir cualquier transformer que pudiera existir y salir
		else{      
			stage.find('Transformer').destroy();
			imagesLayer.draw();
		}
	})
				
	// **************************** FIN Selector de shapes ******************************** //

	// ****************************** FIN Layer de shapes ********************************** //

	// ******************************* Layer de controles *********************************** //
	var controlsLayer = new Konva.Layer();
	stage.add(controlsLayer);

	leftArrow = new Konva.Arrow({
		x: 0,
		y: stage.getHeight() / 2,
		points: [20, 0, 0, 0],
		pointerLength: 20,
		pointerWidth: 20,
		fill: 'green',
		stroke: 'green',
		strokeWidth: 0,		
		shadowColor: 'gray',
		shadowBlur: 20,
		shadowOffset: {x : 0, y : 0},
		shadowOpacity: 0,
		id: 'leftArrow'
	});
	controlsLayer.add(leftArrow);

	leftArrow.tween = new Konva.Tween({
			node: leftArrow,
			duration: 0.3,
			shadowOpacity: 0.9,
			easing: Konva.Easings.EaseInOut,
			fill : 'lightgreen',
	});			
	leftArrow.on('mouseover', function() {
		leftArrow.tween.play();
		controlsLayer.draw();
	});
	leftArrow.on('mouseout', function() {
		leftArrow.tween.reverse();				
	});
	leftArrow.on('click tap', function() {
		if(that.zooming == false && that.changingPage == false){
			if(that.currentPosition > 0){
				arrKonvaImages[that.currentPosition - 1].to({
					opacity:1,					
					easing: Konva.Easings.StrongEaseOut,
					duration:1
				});
				that.previousPage();
			}
		}
	});
	leftArrow.on('mouseenter', function () {
		stage.container().style.cursor = 'pointer';
	});

	leftArrow.on('mouseleave', function () {
		stage.container().style.cursor = 'default';
	});			

	rightArrow = new Konva.Arrow({
		x: stage.getWidth() - 20,
		y: stage.getHeight() / 2,
		points: [0, 0, 20, 0],
		pointerLength: 20,
		pointerWidth: 20,
		fill: 'green',
		stroke: 'green',
		strokeWidth: 0,				
		shadowColor: 'gray',
		shadowBlur: 20,
		shadowOffset: {x : 0, y : 0},
		shadowOpacity: 0,
		id: 'rightArrow'
	});
	controlsLayer.add(rightArrow);
	rightArrow.tween = new Konva.Tween({
			node: rightArrow,
			duration: 0.3,
			shadowOpacity: 0.9,
			easing: Konva.Easings.EaseInOut,
			fill : 'lightgreen',
	});			
	rightArrow.on('mouseover', function() {
		rightArrow.tween.play();
		controlsLayer.draw();
	});
	rightArrow.on('mouseout', function() {
		rightArrow.tween.reverse();				
	});
	rightArrow.on('click tap', function() {
		if(that.zooming == false && that.changingPage == false){
			if(that.currentPosition < that.maxPosition){
				arrKonvaImages[that.currentPosition + 1].to({
					opacity:1,					
					easing: Konva.Easings.StrongEaseOut,
					duration:1
				});
				that.nextPage();
			}
		}
	});
	rightArrow.on('mouseenter', function () {
		stage.container().style.cursor = 'pointer';
	});

	rightArrow.on('mouseleave', function () {
		stage.container().style.cursor = 'default';
	});			
	
	
	// ****************************************** STATUS TEXT *************************************** //			
	function writeMessage(message) {
					text.text(message);
					controlsLayer.draw();
				}
	var text = new Konva.Text({
		x: 10,
		y: 10,
		fontFamily: 'Calibri',
		fontSize: 20,
		text: '',
		fill: 'lightgreen'
	});
	controlsLayer.add(text);
	// *************************************** FIN STATUS TEXT ************************************** //			


	//Paginador
	that.bookPaginator = new BookPaginator({stage: stage, layer: controlsLayer}); 

	controlsLayer.draw();


	// add the layer to the stage
	// ***************************** FIN Layer de controles ********************************* //


	// **************************************************************************************************************** //
	// ***************************** Eventos del visor **************************************************************** //
	

	// ******************************** Manejo del zoom  ********************************** //
	var maxZoom = 6;
	var minZoom = 1;
	stage.on('wheel', e => {
		e.evt.preventDefault();
		// ****************************************************** //
		if(that.zooming == true){
			that.zooming = false;
			clearInterval(that.idZoom);
		}	
		if(that.zooming == false && that.changingPage == false){
			that.zooming = true;

			that.scaleStart = imagesLayer.scaleX();
			
			if(e.evt.deltaY < 0){  //zoomIn
//~ console.log('startZoomIn');
				that.scaleEnd = that.scaleStart * that.scaleBy;
				if(that.scaleEnd > maxZoom)
					that.scaleEnd = maxZoom;
			}
			else{   //zoomOut
//~ console.log('startZoomOut');
				that.scaleEnd = that.scaleStart / that.scaleBy;
				if(that.scaleEnd < minZoom)
					that.scaleEnd = minZoom;
			}
			//Si el zoom destino es > 1 entonces that.inZoom es true
			if(that.scaleEnd > 1)
				that.inZoom = true;
			else  
				that.inZoom = false;

			//Disparar el evento zoomChanged
			that.zoomChanged();
			
			that.zoomStartTime =   new Date().getTime();
			that.zoomEndTime = that.zoomStartTime + that.zoomDuration;
			that.idZoom = setInterval(that.frameZoom, that.fpsZoom);
		}
		
		// ****************************************************** //


	});
	
	//Zoom al máximo o al mínimo con doble click o doble tap
	stage.on('dblclick dbltap', function (e) {
return;
		e.evt.preventDefault();
		// ****************************************************** //
		if(that.zooming == false && that.changingPage == false){
			that.zooming = true;

			that.scaleStart = imagesLayer.scaleX();

			if(that.inZoom == true)
			{
				that.scaleEnd = minZoom;							
				that.inZoom = false;
			}
			else{  
				that.scaleEnd = maxZoom;							
				that.inZoom = true;
			}
			
			//Disparar el evento zoomChanged
			that.zoomChanged();
			
			that.zoomStartTime = new Date().getTime();
			that.zoomEndTime = that.zoomStartTime + that.zoomDuration;
			that.idZoom = setInterval(that.frameZoom, that.fpsZoom);
		}
	})			
	
	// ****************************************************** //
	this.frameZoom = function () {
		//Finalizar conteo de tiempo
		zoomCurrentTime = new Date().getTime();
		zoomElapsedTime = zoomCurrentTime - that.zoomStartTime;

		var t = zoomElapsedTime / that.zoomDuration;
		if(t > 1)
			t = 1;
//~ console.log('scaleStart ' + that.scaleStart + ' scaleEnd ' + that.scaleEnd +  ' zoomStartTime ' + that.zoomStartTime +  ' zoomEndTime ' + that.zoomEndTime +  ' zoomCurrentTime ' + zoomCurrentTime +  ' zoomElapsedTime ' + zoomElapsedTime);

		//Función lineal: 
		//var newScale = scaleStart + (scaleEnd - scaleStart) * (t);
		//Función  quadratic easing out
		var newScale = that.scaleStart + (that.scaleEnd - that.scaleStart) * (1-(t-1)*(t-1)*(t-1)*(t-1));

		//Obtener la posición actual del mouse
		var currentScale = imagesLayer.scaleX();
		var mousePointTo = {
			x: stage.getPointerPosition().x / currentScale - imagesLayer.x() / currentScale,
			y: stage.getPointerPosition().y / currentScale - imagesLayer.y() / currentScale
		};
		
//~ console.log(newScale);
		//Escalar el stage
		imagesLayer.scale({ x: newScale, y: newScale });
		//shapesLayer.scale({ x: newScale, y: newScale });
		//~ layer.draw();
		
		//Obtener la posición nueva del mouse
		var newPos = {
			x:
				stage.getPointerPosition().x - mousePointTo.x * newScale,
			y:
				stage.getPointerPosition().y - mousePointTo.y * newScale
		};
		//Limitar la posición y si es necesario
		if(newPos.y > 0)
			newPos.y = 0;	
		if(newPos.y + (imagesLayer.height() * imagesLayer.attrs.scaleY) < that.height)
			newPos.y = that.height - (imagesLayer.height() * imagesLayer.attrs.scaleY);	
		
		//~ if (newPos.x + ((that.currentPosition * that.imageWidth+that.gutter) * newScale) + (width-(that.imageWidth+that.gutter) * newScale) / 2 > (width-(that.imageWidth+that.gutter)) / 2)
//~ console.log(newPos.x + ((that.currentPosition * that.imageWidth+that.gutter) * newScale) + (width-(that.imageWidth+that.gutter) * newScale) / 2);
			//~ newPos.x = (width-((that.imageWidth+that.gutter)) / 2) - ((that.imageWidth+that.gutter) * imagesLayer.attrs.scaleY) - (width-((that.imageWidth+that.gutter) * imagesLayer.attrs.scaleX) / 2)


			//~ newPos.x = -((that.imageWidth * imagesLayer.attrs.scaleY) + (width / 2));	
		
		//Mover todo a la nueva posición
		imagesLayer.position(newPos);
		//shapesLayer.position(newPos);

//~ writeMessage(imagesLayer.x());

		
		//Si el momento actual es >= que el momento de fin esperado entonces eliminar el idzoom
		if (zoomElapsedTime >= that.zoomDuration) {
			clearInterval(that.idZoom);
//~ console.log('endZoom');
			
			//Si al finalizar la animación ya no está inZoom entonces posicionar el layer en 0, 0
			if(that.inZoom == false){
				that.restorePosition();
			}
			that.zooming = false;
		} 					

		// ********** Dibujar todo el stage en modo batch ********** //
		stage.batchDraw();
		
	};			
	// ****************************** FIN Manejo del zoom  ******************************** //

	// ################################################## EVENTOS ############################################################# //
	this.zoomChanged = options.zoomChanged	|| function (){
//~ console.log(that.inZoom);
		if(that.inZoom){
			controlsLayer.hide();
		} 
		else{
			controlsLayer.show();
		}
	};
	
	this.onClickedArea = options.onClickedArea || function (codigo){
//~ console.log(codigo);
	};
	// ################################################ FIN EVENTOS ########################################################### //

	// ********************************************** ZOOM CON TOUCH ***************************************************** // 
	// For mobile
	pinchZoomTouchEvent(stage);
	let lastDist;
	let point;

	function getDistance(p1, p2) {
		return Math.sqrt(Math.pow((p2.x - p1.x), 2) + Math.pow((p2.y - p1.y), 2));
	}

	function clientPointerRelativeToStage(clientX, clientY, stage) {
		return {
			x: clientX - stage.x(),
			y: clientY - stage.y(),
		}
	}

	function pinchZoomTouchEvent(stage) {
		if (stage) {
			stage.getContent().addEventListener('touchmove', (evt) => {
//~ writeMessage('touchmove');
				const t1 = evt.touches[0];
				const t2 = evt.touches[1];

				if (t1 && t2) {
					evt.preventDefault();
					evt.stopPropagation();
//~ writeMessage('doble touchmove');
					if(that.zooming == false && that.changingPage == false){
					
						const oldScale = imagesLayer.scaleX();

						const dist = getDistance(
							{ x: t1.clientX, y: t1.clientY },
							{ x: t2.clientX, y: t2.clientY }
						);
						if (!lastDist) lastDist = dist;
						const delta = dist - lastDist;

						const px = (t1.clientX + t2.clientX) / 2;
						const py = (t1.clientY + t2.clientY) / 2;
						const pointer = point || clientPointerRelativeToStage(px, py, imagesLayer);
						
						
						if (!point) point = pointer;

						const startPos = {
							x: pointer.x / oldScale - stage.x() / oldScale,
							y: pointer.y / oldScale - stage.y() / oldScale,
						};

						const scaleBy = 1 + Math.abs(delta) / 100;
						var newScale = delta < 0 ? oldScale / scaleBy : oldScale * scaleBy;
						newScale = newScale.toFixed(4);
						
						if(newScale > maxZoom)
							newScale = maxZoom;

						if(newScale < minZoom)
							newScale = minZoom;
//~ x('newScale: ' + newScale);

						//Si el zoom destino es > 1 entonces that.inZoom es true
						if(newScale > 1)
							that.inZoom = true;
						else  
							that.inZoom = false;

						//Disparar el evento zoomChanged
						that.zoomChanged();

						//Obtener la posición actual del mouse
						var currentScale = imagesLayer.scaleX();
						var mousePointTo = {
							x: px / currentScale - imagesLayer.x() / currentScale,
							y: py / currentScale - imagesLayer.y() / currentScale
						};
						
						//Escalar el stage
						imagesLayer.scale({ x: newScale, y: newScale });
						//shapesLayer.scale({ x: newScale, y: newScale });
						//~ layer.draw();
						
						//Obtener la posición nueva del mouse
						var newPos = {
							x:
								px - mousePointTo.x * newScale,
							y:
								py - mousePointTo.y * newScale
						};
						//Mover todo a la nueva posición
						imagesLayer.position(newPos);
						//shapesLayer.position(newPos);
										
//~ writeMessage(newScale + ', ' + that.inZoom + ', ' + delta);
						stage.batchDraw();
						lastDist = dist;

						//Si pasa de zoom a no zoom
						if(oldScale > 1 && newScale==1){
							that.restorePosition();
						}

						
					}
				}
			}, false);

			stage.getContent().addEventListener('touchend', () => {
				lastDist = 0;
				point = undefined;

			}, false);
		}
	}
	
	
	// ******************************************** FIN ZOOM CON TOUCH *************************************************** // 
	
	
	// ***************************************** GENERACIÓN MANUAL DE SHAPES ********************************************* // 
	this.startShapeGeneration = function(){
		if(that.mode != 'e')   //Si no es modo edición SALIR
			return;
			
		var posStart;
		var posNow;
		var mode = '';
		function startDrag(posIn){
			posStart = {x: posIn.x, y: posIn.y};
			posNow = {x: posIn.x, y: posIn.y};
		};

		function updateDrag(posIn){ 
			
			// update rubber rect position
			posNow = {x: posIn.x, y: posIn.y};
			var posRect = reverse(posStart,posNow);
			r2.x(posRect.x1);
			r2.y(posRect.y1);
			r2.width(posRect.x2 - posRect.x1);
			r2.height(posRect.y2 - posRect.y1);
			r2.visible(true);  
			 
			imagesLayer.draw(); // redraw any changes.
		};
		
		// reverse co-ords if user drags left / up
		function reverse(r1, r2){
			var r1x = r1.x, r1y = r1.y, r2x = r2.x,  r2y = r2.y, d;
			if (r1x > r2x ){
				d = Math.abs(r1x - r2x);
				r1x = r2x; r2x = r1x + d;
			}
			if (r1y > r2y ){
				d = Math.abs(r1y - r2y);
				r1y = r2y; r2y = r1y + d;
			}
			return ({x1: r1x, y1: r1y, x2: r2x, y2: r2y}); // return the corrected rect.     
		}

		// draw a background rect to catch events.
		var r1 = new Konva.Rect({
			x: (that.width - (that.imageWidth + that.gutter)) / 2 + (that.imageWidth + that.gutter) * (that.currentPosition),
			y: (that.height - that.imageHeight) / 2,
			image: this,
			width: that.imageWidth,
			height: that.imageHeight,
			fill: 'gold',
			opacity: 0.2
		});    
		imagesLayer.add(r1);
		r1.moveToTop();

		imagesLayer.draggable(false);

		// draw a rectangle to be used as the rubber area
		var r2 = new Konva.Rect({x: 0, y: 0, width: 0, height: 0, stroke: 'red', dash: [2,2]})    
		r2.listening(false); // stop r2 catching our mouse events.
		imagesLayer.add(r2);

		imagesLayer.draw();
		stage.draw() // First draw of canvas.

		// start the rubber drawing on mouse down.
		r1.on('mousedown', function(e){ 
			mode = 'drawing';
			startDrag({
				x: e.evt.layerX + (that.imageWidth + that.gutter) * (that.currentPosition), 
				y: e.evt.layerY
			});
		});

		// update the rubber rect on mouse move - note use of 'mode' var to avoid drawing after mouse released.
		r1.on('mousemove', function(e){ 
			if (mode === 'drawing'){
				updateDrag({
					x: e.evt.layerX + (that.imageWidth + that.gutter) * (that.currentPosition), 
					y: e.evt.layerY
				});
			};
		});

		// here we create the new rect using the location and dimensions of the drawing rect.
		r1.on('mouseup', function(e){ 
			mode = '';
			r2.visible(false);
//~ console.log(r2);
			//Determinar coordenadas del vértice superior izquierdo de la imagen actual
			var vertice_s_i = {
				x: (that.width - (that.imageWidth + that.gutter)) / 2 + (that.imageWidth + that.gutter) * (that.currentPosition),
				y: (that.height - that.imageHeight) / 2
			};

			var relativeCoords = {
				x: (r2.x() - vertice_s_i.x) / that.imageWidth,
				y: (r2.y() - vertice_s_i.y) / that.imageHeight,
				width: r2.width() / that.imageWidth,
				height: r2.height() / that.imageHeight,
				rotation: r2.rotation(),
			}
			
			// ********** Llamada a createClickArea ************ //
			var nuevo_i = that.createClickArea(relativeCoords);

			//Agregar al objeto de página actual
			that.data.pages[that.currentPosition].ClickAreas.push({
				"x": relativeCoords.x, 
				"y": relativeCoords.y,
				"width": relativeCoords.width,
				"height": relativeCoords.height, 
				"rotation": relativeCoords.rotation, 
				"codigo": ''
			});
//~ console.log(that.data.pages[that.currentPosition].ClickAreas);
			r1.destroy();
			r2.destroy();
			imagesLayer.draw();

			imagesLayer.draggable(true);

		})				
	};
	// *************************************** FIN GENERACIÓN MANUAL DE SHAPES ******************************************* // 
	
	
	// *************************************************** ClickAreas ********************************************************* // 
	//Parámetro par_atribs contiene coordenadas expresadas como PROPORCIONES respecto al tamaño de la imagen y aplicadas respecto al vertice superior izquierda de la imagen
	this.createClickArea = function(par_atribs){
//~ console.table(par_atribs);
		var nuevo_i = that.arrShapes.length; 
//~ console.log('nuevo_i ' + nuevo_i);
		//Determinar coordenadas del vértice superior izquierdo de la imagen actual
		var vertice_s_i = {
			x: (that.width - (that.imageWidth + that.gutter)) / 2 + (that.imageWidth + that.gutter) * (that.currentPosition),
			y: (that.height - that.imageHeight) / 2
		};

		var colorFondo;
		if(par_atribs.codigo === undefined)
			colorFondo = 'red';
		else
		{
			if(par_atribs.codigo === '')
				colorFondo = 'red';
			else
			{
				if(that.mode == 'e')          //En modo edicuón va verde, de lo contrario va blanco	
					colorFondo = 'green';
				else
					colorFondo = 'white';
			}
		}
		that.arrShapes.push(new Konva.Rect({
				shapeIndex: nuevo_i,
				x: vertice_s_i.x + par_atribs.x * that.imageWidth,
				y: vertice_s_i.y + par_atribs.y * that.imageHeight,
				width: par_atribs.width * that.imageWidth,
				height: par_atribs.height * that.imageHeight,
				rotation: par_atribs.rotation,
				fill: colorFondo,
				draggable: that.mode == 'e'? true:false,
				opacity: 0.3,

				name: 'ClickArea',
				idShape: 'ClickArea' + nuevo_i,
				codigo: par_atribs.codigo,
				deleted: false
			})
		);
		//Agregar al grupo de shapes
		shapesGroup.add(that.arrShapes[nuevo_i]);
//~ console.log(that.arrShapes);		
		
		// *************** Evento cuando se termina de desplazar un shape ********************* //
		that.arrShapes[nuevo_i].on('dragend', function () {
//~ console.log(this.height());					
			shapeIndex = this.attrs.shapeIndex;
			//Actualizar el data relacionado al shape
			//~ that.data.pages[that.currentPosition].ClickAreas[shapeIndex].x = this.x().toFixed(1)*1;
			//~ that.data.pages[that.currentPosition].ClickAreas[shapeIndex].y = this.y().toFixed(1)*1;
			
			//Determinar coordenadas del vértice superior izquierdo de la imagen actual
			var vertice_s_i = {
				x: (that.width - (that.imageWidth + that.gutter)) / 2 + (that.imageWidth + that.gutter) * (that.currentPosition),
				y: (that.height - that.imageHeight) / 2
			};
			//Las coordenadas que se guardan son relativas a la imagen, por eso en la coordenada x se le resta la posición de la imagen y tanto a x como a y se las divide por las dimensiones de la imagen. 
			var relativeCoords = {
				x: (this.x() - vertice_s_i.x) / that.imageWidth,
				y: (this.y() - vertice_s_i.y) / that.imageHeight,
			}

//~ console.log(shapeIndex);					
			that.data.pages[that.currentPosition].ClickAreas[shapeIndex].x = relativeCoords.x;
			that.data.pages[that.currentPosition].ClickAreas[shapeIndex].y = relativeCoords.y;
//~ console.log(that.data.pages[that.currentPosition].ClickAreas);					
		});
		// ************* FIN Evento cuando se termina de desplazar un shape ******************* //
		// ************* Evento cuando se termina de redimensionar un shape ******************* //
		that.arrShapes[nuevo_i].on('transformend', function () {
//~ console.log(this);					
			var shapeIndex = this.attrs.shapeIndex;
			//Actualizar el data relacionado al shape
			//Las coordenadas que se guardan son relativas a la imagen, por eso en la coordenada x se le resta la posición de la imagen y tanto a x como a y se las divide por las dimensiones de la imagen. 
			//Determinar coordenadas del vértice superior izquierdo de la imagen actual
			var vertice_s_i = {
				x: (that.width - (that.imageWidth + that.gutter)) / 2 + (that.imageWidth + that.gutter) * (that.currentPosition),
				y: (that.height - that.imageHeight) / 2
			};

			var relativeCoords = {
				x: (this.x() - vertice_s_i.x) / that.imageWidth,
				y: (this.y() - vertice_s_i.y) / that.imageHeight,
				width: (this.width() * this.attrs.scaleX) / that.imageWidth,
				height: (this.height() * this.attrs.scaleY) / that.imageHeight,
				rotation: this.rotation(),
			}
			
//~ console.log(relativeCoords);
			that.data.pages[that.currentPosition].ClickAreas[shapeIndex].x = relativeCoords.x; 
			that.data.pages[that.currentPosition].ClickAreas[shapeIndex].y = relativeCoords.y;
			that.data.pages[that.currentPosition].ClickAreas[shapeIndex].width = relativeCoords.width; 
			that.data.pages[that.currentPosition].ClickAreas[shapeIndex].height = relativeCoords.height; 
			that.data.pages[that.currentPosition].ClickAreas[shapeIndex].rotation = relativeCoords.rotation; 
//~ console.log(that.data.pages[that.currentPosition].ClickAreas);					
		});
		// *********** FIN Evento cuando se termina de redimensionar un shape ***************** //
		
		// ************* Eventos de puntero de mouse ******************* //
		that.arrShapes[nuevo_i].on('mouseenter', function() {
			stage.container().style.cursor = 'pointer';
		});

		that.arrShapes[nuevo_i].on('mouseleave', function() {
			stage.container().style.cursor = 'default';
		});					
		// ************* FIN Eventos de puntero de mouse ******************* //		
		
		return nuevo_i;
	};
	// *********************************************** FIN ClickAreas ***************************************************** // 
	// *************************************************** MODOS ********************************************************** // 
	
	this.changeMode = function(parMode){
		that.mode = parMode;
		
		//~ console.log(parMode);
		if(parMode == 'e'){
			//Hacer que todas las ClickArea sean draggables
			var shapes = stage.find('.ClickArea');
//~ console.log(shapes);					
			shapes.each(function(shape) {          
				shape.draggable(true);
			});
		}
		
		if(parMode == 'v'){
			//Nacer que todas las ClickArea NO sean draggables
			var shapes = stage.find('.ClickArea');
//~ console.log(shapes);					
			shapes.each(function(shape) {          
				shape.draggable(false);
			});
		}				
		
	};
	// ************************************************* FIN MODOS ******************************************************** // 
};	//BookDisplay			      
// ************************************************* FIN BookDisplay ******************************************************* // 

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //

// *************************************** PROPERTIES WINDOW ************************************ //			
PropsEditor = function(){
	var options = arguments[0] || {};
//~ console.log(options);
	
	var propsHeight = 100;
	var propsWidth = 400;
	var propsMargin = 4;
	var strPropsTitle = options.title || 'Title';
	var shape = options.shape;
	var controlsLayer = options.layer;
	var stage = options.stage;
	var propsTitleHeight = 20;
	var propsRowHeight = 18;
	
	// ************* Destructor
	this.destroy = function(){
		propsGroup.destroy();
		controlsLayer.draw();			
	}			
	
	// ************* Grupo de shapes del Editor
	var propsGroup = new Konva.Group({
			x: 20,
			y: 20,
			draggable: true
	});
	controlsLayer.add(propsGroup);			
	
	if(shape)
		strPropsTitle = 'Propiedades de ' + shape.attrs.idShape + ' shapeIndex ' + shape.attrs.shapeIndex;
	
	// ****** Fondo
	var propsFondo = new Konva.Rect({
		x: 0,
		y: 0,
		width: propsWidth,
		height: propsHeight,
		opacity: 0.7,
		fill: 'lightgray',
		cornerRadius: 4
	});
	propsGroup.add(propsFondo);

	// ****** Título
	var propsTitleBack = new Konva.Rect({
		x: propsMargin,
		y: propsMargin,
		width: propsWidth - propsMargin * 2,
		height: propsTitleHeight,
		fill: 'DarkOrange',
		cornerRadius: 4
	});
	propsGroup.add(propsTitleBack);

	var propsTitle = new Konva.Text({
		x: propsMargin,
		y: propsMargin,
		width: propsWidth - propsMargin * 2,
		text: strPropsTitle,
		fontSize: 12,
		height: propsTitleHeight,
		fontFamily: 'Calibri',
		fontStyle: 'bold',
		fill: 'DarkRed',
		align: 'center',
		verticalAlign: 'middle',
		type: 'propsTitle',
	});
	propsGroup.add(propsTitle);

	// ****** Propiedades
	var propsAreaY = propsMargin + propsTitleHeight + 5;   //Coordenada Y del Inicio del área de propiedades 
	
	var lblCodigo = new Konva.Text({
		x: propsMargin + 5,
		y: propsAreaY,
		width: 80,
		text: 'Código',
		fontSize: 12,
		fontStyle: 'bold',
		height: propsRowHeight,
		align: 'left',
		verticalAlign: 'middle'
	});
	propsGroup.add(lblCodigo);

	var inputCodigo = new Konva.Text({
		x: propsMargin + 5 + 80 + 10,
		y: propsAreaY,
		width: 200,
		text: shape.attrs.codigo,
		fontSize: 12,
		height: propsRowHeight,
		align: 'left',
		verticalAlign: 'middle'
	});
	propsGroup.add(inputCodigo);

	inputCodigo.on('click', () => {
		// create textarea over canvas with absolute position

		// first we need to find position for textarea
		// how to find it?

		// at first lets find position of text node relative to the stage:
		var textPosition = inputCodigo.getAbsolutePosition();

		// then lets find position of stage container on the page:
		var stageBox = stage.container().getBoundingClientRect();

		// so position of textarea will be the sum of positions above:
		var areaPosition = {
			x: stageBox.left + textPosition.x,
			y: stageBox.top + textPosition.y
		};

		// create textarea and style it
		var textarea = document.createElement('textarea');
		document.body.appendChild(textarea);

		textarea.value = inputCodigo.text();
		textarea.style.position = 'absolute';
		textarea.style.top = areaPosition.y + 'px';
		textarea.style.left = areaPosition.x + 'px';
		textarea.style.width = inputCodigo.width() + 'px';
		textarea.style.height = inputCodigo.height() - inputCodigo.padding() * 2 + 5 + 'px';
		textarea.style.fontSize = inputCodigo.fontSize() + 'px';
		textarea.style.border = 'none';
		textarea.style.padding = '0px';
		textarea.style.margin = '0px';
		textarea.style.overflow = 'hidden';
		//textarea.style.background = 'none';
		textarea.style.outline = 'none';
		textarea.style.resize = 'none';
		textarea.style.lineHeight = inputCodigo.lineHeight();
		textarea.style.fontFamily = inputCodigo.fontFamily();
		textarea.style.transformOrigin = 'left top';
		textarea.style.textAlign = inputCodigo.align();
		textarea.style.color = inputCodigo.fill();
		textarea.focus();
		inputCodigo.hide();
		controlsLayer.draw();

		textarea.addEventListener('keydown', function(e) {
			// hide on enter
			if (e.keyCode === 13) {
				inputCodigo.show();
				inputCodigo.text(textarea.value);
				controlsLayer.draw();
				document.body.removeChild(textarea);
				updateCodigo();
			}
			// hide on Esc
			if (e.keyCode === 27) {
				inputCodigo.show();
				controlsLayer.draw();
				document.body.removeChild(textarea);
			}
		});

		textarea.addEventListener('blur', function(e) {
			inputCodigo.show();
			inputCodigo.text(textarea.value);
			controlsLayer.draw();
			document.body.removeChild(textarea);
			updateCodigo();
		});
		
	});

	// ****** Botones
	var cant_buttons = 4;
	var button_width = 80;
	
	var nro_bot = 0;

	nro_bot++;
	var propsEliminarBack = new Konva.Rect({
		x: ((propsWidth - 2 * propsMargin) / cant_buttons ) * nro_bot - (button_width/2) + propsMargin,
		y: propsHeight - propsMargin - 20,
		width: button_width,
		height: 20,
		fill: 'red',
		cornerRadius: 4,
	});
	propsGroup.add(propsEliminarBack);
	var propsEliminar = new Konva.Text({
		x: ((propsWidth - 2 * propsMargin) / cant_buttons ) * nro_bot - (button_width/2) + propsMargin,
		y: propsHeight - propsMargin - 20,
		width: button_width,
		height: 20,
		text: 'Eliminar',
		fontSize: 12,
		height: propsTitleHeight,
		fill: 'black',
		align: 'center',
		verticalAlign: 'middle',
		type: 'propsButton',
		action: function(){
			
			shapeIndex = shape.attrs.shapeIndex;
			//Actualizar el data relacionado al shape
			that.data.pages[that.currentPosition].ClickAreas[shapeIndex].deleted = true;
			
			
			shape.hide();
			shape.destroy();
			that.propsEditor.destroy();
		}
	});
	propsGroup.add(propsEliminar);
	

	nro_bot++;
	var propsCancelarBack = new Konva.Rect({
		x: ((propsWidth - 2 * propsMargin) / cant_buttons ) * nro_bot - (button_width/2) + propsMargin,
		y: propsHeight - propsMargin - 20,
		width: button_width,
		height: 20,
		fill: 'lightyellow',
		cornerRadius: 4,
	});
	propsGroup.add(propsCancelarBack);
	var propsCancelar = new Konva.Text({
		x: ((propsWidth - 2 * propsMargin) / cant_buttons ) * nro_bot - (button_width/2) + propsMargin,
		y: propsHeight - propsMargin - 20,
		width: button_width,
		height: 20,
		text: 'Cancelar',
		fontSize: 12,
		height: propsTitleHeight,
		fill: 'black',
		align: 'center',
		verticalAlign: 'middle',
		type: 'propsButton',
		action: function(){
			that.propsEditor.destroy();
		}
	});
	propsGroup.add(propsCancelar);

	nro_bot++;
	var propsAceptarBack = new Konva.Rect({
		x: ((propsWidth - 2 * propsMargin) / cant_buttons ) * nro_bot - (button_width/2) + propsMargin,
		y: propsHeight - propsMargin - 20,
		width: button_width,
		height: 20,
		fill: 'lightgreen',
		cornerRadius: 4,
	});
	propsGroup.add(propsAceptarBack);
	var propsAceptar = new Konva.Text({
		x: ((propsWidth - 2 * propsMargin) / cant_buttons ) * nro_bot - (button_width/2) + propsMargin,
		y: propsHeight - propsMargin - 20,
		width: button_width,
		height: 20,
		text: 'Aceptar',
		fontSize: 12,
		height: propsTitleHeight,
		fill: 'black',
		align: 'center',
		verticalAlign: 'middle',
		type: 'propsButton',
		action: function(){
			updateCodigo();
		}
	});
	propsGroup.add(propsAceptar);

	controlsLayer.add(propsGroup);			

	propsGroup.on('mouseover', function (e) {
		if(e.target.attrs.type == 'propsButton'){
			stage.container().style.cursor = 'pointer';
		}				
		else if(e.target.attrs.type == 'propsTitle'){
			stage.container().style.cursor = 'move';
		}				
	});
	
	propsGroup.on('mouseout', function (e) {
		if(e.target.attrs.type == 'propsButton' || e.target.attrs.type == 'propsTitle'){
			stage.container().style.cursor = 'default';
		}				
	});
	
	propsGroup.on('click', function (e) {
		if(e.target.attrs.type == 'propsButton'){
			var button = e.target;
			stage.container().style.cursor = 'default';
			stage.find('Transformer').destroy();
			button.attrs.action();
			stage.draw();
		}				
	});
	
	updateCodigo = function(){
		shape.attrs.codigo = inputCodigo.text();
		
		var colorFondo;
		if(shape.attrs.codigo === '')
			colorFondo = 'red';
		else
		{
			if(that.mode == 'e')          //En modo edicuón va verde, de lo contrario va blanco	
				colorFondo = 'green';
			else
				colorFondo = 'white';
		}
		
		shape.attrs.fill = colorFondo; 
		shapeIndex = shape.attrs.shapeIndex;
		//Actualizar el data relacionado al shape
		that.data.pages[that.currentPosition].ClickAreas[shapeIndex].codigo = shape.attrs.codigo;
		
		that.propsEditor.destroy();
		
	};
	
	controlsLayer.draw();			
};

// ************************************* FIN PROPERTIES WINDOW ********************************** //		
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //

// *************************************** PAGINATOR ************************************ //			
BookPaginator = function(){
	var options = arguments[0] || {};
//~ console.log(options);
	
	var paginatorHeight = options.paginatorHeight || 40;
	var lMargin = 20;
	var rMargin = 20;
	var bMargin = 5;
	var tMargin = 5;	
	var layer = options.layer;
	var stage = options.stage;
	var textHeight = 50;
	var textWidth = 50;
	
	// ****** Ticks
	var arrTicks = [];
	var tickWidth = (layer.width() - lMargin - rMargin) / (that.maxPosition+1);
	for(var t = 0; t<=that.maxPosition; t+=2)
	{
		arrTicks[t] = new Konva.Rect({
			x: lMargin + t * tickWidth,
			y: layer.height() - paginatorHeight + tMargin,
			width: tickWidth,
			height: paginatorHeight - tMargin - bMargin,
			opacity: 0.3,
			fill: 'lightGray',
			cornerRadius: 6
		});
		layer.add(arrTicks[t]);
	}

	// ****** Fondo
	var paginatorBackground = new Konva.Rect({
		x: lMargin,
		y: layer.height() - paginatorHeight + tMargin,
		width: layer.width() - lMargin - rMargin,
		height: paginatorHeight - tMargin - bMargin,
		opacity: 0.5,
		fill: 'darkGray',
		cornerRadius: 6
	});
	layer.add(paginatorBackground);
	
	// ****** Linea
	var paginatorLine = new Konva.Rect({
		x: lMargin + 10,
		y: layer.height() - paginatorHeight / 2 - 10,
		width: layer.width() - lMargin - rMargin - 20,
		height: 20,
		opacity: 0.5,
		fill: 'lightGray'
	});
	layer.add(paginatorLine);


	// ****** Texto de posición de paginador
	// ************* Grupo de shapes del Editor
	var paginatorTextGroup = new Konva.Group({
			x: 100,
			y: 100,
	});
	layer.add(paginatorTextGroup);	
	paginatorTextGroup.hide();
		
	
	var paginationTextBack = new Konva.Rect({
		x: 0,
		y: 0,
		width: textHeight,
		height: textWidth,
		fill: 'DarkGray',
		cornerRadius: 4,
		stroke: 'white',
		strokeWidth: 3,
	});
	paginatorTextGroup.add(paginationTextBack);

	var paginationText = new Konva.Text({
		x: 0,
		y: 0,
		width: textWidth,
		height: textHeight,
		text: 'Pag',
		fontSize: 12,
		height: textHeight,
		fontFamily: 'Calibri',
		fontStyle: 'bold',
		fill: 'white',
		align: 'center',
		verticalAlign: 'middle'
	});
	paginatorTextGroup.add(paginationText);
	
	var triangle = new Konva.RegularPolygon({
		x: textWidth / 2,
		y: textWidth,
		sides: 3,
		radius: 5,
		fill: 'darkGray',
		stroke: 'darkGray',
		strokeWidth: 1,
		rotation: 180
	});
	paginatorTextGroup.add(triangle);

	
	layer.add(paginatorTextGroup);
	// ****** FIN Texto de posición de paginador
	  
	//Indicador de posición de paginador
	var paginatorIndicator = new Konva.Circle({
		x: stage.width() / 2,
		y: paginatorBackground.y() + paginatorBackground.height() / 2,
		radius: 16,
		fill: 'blue',
		stroke: 'lightGray',
		strokeWidth: 5,
		draggable: true,
		dragBoundFunc: function(pos) {
			if(pos.x < paginatorLine.x())
			  pos.x = paginatorLine.x();
			if(pos.x > paginatorLine.x() + paginatorLine.width())
			  pos.x = paginatorLine.x() + paginatorLine.width();
			return {
				x: pos.x,
				y: this.y()
			};
		}
	});
	layer.add(paginatorIndicator);	

	// write out drag and drop events
	paginatorLine.on('click tap', function(e) {
		if(that.changingPage == false){
			paginatorIndicator.x(e.evt.layerX);
			layer.draw();
			paginatorIndicator.fire('dragend');
		}
	});
	
	paginatorLine.on('mouseenter', function(e) {
		if(that.changingPage == false){
			paginatorLine.to({
				fill: 'white',
				duration: .2
			});
			paginatorIndicator.to({
				stroke: 'white',
				duration: .2
			});
		}
	});
	paginatorLine.on('mouseleave', function(e) {
		if(that.changingPage == false){
			paginatorLine.to({
				fill: 'lightGray',
				duration: .2
			});
			paginatorIndicator.to({
				stroke: 'lightGray',
				duration: .2
			});
		}
	});
	
	paginatorIndicator.on('mouseenter', function(e) {
		if(that.changingPage == false){
			paginatorLine.to({
				fill: 'white',
				duration: .2
			});
			paginatorIndicator.to({
				stroke: 'white',
				duration: .2
			});

			paginatorTextGroup.x(paginatorIndicator.x() - textWidth / 2);
			paginatorTextGroup.y(paginatorIndicator.y() - textHeight - 25);
			//~ var calcPosition = Math.floor(((paginatorIndicator.x() - paginatorLine.x()) / paginatorLine.width()) * that.maxPosition) + 1;
			var calcPosition = Math.floor(((paginatorIndicator.x() - paginatorLine.x()) / paginatorLine.width()) * (that.maxPosition + 1)) +1 ;
			paginationText.text(calcPosition);

			paginatorTextGroup.show();
			paginatorTextGroup.to({
				opacity: 1,
				duration: .2
			});

			//~ paginatorTextGroup.show();
			layer.draw();

		}
	});
	paginatorIndicator.on('mouseleave', function(e) {
		if(that.changingPage == false){
			paginatorLine.to({
				fill: 'lightGray',
				duration: .2
			});
			paginatorIndicator.to({
				stroke: 'lightGray',
				duration: .2
			});

			paginatorTextGroup.to({
				opacity: 0,
				duration: .2
			});

		}
	});
	
	// write out drag and drop events
	paginatorIndicator.on('dragstart', function(e) {
		if(that.changingPage == false){
			paginatorTextGroup.x(paginatorIndicator.x() - textWidth / 2);
			paginatorTextGroup.y(paginatorIndicator.y() - textHeight - 25);
			paginatorTextGroup.show();
			layer.draw();
		}
	});
	// write out drag and drop events
	paginatorIndicator.on('dragmove', function(e) {
		if(that.changingPage == false){
			paginatorTextGroup.x(paginatorIndicator.x() - textWidth / 2);
			paginatorTextGroup.y(paginatorIndicator.y() - textHeight - 25);
			//~ var calcPosition = Math.floor(((paginatorIndicator.x() - paginatorLine.x()) / paginatorLine.width()) * that.maxPosition) + 1;
			var calcPosition = Math.floor(((paginatorIndicator.x() - paginatorLine.x()) / paginatorLine.width()) * (that.maxPosition + 1)) +1 ;
			if(calcPosition > that.maxPosition + 1)
			  calcPosition = that.maxPosition + 1;
			paginationText.text(calcPosition);
			layer.draw();
		}		
	});
	paginatorIndicator.on('dragend', function() {
//~ console.log('currentPosition ' + that.currentPosition);
		if(that.changingPage == false){
			paginatorTextGroup.hide();
			layer.draw();
			//~ var calcPosition = Math.floor(((paginatorIndicator.x() - paginatorLine.x()) / paginatorLine.width()) * that.maxPosition) ;
			var calcPosition = Math.floor(((paginatorIndicator.x() - paginatorLine.x()) / paginatorLine.width()) * (that.maxPosition + 1));
			if(calcPosition > that.maxPosition )
			  calcPosition = that.maxPosition;
			that.setPage(calcPosition);
		}
	});
	
	this.updatePosition = function(){
//~ console.log(that.currentPosition);		
		var calcX = (that.currentPosition+.5) * (paginatorLine.width() / (that.maxPosition+1)) + paginatorLine.x();

		//~ var calcX = that.currentPosition * (paginatorLine.width() / that.maxPosition) + paginatorLine.x();
		paginatorIndicator.to({
			x: calcX,
		});	
		//~ layer.draw();
	}
	//FIN Indicador de posición de paginador
	
}
// ************************************* FIN PAGINATOR ********************************** //			
