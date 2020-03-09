<?php
/* Database connection start */
$_servername = "localhost";
$_username = "USERNAME";
$_password = "PASSWORD";
$_dbname = "DATABASE";

try 
{
	$conn = new PDO("mysql:host=$_servername;dbname=$_dbname", $_username, $_password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch(PDOException $e) 
{
  echo 'Error conectando con la base de datos: ' . $e->getMessage();
}
/* Database connection end */

// ********************************************************************** //
$tiempo =6000;

?>
