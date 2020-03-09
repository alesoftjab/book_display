<div id="upload_progress" class="p-3 m-2 bg-info text-white">
	Aún no se ha subido el PDF del catálogo. Por favor seleccione el archivo PDF.
</div>

<div id="div_continuar" class="d-none p-3 m-2">
	<button type="button" id="btn_continuar" class=" btn btn-success btn-lg" >
		<span aria-hidden="true"><i class="fas fa-images"></i> Páginas</span>
	</button>
</div>

<button type="button" id="browseButton" class="btn btn-outline-primary btn-lg">
	Seleccionar archivo
</button>

<script src="js/resumable.js"></script>
<script>
	
showUploadProgress = function(parText){
	$('#upload_progress').html(parText);
};

var r = new Resumable({
  target: 'index.php?id_book=<?php echo $id_book;?>&_c_=books&_a_=grabar_importar_paginas_resumable&token=<?php echo $_ent->getToken() ?>&randomica=' + Math.random()
});
r.assignBrowse(document.getElementById('browseButton'));


r.on('fileSuccess', function(file){
	//~ console.debug('fileSuccess',file);
	showUploadProgress('archivo Subido exitosamente');
});
r.on('fileProgress', function(file){
	//~ console.debug('fileProgress', file);
	showUploadProgress('Subiendo archivo. Luego de subirlo se procesa cada página. Esto puede tardar algunos minutos. Por favor espere...' +
												'<div class="spinner-grow text-light" role="status">' +
												'	<span class="sr-only">Loading...</span>' +
												'</div>'	
	
	);

	//~ showUploadProgress('Subiendo archivo. Parte ' + file[] + ' de ' + file[] + '. Esto puede tardar algunos minutos. Luego de subirlo se procesa cada página. Por favor espere...');

});
r.on('fileAdded', function(file, event){
	r.upload();
	//~ console.debug('fileAdded', event);
	$('#browseButton').toggleClass('d-none');
});
r.on('filesAdded', function(array){
	r.upload();
	//~ console.debug('filesAdded', array);
});
r.on('fileRetry', function(file){
	//~ console.debug('fileRetry', file);
	showUploadProgress('Reintentando archivo...');
});
r.on('fileError', function(file, message){
	//~ console.debug('fileError', file, message);
	showUploadProgress('Error archivo');
});
r.on('uploadStart', function(){
	//~ console.debug('uploadStart');
	showUploadProgress('Iniciando subida de archivo');
});
r.on('complete', function(){
	//~ console.debug('complete');
	showUploadProgress('Terminado');
	$('#div_continuar').toggleClass('d-none');
});
r.on('progress', function(){
	//~ console.debug('progress');
});
r.on('error', function(message, file){
	console.debug('error', message, file);
	showUploadProgress('Error: ' + message);
});
r.on('pause', function(){
	//~ console.debug('pause');
});
r.on('cancel', function(){
	//~ console.debug('cancel');
});


$('#btn_continuar').on( 'click', function () {

	var id_book = '<?php echo $id_book;?>';

	window.location.href = "index.php?token=<?php echo $_ent->getToken();?>&_c_=books&_a_=editar_paginas&randomica=" + Math.random() + "&id_book=" + id_book;


});

</script>
