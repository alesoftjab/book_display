<?php

/*
FUNCIONES DE POSTA
calcularPosta: Calcula un string de 10 caracteres al azar. Retorna un string
extenderPosta: tomando el miembro y la posta extiende el tiempo de una posta si ésta es válida y no está caducada. Retorna true o false
otorgarPosta: Tomando un miembro le calcula una posta y se la asigna. Retorna la nueva posta
recambiarPosta: toma un miembro, su posta y un tiempo de caducidad. Si la posta es válida y no está caducada clcula una nueva posta, la asigna al miembro y la devuelve
*/
class EntornoGeneral
{
	var $member; 
	var $nombreMember;
	var $representado; 
	var $nombreRepresentado;
	var $posta;
	var $estado;
	var $token;
	var $avisos;
	var $libreriasCargadas;
	
	function EntornoGeneral()
	{
		$this->interpretarEntorno();
		$this->avisos=array();
		$this->libreriasCargadas=array();
		
	}
	function showAll()
	{
	  var_dump($this);	
	}
/*
 * 
 * name: interpretarEntorno
 * @param
 * @return
 */	
	function interpretarEntorno()
	{
		global $_envLink;
		global $_envForm;
		$password = $_REQUEST['password'];
		//Si viene password es login
		if($password<>'')
		{
			$this->estado='login';
			$this->member = strtoupper(trim($_REQUEST['usuario']));

			$this->validarPassword($password);
		//$this->showAll();
		}  
		//Si no viene password autentificar.
		else
		{
			
			//Interpretar el token
			$this->interpretarToken();
//				  $this->showAll();
			
			//Extender Posta
			if($this->extenderPosta()==false)
				$this->estado='no_autentificado';
			else
			{
				$this->estado='autentificado';
			
				$nuevo_representado = strtoupper(trim($_REQUEST['_representado']));
//~ echo "token: $this->token<br>";			
				if($nuevo_representado <> '')
				{
					$this->estado='cambio_representado';
					$this->validarRepresentado($nuevo_representado);
//~ echo "token: $this->token";exit;				
				}
			
			
			}
			$this->interpretarEnvVars();
			
			$_envLink = $this->envLink();
			$_envForm = $this->envForm();			
		}
		
	}
/*
 * 
 * name: getToken
 * @param
 * @return
 */
	function getToken()
	{
	  return $this->token;
	}
	
/*
 * 
 * name: generarToken
 * @param
 * @return
 */
	function generarToken()
	{
		global $_envLink;
		global $_envForm;
		$tok = $this->member . '*' . $this->posta . '*' . $this->representado;
		$tok1='';
	  for ($i = 0; $i < strlen($tok); $i++) 
	  {
	  	$char2 = substr($tok, $i, 1); 
      //$char2 << 2;
      $tok1 .=  substr( '000' . ord($char2),-3);
		}

    $num1=rand(1, 9);
    $num2=rand(1, 9);
    $num3=$num1*$num2;
    $tok1=$this->rotarIzquierda($tok1,$num3);
    $tok1=$tok1.$num1.$num2;

	  $this->token = $tok1;
	  //echo $this->token;
	  $_envLink = $this->envLink();
	  $_envForm = $this->envForm();
	}
/*
 * 
 * name: interpretarToken
 * @param
 * @return
 */
	function interpretarToken()
	{
		
	  $this->token = $_REQUEST['token'];
    $tok1=substr($this->token,0,-2);
    $num1=substr($this->token,-1);
    $num2=substr($this->token,-2,1);
    $num3=$num1*$num2;
    $tok1=$this->rotarDerecha($tok1,$num3);

	  
	  $ch="";
	  for($i=0;$i<strlen($tok1);$i+=3)
	  {
	    $nch = substr($tok1, $i,3);  
	    $ch .=  chr((1*$nch));
	  }
	  
	  $arr_token = explode('*',$ch);
	  $this->member = $arr_token[0];
	  $this->posta = $arr_token[1];
	  $this->representado = $arr_token[2];
	}

  function validarCliente()
  {
    $ip_invalidas=array('193.200.150.137');//,'190.229.13.234'
    $user_agent_invalidos=array('http://Anonymouse.org/ (Unix)');
    
    $cliente_valido=true;
	  $ip=$this->getIP();
	  if(in_array($ip, $ip_invalidas))
	    $cliente_valido=false;

	  if(in_array($_SERVER['HTTP_USER_AGENT'], $user_agent_invalidos))
	    $cliente_valido=false;

    return $cliente_valido;
  }

	function validarPassword($password)
	{
		global $conn;
	  global $nombreusuario;
	  global $categoriausu;
	  global $_galleta_sesion_valor;
	  global $_galleta_sesion_eterna_valor;
	  global $cfg;
	  
	  sleep(5);
	  $ip=$this->getIP();

	  $cliente_valido = $this->validarCliente();
     
 	  $usuario = $this->member;
	  if($cliente_valido==true)
	  {
  	  $qrystr = "select * from member where cod_member = '" . $usuario . "'";
  	  //echo $qrystr;
			try 
			{
				$data = $conn->query($qrystr);
				if($data->rowCount() > 0) 
				{
					
					$row = $data->fetch(PDO::FETCH_ASSOC);
					$nivel=$row[nivel];
					$validacion_localizacion = $row['validacion_localizacion'];  //[ '', 'EMPRESA','ALGUNAIP' ]
					//~ $this->doLog($usuario,'login',"LELELELELELELELEL","ip $ip pass $password usuario $usuario - validacion_localizacion: $validacion_localizacion - cfg[arr_ip_empresa]:"  . str_replace("'",'' ,var_export($cfg['arr_ip_empresa'],true)));
					//Si $validacion_localizacion no está vacío validar según el caso
					if($validacion_localizacion <> '')
					{
						$ip=$this->getIP();

						//Si validacion_localizacion es EMPRESA validar que la ip del login sea una de las de la empresa
						if($validacion_localizacion == 'EMPRESA' and is_array($cfg['arr_ip_empresa']))
						{
							if(!in_array($ip, $cfg['arr_ip_empresa']))
							{
								$this->doLog($usuario,'login',"LOCALIZ NO VALIDA","ip $ip pass $password usuario $usuario");
								return "";
							}
						}
						//Si validacion_localizacion es una IP validar que la ip del login sea la del usuario
						elseif($ip <> $validacion_localizacion)
						{
							$this->doLog($usuario,'login',"LOCALIZ NO VALIDA","ip $ip pass $password usuario $usuario");
							return "";
						}	
					}

					if($nivel==100)
					{
						$this->doLog($usuario,'login',"Usuario baja","ip $ip pass $password usuario $usuario");
						return "";
					}
					$this->nombreMember = "$row[nombres] $row[apellido]";
					if ($password == $row[psw])
					{
						//ECHO "SI";
						$this->doLog($usuario,'login','aceptado',"ip $ip - ses $_galleta_sesion_valor");
						$this->doLog($usuario,'login','contador_gral',"ip $ip - ses $_galleta_sesion_eterna_valor");
						return $this->otorgarPosta($usuario);
					}
					else
					{
						$this->doLog($usuario,'login','pass no aceptado',"ip $ip pass $password");
						return "";
						//ECHO "NO";
					}					
				}
				else
				{
					$this->doLog($usuario,'login','usu no aceptado',"ip $ip pass $password");
					return "";
				}
				//$arr_ret = array("estado" => true, "descripcion"=>'', 'datos' => $filas);	
				
				return $arr_ret;

			} 
			catch(PDOException $e) 
			{
				echo'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
				return "";
				//$arr_ret = array("estado" => false, "descripcion"=>'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr", 'datos' => array());	
			}										 
    }
    else
    {
  	  sleep(5);
 		  $this->doLog($usuario,'login','cliente invalido',"pass $password " . $_SERVER['HTTP_USER_AGENT'] . " " . $_SERVER['REQUEST_URI']);
      return "";
    }
	}
	
	function validarRepresentado($representado)
	{
		global $conn;
	  global $nombreusuario;
	  global $categoriausu;
	  global $cfg;
	  

		$qrystr = " select m.cod_member, m.apellido, m.nombres
								from member_representaciones AS mr
								  INNER JOIN member AS m ON mr.representado = m.cod_member
								where representante = '$this->member' AND representado= '$representado'";
//~ echo $qrystr;exit;

		try 
		{
			$data = $conn->query($qrystr);
			if($data->rowCount() > 0) 
			{
				$row = $data->fetch(PDO::FETCH_ASSOC);

				$this->representado = $row[cod_member];
				$this->nombreRepresentado = "$row[nombres] $row[apellido]";

				return $this->otorgarPosta($usuario);
			}
			else
			{
				$this->doLog($usuario,'cambio_representado','representado no aceptado',"ip $ip representado $representado");
				return "";
			}     
		}
		catch(PDOException $e) 
		{
			echo'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			return "";
			//$arr_ret = array("estado" => false, "descripcion"=>'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr", 'datos' => array());	
		}										 
	}

	function otorgarPosta($usuario='')
	{
		global $conn;
		if($usuario=='')
		  $usuario = $this->member;
		$clave_sesion = $this->calcularPosta();
		$qrystr = "update member set
							 fec_last_acc = CURRENT_TIMESTAMP,
							 clave_ses = '" . $clave_sesion . "',
							 cant_acc = cant_acc + 1,
							 time_ses = " . date('U') .
							" where cod_member = '" . $usuario . "'";
		$count = $conn->exec($qrystr);					
		$this->posta = $clave_sesion;
		$this->generarToken();
	}


/*
 * 
 * name: calcularPosta
 Calcula un string de 10 caracteres al azar
 * @param
 largo: la cantidad de caracteres del string. Por defecto es 10.
 * @return el string de 10 caracteres
 */
	function calcularPosta($largo_clave=10)
	{
		$letters = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		return substr(str_shuffle($letters), 0, $largo_clave);
	}
/*
 * 
 * name: rotarIzquierda
 Rota a la izquierda una cadena
 * @param
 texto: 
 iteraciones: 
 * @return el string de rotado
 */
	function rotarIzquierda($texto,$iteraciones)
	{
    for($i=0;$i<$iteraciones;$i++)
    {
      $parte1=substr($texto ,1);
      $parte2=substr($texto , 0,1);
      $texto=$parte1.$parte2;
    }
		
    return $texto;
	}

/*
 * 
 * name: rotarDerecha
 Rota a la izquierda una cadena
 * @param
 texto: 
 iteraciones: 
 * @return el string de rotado
 */
	function rotarDerecha($texto,$iteraciones)
	{
    for($i=0;$i<$iteraciones;$i++)
    {
      $parte1=substr($texto ,-1);
      $parte2=substr($texto , 0,-1);
      $texto=$parte1.$parte2;
    }
		
    return $texto;
	}
	
	/*
	 * 
	 * name: extenderPosta
	 extiende el tiempo de una posta sin cambiarla
	 * @param
	 posta
	 miembro
	 * @return
	 */	
	function extenderPosta()
	{
		global $conn;
		global $tiempo;
		global $c_database;

	  $usuario=$this->member;
	  $representado=$this->representado;
	  $sesion=$this->posta;


		$ses_ok =false;

	  $cliente_valido = $this->validarCliente();
	  if($cliente_valido ==false)
	  {
 		  $this->doLog($usuario,'login','cliente invalido',"ep posta $sesion " . $_SERVER['HTTP_USER_AGENT'] . " " . $_SERVER['REQUEST_URI'] ." _POST " . str_replace("'","\'",var_export($_POST,1)) . " _COOKIE ". str_replace("'","\'",var_export($_COOKIE,1))  . " _GET ". str_replace("'","\'",var_export($_GET,1)) );
	    return $ses_ok;
    }

		$qrystr = "select * from member where cod_member = '" . $usuario . "'";
		try 
		{
			$data = $conn->query($qrystr);
			$row = $data->fetch(PDO::FETCH_ASSOC);

			$nivel=$row[nivel];
			if($nivel==100)
			{
				$this->doLog($usuario,'login',"Usuario baja","ip $ip pass $password usuario $usuario");
				return "";
			}
			
			if ($sesion == $row[clave_ses])
			{
  			$this->nombreMember = "$row[nombres] $row[apellido]";
				$tiempo_act = date("U");
				$trans =($tiempo_act - $row[time_ses]);
				if ($trans <= ($tiempo))
				{
					$qrystr = "update member set time_ses = " . $tiempo_act . " where cod_member = '" . $usuario . "'";
					$count = $conn->exec($qrystr);
					$ses_ok = 1;
					
					if($this->representado <> '')
					{
						$qrystr = "select * from member where cod_member = '" . $representado . "'";
						$data = $conn->query($qrystr);
						$row = $data->fetch(PDO::FETCH_ASSOC);
						$this->nombreRepresentado = "$row[nombres] $row[apellido]";
					}

				}
			}
		}
		catch(PDOException $e) 
		{
			echo'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			return "";
			//$arr_ret = array("estado" => false, "descripcion"=>'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr", 'datos' => array());	
		}

		return $ses_ok;
	}


	function recambiarPosta($tiempo_caducidad)
	{
		global $conn;
		
	  $usuario=$this->member;
	  $sesion=$this->posta;
		$ses_ok=false;

	  $cliente_valido = $this->validarCliente();
	  if($cliente_valido ==false)
	  {
 		  $this->doLog($usuario,'login','cliente invalido',"rp posta $sesion " . $_SERVER['HTTP_USER_AGENT'] . " " . $_SERVER['REQUEST_URI']);
	    return $ses_ok;
    }

		$qrystr = "select * from member where cod_member = '" . $usuario . "'";
		try 
		{
			$data = $conn->query($qrystr);
			$row = $data->fetch(PDO::FETCH_ASSOC);

			if ($sesion == $row[clave_ses])
			{
				$tiempo_act = date("U");
				$trans =($tiempo_act - $row[time_ses]);
				if ($trans <= ($tiempo_caducidad))
				{   
					$clave_sesion = $this->calcularPosta();
					$qrystr = "update member
												 set clave_ses = '$clave_sesion',
															time_ses =  $tiempo_act
												 where cod_member = '$usuario'";
					$count = $conn->exec($qrystr);
					$ses_ok = $clave_sesion;
					$this->posta=$ses_ok;
				}
			}
			$this->generarToken();
			return $ses_ok;
		}
		catch(PDOException $e) 
		{
			echo'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			return "";
			//$arr_ret = array("estado" => false, "descripcion"=>'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr", 'datos' => array());	
		}										 
	}

	function caducarPosta()
	{
		global $conn;
		
	  $usuario=$this->member;
	  $sesion=$this->posta;
		$ses_ok=false;


	  $cliente_valido = $this->validarCliente();
	  if($cliente_valido ==false)
	  {
 		  $this->doLog($usuario,'login','cliente invalido',"cp posta $sesion " . $_SERVER['HTTP_USER_AGENT'] . " " . $_SERVER['REQUEST_URI']);
	    return $ses_ok;
    }

		$qrystr = "select * from member where cod_member = '" . $usuario . "'";
		try 
		{
			$data = $conn->query($qrystr);
			$row = $data->fetch(PDO::FETCH_ASSOC);

			if ($sesion == $row[clave_ses])
			{
				$tiempo_act = date("U");
				$trans =($tiempo_act - $row[time_ses]);
				if ($trans <= ($tiempo_caducidad))
				{   
					$clave_sesion = $this->calcularPosta();
					$qrystr = "update member
												 set clave_ses = '$clave_sesion',
															time_ses =  $tiempo_act
												 where cod_member = '$usuario'";
					$count = $conn->exec($qrystr_dd);
					$ses_ok = $clave_sesion;
				}
			}
			return 1;			
		}
		catch(PDOException $e) 
		{
			echo'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
			return "";
			//$arr_ret = array("estado" => false, "descripcion"=>'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr", 'datos' => array());	
		}										 
	}
	
	function envLink($par_link='')
	{
		return $par_link . "&token=" . $this->token;
	}

	function envForm($par_form='')
	{
		return $par_form . "<input type=hidden name=token value='$this->token'>";
	}

	function interpretarEnvVars()
	{
	}
	
	function doLog($codus,$area,$accion,$detalle)
	{
	 global $conn;
	 $ip=$this->getIP();
	 $qrystr = "INSERT INTO `logs` (`cod_member`, `fecha`, `area`, `accion`, `detalle`, `ip`) VALUES ('$codus', NOW(), '$area', '$accion', '$detalle', '$ip');";
	 $count = $conn->exec($qrystr);
	}

	function renovarEntorno($sesion)
	{
	 echo"<script>_GC_.cambiarSesion('$sesion');</script>";
	}
	function inicializarEntorno($sesion)
	{
		echo"
		<script type='text/javascript'>
		_gestorContenido = function()
		{
			this.sesion='$sesion';
			this.cambiarSesion=function(nuevoValor)
			{
				this.sesion = nuevoValor;  
			};
			this.navegar=function(url)
			{
				location.href = url + '&sesion=' + this.sesion;
			
			};
			
		}
		var _GC_ = new _gestorContenido(); 
		</script>
		";	
	}

	function getIP()
	{
		if (isset($_SERVER))
		{
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else
			{
				return $_SERVER['REMOTE_ADDR'];
			}
		}
		else
		{
			if (isset($GLOBALS['HTTP_SERVER_VARS']['HTTP_X_FORWARDER_FOR']))
			{
				return $GLOBALS['HTTP_SERVER_VARS']['HTTP_X_FORWARDED_FOR'];
			}
			else
			{
				return $GLOBALS['HTTP_SERVER_VARS']['REMOTE_ADDR'];
			}
		}
	}
		

	function libreriaCargada($parLibreria)
	{
		if(in_array($parLibreria,$this->libreriasCargadas))
			return true;
		else
			return false;
	}	
	
	function agregarLibreriaCargada($parLibreria)
	{
		$this->libreriasCargadas[] = $parLibreria;
	}	

	function agregarAviso($parAviso,$nivel=1)
	{
		$this->avisos[] = array('texto'=>$parAviso,'nivel'=>$nivel);
	}	

	function mostrarAvisos($parIdDivAvisos)
	{
		$buf='';
		$bufjs='';
		
		foreach($this->avisos as $aviso)
		{
		  $buf .= "<div class='aviso_nivel$aviso[nivel]'>$aviso[texto]</div>";
		}

		$bufjs .= "<script>";
		$bufjs .= "var divAvisos = document.getElementById('$parIdDivAvisos');";
		$bufjs .= "divAvisos.innerHTML = \"". str_replace("\'", "\\'",$buf) ."\";";
		$bufjs .= "</script>";
		
		if(sizeof($this->avisos)>0)
		  return $bufjs;
		else
		  return '';
	}		

}//FIN CLASE


?>
