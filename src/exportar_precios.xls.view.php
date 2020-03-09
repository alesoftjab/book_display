<?php
require_once ($GLOBALS[cfg][paso_libreria_general] . "/excel_avanzado/class.writeexcel_workbook.inc.php");
require_once ($GLOBALS[cfg][paso_libreria_general] . "/excel_avanzado/class.writeexcel_worksheet.inc.php");

function numero_a_letra_xls($numero){
  if($numero>26){
     $resto =$numero;
     $letra1 = (int)($numero/26);
     $resto = $resto - (26*$letra1);

     $letra1=chr(64+$letra1);
     $letra2=chr(64+$resto);
     $letra = "$letra1$letra2";
  }
  else{
    $letra = chr(64+$numero);
  }
    return $letra;
}

$fname = tempnam("/tmp", "simple.xls");
$workbook = new writeexcel_workbook($fname);

//colores
$celeste = $workbook->set_custom_color(40, 165, 209, 211);
$verde = $workbook->set_custom_color(41, 172, 249, 180);



# The general syntax is write($row, $column, $token). Note that row and
# column are zero indexed
#
# Create a format for the column headings
$header =& $workbook->addformat();
$header->set_bold();
$header->set_size(12);
$header->set_color('white');
$header->set_bg_color('blue');
$header->set_align('center');

$header_celeste =& $workbook->addformat();
$header_celeste->set_bold();
$header_celeste->set_size(12);
$header_celeste->set_bg_color($celeste);
$header_celeste->set_align('center');

# Create a "vertical justification" format
$format1 =& $workbook->addformat();
$format1->set_align('right');
$format1->set_bg_color($verde);
# Create a "vertical justification" format
$format2 =& $workbook->addformat();
$format2->set_align('left');

$format3 =& $workbook->addformat();
$format3->set_align('right');

//***************************** HOJA RESUMEN ***************************************************

$worksheet = &$workbook->addworksheet("PRECIOS");
$worksheet->set_column(0, 1, 20);
$worksheet->set_column(2, 2, 70);
$worksheet->set_column(3, 3, 20);

$id_book = desEncriptarID($id_book);
$qrystr = "	SELECT p.codigo_parte, p.codigo_completo, p.descripcion, p.precio_unitario 
									FROM `books_items_shopcart` AS p
								WHERE p.id_book ='$id_book'
								";                

$result = $conn->query($qrystr);

$cant_campos =  $result->columnCount();

//encabezados
for ($i = 0; $i < $cant_campos; $i++) 
{
  $col = $result->getColumnMeta($i);
  $worksheet->write(0, $i, $col['name'], $header_celeste);
}

//datos
$j=1;
while($row = $result->fetch(PDO::FETCH_NUM)) 	//Filas
{   
  for($i=0;$i<$cant_campos;$i++)        //Columnas
  {
		if($i>0 and $i<>$cant_campos-1)
	    $worksheet->write($j, $i,$row["$i"],$format_numero);
	  elseif($i==$cant_campos-1)
	  	$worksheet->write($j, $i,$row["$i"],$format_numero_celeste);
	  else 
		  $worksheet->write($j, $i,$row["$i"],$format2);
  }
  $j++;
}

$workbook->close();

header("Content-Type: application/x-msexcel; name=\"precios_".$id_book.".xls\"");
header("Content-Disposition: inline; filename=\"precios_".$id_book.".xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);

?>
