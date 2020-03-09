<div id="formulario" class="jumbotron vertical-center">

	<div id="container" class="container">
		<h1>Cambio de Contraseña</h1>
		<form id="myform">
			<input type=hidden name=_c_ value=usuarios>
			<input type=hidden name=_a_ value=contrasenia_grabar>
			<input type=hidden name=token value="<?php echo $_ent->getToken() ?>">
			<ul>
				<li>
					<label for="pswd">Nueva Contraseña:</label>
					<span><input id="pswd" type="password" name="contrasenia" autocomplete="false" readonly onfocus="this.removeAttribute('readonly');"></span>
				</li>
				<li>
					<label for="pswd1">Repetir Contraseña:</label>
					<span><input id="pswd1" type="password" name="contrasenia1" autocomplete="false" readonly onfocus="this.removeAttribute('readonly');"></span>
				</li>
			</ul>
			<div id="error">
				<div class="alert alert-danger"> <strong>Error!</strong> Hay errores en el formulario!</div>
			</div>


			<div class=modal-footer>
				<button type=button class="btn btn-primary" id=submitForm>Aceptar</button>
			</div>
			
		</form>

		<div id="pswd_info" style="display: none;">
			<h4>La contraseña debe contener:</h4>
			<ul>
				<li id="letter" class="invalid">Al menos <strong>una letra</strong></li>
				<li id="capital" class="invalid">Al menos <strong>una mayúscula</strong></li>
				<li id="number" class="invalid">Al menos <strong>un número</strong></li>
				<li id="length" class="invalid">Al menos <strong>8 caracteres</strong></li>
			</ul>
		</div>
	</div>



</div>

<div id="exito" class=" alert alert-success" >
	<br><br><br><br><br><br><br>
	<strong>LA CONTRASEÑA SE HA CAMBIADO EXITOSAMENTE</strong>
	<br><br><br><br><br><br><br>
</div>


<style>
.vertical-center {
  min-height: 80%;  /* Fallback for browsers do NOT support vh unit */
  min-height: 80vh; /* These two lines are counted as one :-)       */

  display: flex;
  align-items: center;
}

ul, li {
	margin:0; 
	padding:0; 
	list-style-type:none;
}

#container {
	width:400px; 
	padding:0px; 
	background:#fefefe; 
	margin:0 auto; 
	border:1px solid #c4cddb;
	border-top-color:#d3dbde;
	border-bottom-color:#bfc9dc;
	box-shadow:0 1px 1px #ccc;
	border-radius:5px;
	position:relative;
}

h1 {
	margin:0;
	padding:10px 0;
	font-size:24px; 
	text-align:center;
	background:#eff4f7;
	border-bottom:1px solid #dde0e7;
	box-shadow:0 -1px 0 #fff inset;
	border-radius:5px 5px 0 0; /* otherwise we get some uncut corners with container div */
	text-shadow:1px 1px 0 #fff;
}

form ul li {
	margin:10px 20px;
	
}
form ul li:last-child {
	text-align:center;
	margin:20px 0 25px 0;
}
input {
	padding:10px 10px;
	border:1px solid #d5d9da;
	border-radius:5px;
	box-shadow: 0 0 5px #e8e9eb inset;
	width:328px; /* 400 (parent) - 40 (li margins) -  10 (span paddings) - 20 (input paddings) - 2 (input borders) */
	font-size:1em;
	outline:0; /* remove webkit focus styles */
}
input:focus {
	border:1px solid #b9d4e9;
	border-top-color:#b6d5ea;
	border-bottom-color:#b8d4ea;
	box-shadow:0 0 5px #b9d4e9;
}
label {
	color:#555;
}
#container span {
	background:#f6f6f6;
	padding:3px 5px;
	display:block;
	border-radius:5px;
	margin-top:5px;
}


/* Styles for verification */
#pswd_info {
	position:absolute;
	bottom:-0px;
	bottom: -30px\9;
	right:55px;
	width:250px;
	padding:15px;
	background:#fefefe; 
	font-size:.875em;
	border-radius:5px;
	box-shadow:0 1px 3px #ccc;
	border:1px solid #ddd;
	display:none;
}
#pswd_info::before {
	content: "\25B2";
	position:absolute;
	top:-12px;
	left:45%;
	font-size:14px;
	line-height:14px;
	color:#ddd;
	text-shadow:none;
	display:block;
}
#pswd_info h4 {
	margin:0 0 10px 0; 
	padding:0;
	font-weight:normal;
}

.invalid {
	background:url(images/invalid.png) no-repeat 0 50%;
	padding-left:22px;
	line-height:24px;
	color:#ec3f41;
}
.valid {
	background:url(images/valid.png) no-repeat 0 50%;
	padding-left:22px;
	line-height:24px;
	color:#3a7d34;
}
</style>

<script>
$(document).ready(function() {

  //you have to use keyup, because keydown will not catch the currently entered value
  $('input[id=pswd]').keyup(function() {

    // set password variable
    var pswd = $(this).val();

    //validate the length
    if (pswd.length < 8) {
      $('#length').removeClass('valid').addClass('invalid');
    } else {
      $('#length').removeClass('invalid').addClass('valid');
    }

    //validate letter
    if (pswd.match(/[A-z]/)) {
      $('#letter').removeClass('invalid').addClass('valid');
    } else {
      $('#letter').removeClass('valid').addClass('invalid');
    }

    //validate uppercase letter
    if (pswd.match(/[A-Z]/)) {
      $('#capital').removeClass('invalid').addClass('valid');
    } else {
      $('#capital').removeClass('valid').addClass('invalid');
    }

    //validate number
    if (pswd.match(/\d/)) {
      $('#number').removeClass('invalid').addClass('valid');
    } else {
      $('#number').removeClass('valid').addClass('invalid');
    }

  }).focus(function() {
    $('#pswd_info').show();
  }).blur(function() {
    $('#pswd_info').hide();
  });

});

$('#error').hide();
$('#exito').hide();
$('#submitForm').click(function (e) {
	e.preventDefault();
	$('#error').hide();
	$('#exito').hide();

	//Si alguna de las validaciones falla entonce sno submitear
	var pswd = $("#pswd").val();
	var submitear = true;
	if (pswd.length < 8) 
		submitear = false;
	if (!pswd.match(/[A-z]/)) 
		submitear = false;
	if (!pswd.match(/[A-Z]/)) 
		submitear = false;
	if (!pswd.match(/\d/)) 
		submitear = false;
	//FIN Si alguna de las validaciones falla entonce sno submitear
	
	if(submitear == true)
	{
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
					$('#formulario').hide();
					$('#exito').show();
				}
			},
			error: function () {
				$('#error').show();
			}
		});		
	}
	else
	{
		$('#pswd_info').show();
	}

});

</script>
