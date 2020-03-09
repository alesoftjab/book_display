<form id=myform class=form-horizontal role=form method=POST>
 	<input type=hidden name=id_book id=id_book value="<?php echo $row['id_book'] ?>">
  <input type=hidden name=_c_ value=books>
  <input type=hidden name=_a_ value=grabar>
  <input type=hidden name=token value="<?php echo $_ent->getToken() ?>">

<?php
  
    echo "
			<div class='form-row'>
      	<div class='form-group col-md-4'>
	      	<label for=titulo class='col-form-label'>Título:</label>
	      </div>
	      <div class='col'>
	        <input type='text' class='form-control' id='titulo' name='titulo' required='required' value='".$row[titulo]."'>
	      </div>

	    </div>
			<div class='form-row'>
	      
      	<div class='form-group col-md-4'>
	      	<label for=book_topics class='col-form-label'>Tópicos:</label>
	      </div>
	      <div class='col'>
	        <input type='text' class='form-control' id='book_topics' name='book_topics' required='required' value='".$row[book_topics]."'>
	      </div>
	      
	    </div>
			<div class='form-row'>
	      
      	<div class='form-group col-md-4'>
	      	<label for=home_url class='col-form-label'>Home Url:</label>
	      </div>
	      <div class='col'>
	        <input type='text' class='form-control' id='home_url' name='home_url' required='required' value='".$row[home_url]."'>
	      </div>
	      
	    </div>
			<div class='form-row'>

      	<div class='form-group col-md-4'>
	      	<label for=url_image_base class='col-form-label'>URL  image base:</label>
	      </div>
	      <div class='col'>
	        <input type='text' class='form-control' id='url_image_base' name='url_image_base' required='required' value='".$row[url_image_base]."'>
	      </div>
	      
	    </div>
			<div class='form-row'>

      	<div class='form-group col-md-4'>
	      	<label for=pageHeight class='col-form-label'>Alto Imagen:</label>
	      </div>
	      <div class='col'>
	        <input type='text' class='form-control' id='pageHeight' name='pageHeight' required='required' value='".$row[pageHeight]."'>
	      </div>
	      
	    </div>
			<div class='form-row'>

      	<div class='form-group col-md-4'>
	      	<label for=pageWidth class='col-form-label'>Ancho Imagen:</label>
	      </div>
	      <div class='col'>
	        <input type='text' class='form-control' id='pageWidth' name='pageWidth' required='required' value='".$row[pageWidth]."'>
	      </div>
	      
	    </div>

      ";

?>

	<div id="error">
		<div class="alert alert-danger"> <strong>Error!</strong> Hay errores en el formulario!</div>
	</div>

	<div class=modal-footer>
		<button type="button" class="btn btn-primary" id="submitForm">Aceptar</button>
	</div>
</form>
<script>
$("input[type='text']").on("click", function () {
   $(this).select();
});

// Si es una modificación, lleno los campos con los valores que me llegan en $row
if($('#id_book').val() != '') {
  $('#tipo_doc').val(<?php echo $row['tipo_doc']?>);
}

$('#error').hide();
$('#submitForm').click(function (e) {
	e.preventDefault();
	$('#error').hide();
	// $('cantidad').focus();

	$.ajax({
		type: 'POST',
		url: 'index.php',
		data: $('#myform').serialize(),
		success: function (data) {

			var respuesta = $.parseJSON(data);
			if(respuesta.estado!=true)
			{
				$('#error').html('<div class=\"alert alert-danger\"><strong>Error: </strong>' + respuesta.descripcion + '</div>');
				$('#error').show();
			}
			else
			{
				BstrapModal.Close();
				//$('_id_modal').modal('hide');
			}
		},
		error: function () {
			$('#error').show();
		}
	});
});



</script>
