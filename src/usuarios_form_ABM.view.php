<form id=myform class=form-horizontal role=form method=POST>
  <input type=hidden name=cod_member value="<?php echo $row[cod_member] ?>">
  <input type=hidden name=_c_ value=usuarios>
  <input type=hidden name=_a_ value=grabar>
  <input type=hidden name=token value="<?php echo $_ent->getToken() ?>">

	<div class="form-group row">
		<div class=col-md-12>
			<label class=control-label popup-label>Código</label>
      <div class='col-sm-8'>
				<input required type=text class=form-control name=nuevousuario id=nuevousuario autocomplete='off' value="<?php echo $row[cod_member];?>" <?php echo ($row['cod_member']<>''?'readonly':'');?> >
      </div>
    </div>

		<div class=col-md-12>
			<label class=control-label popup-label>Apellido</label>
      <div class='col-sm-8'>
				<input required type=text class=form-control name=apellido id=apellido autocomplete='off' value="<?php echo $row[apellido] ?>">
      </div>
    </div>
        
		<div class=col-md-12>
			<label class=control-label popup-label>Nombres</label>
      <div class='col-sm-8'>
				<input required type=text class=form-control name=nombres id=nombres autocomplete='off' value="<?php echo $row[nombres] ?>">
      </div>
    </div>

		<div class=col-md-12>
			<label class=control-label popup-label>Tipo Doc</label>
      <div class='col-sm-8'>
				<select class='form-control' id='tipo_doc_id' name='tipo_doc_id'>";
<?php
	$tipos = '';
	foreach ($tipos_doc as $tipo => $valor) {
		$tipos .="<option value='".$valor[tipo_doc_id]."'>".$valor[descri_tipo_doc]."</option>";
	}
	echo"$tipos";
?>			
				</select>
			</div>
		</div>
	
		<div class=col-md-12>
			<label class=control-label popup-label>Número Doc</label>
      <div class='col-sm-8'>
				<input required type=text class=form-control name=nro_doc id=nro_doc autocomplete='off' value="<?php echo $row[nro_doc] ?>">
			</div>
		</div>

		<div class=col-md-12>
			<label class=control-label popup-label>Perfil</label>
      <div class='col-sm-8'>
				<select class='form-control' id='profile' name='profile'>";
<?php
	$tipos = '';
	foreach ($profiles as $tipo => $valor) {
		$tipos .="<option value='".$valor[id_profile]."'>".$valor[nombre]."</option>";
	}
	echo"$tipos";
?>			
				</select>
			</div>
		</div>
		
		<div class=col-md-12>
			<label class=control-label popup-label>Cliente</label>
      <div class='col-sm-8'>
				<select class='form-control' id='id_cliente' name='id_cliente'>
					<option value='0'>---</option>
<?php
	$tipos = '';
	foreach ($clientes as $tipo => $valor) {
		$tipos .="<option value='".$valor[id_cliente]."'>".$valor[nombre]."</option>";
	}
	echo"$tipos";
?>			
				</select>
			</div>
		</div>
		
	</div>
	
	<div id="error">
		<div class="alert alert-danger"> <strong>Error!</strong> Hay errores en el formulario!</div>
	</div>

	<div class=modal-footer>
		<button type=button class=btn btn-primary id=submitForm>Aceptar</button>
		<button type=button class=btn btn-default data-dismiss=modal id=reset>Cancelar / Borrar</button>
	</div>
</form>

<script>

// Si es una modificación, lleno los campos con los valores que me llegan en $row
if($('#cod_member').val() != '') {
  $('#tipo_doc_id').val(<?php echo $row[tipo_doc_id]?>);
  $('#profile').val(<?php echo $row[profile]?>);
  $('#id_cliente').val(<?php echo $row[id_cliente]?>);
}


$('#error').hide();
$('#submitForm').click(function (e) {
	e.preventDefault();
	$('#error').hide();
	$('cantidad').focus();

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
