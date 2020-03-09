<form id="myForm" class=form-horizontal role=form method=POST name="myForm"> 

	<input type="file" name="file" required />
	<input type="button" id="uploadBTN" value="IMPORTAR"></input>

</form>

<div id="output"></div>

<script>
$(function(){
	$('#uploadBTN').on('click', function(){ 
		var formData = new FormData();
		formData.append('file', $('input[type=file]')[0].files[0]);
		formData.append('id_book', "<?php echo $id_book;?>");
		formData.append('_c_', "books");
		formData.append('_a_', "grabar_importar_paginas");
		formData.append('token', "<?php echo $_ent->getToken() ?>");
		//fd.append("CustomField", "This is some extra data");
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: formData,
			success:function(data){
				$('#output').html(data);
			},
			cache: false,
			contentType: false,
			processData: false
		});
	});
});
</script>
