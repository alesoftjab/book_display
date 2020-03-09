<?php
require_once 'src/books.mdl.php';

class BooksCtrlr
{
	
	private $model;
	
	// ********************************** CONSTRUCTOR **************************************** //
	public function __CONSTRUCT()
	{
		$this->model = new Books();
	}
	
	// ********************************** Index **************************************** //
	public function Index()
	{
		global $_ent;
		require_once 'header.view.php';
		require_once 'src/books_lista.view.php';
		require_once 'footer.view.php';
		
	}

	// ********************************** Index **************************************** //
	public function xlsBooks()
	{
		global $_ent;
		global $conn;

		$requestData = $_REQUEST;
		$fecha_desde = $requestData['fecha_desde'];
		$fecha_hasta = $requestData['fecha_hasta'];

		require_once 'src/books.xls.view.php';
		
	}

	// // ********************************** Index **************************************** //
	// public function Imprimir()
	// {
	// 	global $_ent;

	// 	$requestData = $_REQUEST;

	// 	require_once 'src/comprobantes_lista.pdf.view.php';
		
	// }

	// ************************ armarFiltrosObligatorios ******************************* //
	/*
	Este método interpreta los filtros obligatorios desde el request
	* Retorno: array('nombreFiltro'=>'valorFiltro')
	*/ 
	public function armarFiltrosObligatorios()
	{
		$requestData = $_REQUEST;

		$arrFiltrosObligatorios = array();

		return($arrFiltrosObligatorios);
	}

	// ************************ armarFiltrosOpcionales ******************************* //
	/*
	Este método interpreta los filtros opcionales desde el request
	* Retorno: array('nombreFiltro'=>'valorFiltro')
	*/ 
	public function armarFiltrosOpcionales()
	{
		$requestData = $_REQUEST;

		
		$arrFiltrosOpcionales = array();
		if($requestData['filtro_unico'] <> '')
		  $arrFiltrosOpcionales['filtro_unico'] = desEncriptarID($requestData['filtro_unico']);
		if($requestData['filtro_fecha'] <> '')
		  $arrFiltrosOpcionales['filtro_fecha'] = $requestData['filtro_fecha'];
		if($requestData['tipo_listado'] <> '')
		  $arrFiltrosOpcionales['tipo_listado'] = $requestData['tipo_listado'];
		if($requestData['id_comprobante'] <> '')
		  $arrFiltrosOpcionales['id_comprobante'] = $requestData['id_comprobante'];
		if($requestData['filtro_txt'] <> '')
		  $arrFiltrosOpcionales['filtro_txt'] = $requestData['filtro_txt'];
		return($arrFiltrosOpcionales);
	}
	

	// ************************ formatearFilas ******************************* //
	/*
	Este método formatea algunos datos de las columnas obtenidas desde el modelo. Recibe 
	* Parámetros: $rows es un array de registros. Se toma por referencia
	* Retorno: void. Pero modifica por referencia el valor de $rows
	*/ 
	public function formatearFilas(&$rows)
	{
		foreach($rows AS &$row)
		{
			//Asignar htmlentities para que JSON no falle
			foreach($row AS &$campo)
				$campo =  htmlentities( (string) $campo);
			//FIN Asignar htmlentities para que JSON no falle

			$row['id_book'] = encriptarID($row["id_book"]);
			//~ $row['url_imagen'] = $this->getUrlImagen($row["nombre_imagen"]);

		}
	}


	// ************************ getLista ******************************* //
	/*
	Este método devuelve una lista de datos en json para que sean representados en la vista que está en el book
	*/ 
	public function getLista()
	{
		$requestData = $_REQUEST;
		
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();

		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = $this->armarFiltrosOpcionales();
		
		// *********** PAGINACIÓN ****************** //
		$arrPaginacion = array('start' => $requestData['start'], 'length' => $requestData['length']); 

	  //getFilasParaDataTables($arrFiltrosObligatorios=array(), $arrFiltrosOpcionales=array(), $arrPaginacion=array())
		$resultados = $this->model->getFilasParaDataTables($arrFiltrosObligatorios, $arrFiltrosOpcionales, $arrPaginacion);
		

		// ************************ PREPARAR ALGUNOS VALORES DE COLUMNAS *************************//
		$this->formatearFilas($resultados['rows']);
		
		$json_data = array(
					"draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
					"recordsTotal"    => intval( $resultados['totalData'] ),  // total number of records
					"recordsFiltered" => intval( $resultados['totalFiltered'] ), // total number of records after searching, if there is no searching then totalFiltered = totalData
					"data"            => $resultados['rows']   // total data array
					);

		echo json_encode($json_data);  // send data as json format
	}
		
	// ************************ getUno ******************************* //
	/*
	Este método devuelve un único elemento y puede imprimirlo en json para que sea representados en la vista que está en el book o utilizad por otro método
	Parámetros:
	* $parImprimir: Si es true hace echo del registro en formato json
	* Retorno: el registro como array tal como lo devuelve el modelo ( a veces se usa y a veces no )
	*/ 
	public function getUno($parImprimir=true)
	{
		//Llamar al modelo para obtener los datos con el id_orden_det que vino como parámetro
		// ************* Rellenado de parámetros obligatorios ******************** //
		$arrFiltrosObligatorios = $this->armarFiltrosObligatorios();

		// ************* Rellenado de parámetros opcionales ******************** //
		$arrFiltrosOpcionales = $this->armarFiltrosOpcionales();
		$rows = $this->model->getFilas(array_merge($arrFiltrosObligatorios, $arrFiltrosOpcionales));

		// ************************ PREPARAR ALGUNOS VALORES DE COLUMNAS *************************//
		$this->formatearFilas($rows);

		$row = $rows[0];
		//~ foreach($row AS &$campo)
		  //~ $campo =  htmlentities( (string) $campo);

		$json_data_encoded = json_encode($row);
		
		if($parImprimir==true)
  		echo $json_data_encoded;  // send data as json format
		
		return($row);  // return data as json format
		
	}
	
	public function formAbm()
	{
		global $_ent;
		$requestData = $_REQUEST;

		$id_book    = $requestData['id_book'];
		$row = $this->model->getDatosParaForm(desEncriptarID($id_book));
		
		$row['id_book'] = $id_book;
		//var_dump($row);
		require_once 'src/books_form_ABM.view.php';
	}


	public function grabar()
	{
		global $_ent;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];
		$id_book = desEncriptarID($id_book);
		$titulo=$requestData['titulo']; 
		$book_topics = $requestData['book_topics']; 
		$url_image_base = $requestData['url_image_base']; 
		$pageHeight = $requestData['pageHeight'];
		$pageWidth = $requestData['pageWidth'];
		$home_url = $requestData['home_url'];
		
		//Actualizar las cantidades por elemento
		$arr_ret = $this->model->grabar($id_book, $titulo, $book_topics, $url_image_base, $pageHeight, $pageWidth, $home_url);

		$json_data = array(
					"estado"         => $arr_ret['estado'],   	//
					"descripcion"    => $arr_ret['descripcion']
					);
		echo json_encode($json_data);  // send data as json format
	}
	
	public function exportar()
	{
		global $_ent;
		require_once 'src/shop_carts.ctrlr.php';
		
		
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];
		$id_book = desEncriptarID($id_book);
		
		$rows_libro = $this->model->getFilas(array('filtro_unico'=>$id_book));
		$row_libro = $rows_libro[0];
		$id_book_ver = $row_libro['id_book_ver'];
		
		//Obtener el json de BookData
		ob_start();
		$this->getBookData($id_book);              //Esto internamente hace un echo del json del BookData
		//Almacaner el buffer en una variable que se usa despus
		$strBookData = ob_get_contents();
		//Fin de buffer
		ob_end_clean();


		//Obtener el json de ShopCart
		$ctrlrShopCart = new ShopCartsCtrlr();
		//Obtener el json de BookData
		ob_start();
		$ctrlrShopCart->getShopCartData($id_book);  //Esto internamente hace un echo del json del BookData
		//Almacaner el buffer en una variable que se usa despus
		$strShopCart = ob_get_contents();
		//Fin de buffer
		ob_end_clean();

		//Generar el zip
		$nombre_archivo = "export_book_".$id_book_ver."_".date('Y-m-d');

		//Abrir archivo zip
		$zip = new ZipArchive;
		$res = $zip->open($nombre_archivo.'.zip', ZipArchive::CREATE);
		if ($res === TRUE) {

			//Camino interno del zip para el book
			$path_book = "book$id_book_ver";
			//Camino a la plantilla
			$path_plantilla = "../book_display_catalogos/PLANTILLA";
			
			//Agregar cada json al zip
			$zip->addFromString($path_book . '/book_data.js', $strBookData);
			$zip->addFromString($path_book . '/get_shop_cart_data.js', $strShopCart);
			
			//Agregar los archivos de la plantilla
			$files = $this->getDirContents($path_plantilla);	
			foreach($files AS $file)
				$zip->addFile($file, $path_book . substr($file,strlen($path_plantilla)));

			//Agregar las imágenes
			$url_image_base = $row_libro['url_image_base'];
			if($url_image_base<>'')
			{
				$files = $this->getDirContents($url_image_base);	
				foreach($files AS $file)
					$zip->addFile($file, $path_book . '/' . $file);
			}
			
			$zip->close();

			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename='.$nombre_archivo.'.zip');
			header('Content-Length: ' . filesize($nombre_archivo.'.zip'));
			readfile($nombre_archivo.'.zip');
			unlink($nombre_archivo.'.zip');

		} else {
			echo 'failed';
		}


		
	}
	
	public function getDirContents($path) {
			$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

			$files = array(); 
			foreach ($rii as $file)
					if (!$file->isDir())
							$files[] = $file->getPathname();

			return $files;
	}

	
	public function editarPaginas()
	{
		global $_ent;
		$requestData = $_REQUEST;

		$id_book    = $requestData['id_book'];

		$page    = $requestData['page'];
		if($page == 0)
			$page=1;
		
		//Si no tiene páginas redireccionar al método de importar pdf
		$retorno = $this->model->getBookData(desEncriptarID($id_book));
		if($retorno['estado'] == true)
		{
			$bookData = $retorno['bookData'];
			if(sizeof($bookData['pages']) == 0)
			{
				//~ $this->formImportarPaginas(desEncriptarID($id_book));
				$this->formImportarPaginasResumable(desEncriptarID($id_book));
			}
			else
			{
				$row = $this->model->getDatosParaForm(desEncriptarID($id_book));
						
				//var_dump($row);
				require_once 'header.view.php';
				require_once 'src/books_editar_paginas.view.php';
				require_once 'footer.view.php';
			}
		}
	}


	public function importarPrecios()
	{
		global $_ent;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];
		
		//~ require_once 'header.view.php';
		require_once 'src/importar_precios.view.php';
		//~ require_once 'footer.view.php';
	}

	public function grabarImportarPrecios()
	{
		global $_ent;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];
		$id_book = desEncriptarID($id_book);
		$strseparador   			= $requestData['strseparador'];
		if(isset($_FILES['file'])){
			$filename = $_FILES['file']['name'];
			if(isset($filename) && !empty($filename))
			{
				echo 'IMPORTANDO<br>';
					
				$tmpFileName =  $_FILES['file']['tmp_name'];
				
				$this->model->importarPrecios($id_book, $tmpFileName, $strseparador);

				echo 'FINALIZADO<br>';
					
			}
			else
			{
				echo 'please choose a file';
			}
		}
		else
		{
			echo 'Elija un archivo';
		}		
		
	}

	public function formImportarPaginas($id_book)
	{
		global $_ent;
		
		$id_book = encriptarID($id_book);

		require_once 'header.view.php';
		require_once 'src/books_importar_paginas.view.php';
		require_once 'footer.view.php';
	}
	
	public function grabarImportarPaginas()
	{
		global $_ent;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];
		$id_book = desEncriptarID($id_book);
		if(isset($_FILES['file'])){
			$filename = $_FILES['file']['name'];
			if(isset($filename) && !empty($filename))
			{
				echo 'IMPORTANDO<br>';
					
				$tmpFileName =  $_FILES['file']['tmp_name'];
				$images_folder = "images_books/$id_book";
				if (!file_exists($images_folder)) 
				{
					mkdir($images_folder, 0777, true);
				}

				echo"Carpeta: $images_folder<br>";		

				$imagick = new Imagick($tmpFileName);

				$cantidad = $imagick->getNumberImages();

				for($i=0; $i<$cantidad;$i++)
				{
					$imagick->readImage($tmpFileName.'[' . $i . ']');
					$width = $imagick->getImageWidth();
					$height = $imagick->getImageHeight();
					$imagick = $imagick->flattenImages();
					$nombre_archivo = 'page' . $i . '.jpg';
					$imagick->writeImage($images_folder . '/' . $nombre_archivo);
					
					$this->model->addPage($id_book, $nombre_archivo, $images_folder, $height, $width);
					
					echo"Generando " . $images_folder . '/' . $nombre_archivo . "<br>"; 
				}


					
			}
			else
			{
				echo 'please choose a file';
			}
		}
		else
		{
			echo 'Elija un archivo';
		}		
		
	}
	
	// ******************************************************************************************************************** //
	// ******************************************************************************************************************** //
	
	public function formImportarPaginasResumable($id_book)
	{
		global $_ent;
		
		$id_book = encriptarID($id_book);

		require_once 'header.view.php';
		require_once 'src/books_importar_paginas_resumable.view.php';
		require_once 'footer.view.php';
	}
	
	
	
	public function grabarImportarPaginasResumable()
	{
		global $_ent;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];
		$id_book = desEncriptarID($id_book);

		////////////////////////////////////////////////////////////////////
		// THE SCRIPT
		////////////////////////////////////////////////////////////////////

		//check if request is GET and the requested chunk exists or not. this makes testChunks work
		if ($_SERVER['REQUEST_METHOD'] === 'GET') 
		{

			if(!(isset($_GET['resumableIdentifier']) && trim($_GET['resumableIdentifier'])!=''))
			{
				$_GET['resumableIdentifier']='';
			}
			$temp_dir = 'temp/'.$_GET['resumableIdentifier'];
			if(!(isset($_GET['resumableFilename']) && trim($_GET['resumableFilename'])!=''))
			{
				$_GET['resumableFilename']='';
			}
			if(!(isset($_GET['resumableChunkNumber']) && trim($_GET['resumableChunkNumber'])!=''))
			{
				$_GET['resumableChunkNumber']='';
			}
			$chunk_file = $temp_dir.'/'.$_GET['resumableFilename'].'.part'.$_GET['resumableChunkNumber'];
			if (file_exists($chunk_file)) 
			{
				header("HTTP/1.0 200 Ok");
			} 
			else 
			{
				header("HTTP/1.0 404 Not Found");
			}
		}

		// loop through files and move the chunks to a temporarily created directory
		if (!empty($_FILES)) foreach ($_FILES as $file) {

			// check the error status
			if ($file['error'] != 0) 
			{
				_log('error '.$file['error'].' in file '.$_POST['resumableFilename']);
				continue;
			}

			// init the destination file (format <filename.ext>.part<#chunk>
			// the file is stored in a temporary directory
			if(isset($_POST['resumableIdentifier']) && trim($_POST['resumableIdentifier'])!='')
			{
				$temp_dir = 'temp/'.$_POST['resumableIdentifier'];
			}
			$dest_file = $temp_dir.'/'.$_POST['resumableFilename'].'.part'.$_POST['resumableChunkNumber'];

			// create the temporary directory
			if (!is_dir($temp_dir)) 
			{
				mkdir($temp_dir, 0777, true);
			}

			// move the temporary file
			if (!move_uploaded_file($file['tmp_name'], $dest_file)) 
			{
				_log('Error saving (move_uploaded_file) chunk '.$_POST['resumableChunkNumber'].' for file '.$_POST['resumableFilename']);
			} 
			else 
			{
				$final_dir = './temp/';
				// check if all the parts present, and create the final destination file
				$this->createFileFromChunks($final_dir, $temp_dir, $_POST['resumableFilename'],$_POST['resumableChunkSize'], $_POST['resumableTotalSize'],$_POST['resumableTotalChunks']);

				//AHORA APLICAR EL IMAGICK
				$tmpFileName =  $final_dir . '/' . $_POST['resumableFilename'];
				$images_folder = "images_books/$id_book";
				if (!file_exists($images_folder)) 
				{
					mkdir($images_folder, 0777, true);
				}

				echo"Carpeta: $images_folder<br>";		

				$imagick = new Imagick($tmpFileName);
//~ $imagick->setResolution(300,300);
				$cantidad = $imagick->getNumberImages();

				for($i=0; $i<$cantidad;$i++)
				{
					$imagick->readImage($tmpFileName.'[' . $i . ']');
					$width = $imagick->getImageWidth();
					$height = $imagick->getImageHeight();

//~ $imagick->setImageResolution(300,300);
//~ $imagick->resampleImage(300,300,imagick::FILTER_UNDEFINED,1);
$imagick->setImageFormat('jpg');
$imagick->setImageCompression(imagick::COMPRESSION_JPEG); 
$imagick->setImageCompressionQuality(100);

					$imagick = $imagick->flattenImages();
					$nombre_archivo = 'page' . $i . '.jpg';
					$imagick->writeImage($images_folder . '/' . $nombre_archivo);
					
					$this->model->addPage($id_book, $nombre_archivo, $images_folder, $height, $width);
					
					echo"Generando " . $images_folder . '/' . $nombre_archivo . "<br>"; 
				}
				unlink($tmpFileName);
			}
		}
	}

	private function rrmdir($dir) 
	{
    if (is_dir($dir)) 
    {
			$objects = scandir($dir);
			foreach ($objects as $object) 
			{
				if ($object != "." && $object != "..") 
				{
					if (filetype($dir . "/" . $object) == "dir") 
					{
						rrmdir($dir . "/" . $object); 
					} 
					else 
					{
						unlink($dir . "/" . $object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
    }
	}
	
	/**
	 *
	 * Check if all the parts exist, and 
	 * gather all the parts of the file together
	 * @param string $temp_dir - the temporary directory holding all the parts of the file
	 * @param string $fileName - the original file name
	 * @param string $chunkSize - each chunk size (in bytes)
	 * @param string $totalSize - original file size (in bytes)
	 */
	private function createFileFromChunks($final_dir, $temp_dir, $fileName, $chunkSize, $totalSize,$total_files) {
		// count all the parts of this file
		$total_files_on_server_size = 0;
		$temp_total = 0;
		foreach(scandir($temp_dir) as $file) 
		{
			$temp_total = $total_files_on_server_size;
			$tempfilesize = filesize($temp_dir.'/'.$file);
			$total_files_on_server_size = $temp_total + $tempfilesize;
		}
		// check that all the parts are present
		// If the Size of all the chunks on the server is equal to the size of the file uploaded.
		if ($total_files_on_server_size >= $totalSize) 
		{
			// create the final destination file 
			if (($fp = fopen($final_dir.'/'.$fileName, 'w')) !== false) 
			{
				for ($i=1; $i<=$total_files; $i++) 
				{
					fwrite($fp, file_get_contents($temp_dir.'/'.$fileName.'.part'.$i));
					$this->_upload_log('writing chunk '.$i);
				}
				fclose($fp);
			} 
			else 
			{
				$this->_upload_log('cannot create the destination file');
				return false;
			}

			// rename the temporary directory (to avoid access from other 
			// concurrent chunks uploads) and than delete it
			if (rename($temp_dir, $temp_dir.'_UNUSED')) 
			{
				$this->rrmdir($temp_dir.'_UNUSED');
			} 
			else 
			{
				$this->rrmdir($temp_dir);
			}
		}
	}	

	/**
	 *
	 * Logging operation - to a file (upload_log.txt) and to the stdout
	 * @param string $str - the logging string
	 */
	function _upload_log($str) {

		// log to the output
		$log_str = date('d.m.Y').": {$str}\r\n";
		echo $log_str;

		// log to file
		if (($fp = fopen('upload_log.txt', 'a+')) !== false) {
			fputs($fp, $log_str);
			fclose($fp);
		}
	}
	
	// ******************************************************************************************************************** //
	// ******************************************************************************************************************** //
	
	
	public function getBookData()
	{
		global $_ent;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];

		$id_book = desEncriptarID($id_book);

		$retorno = $this->model->getBookData($id_book);
		
		$retorno['bookData']['id_book'] = encriptarID($retorno['bookData']['id_book']);
		
		echo json_encode($retorno);  // send data as json format

	}
	
	public function setBookData()
	{
		global $_ent;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];
		$id_book = desEncriptarID($id_book);
		
		$bookData = json_decode(stripslashes($requestData['bookData']),true);

		$retorno = $this->model->setBookData($id_book, $bookData);
		
		echo json_encode($retorno);  // send data as json format

	}

	public function registrarAcceso()
	{
		global $_ent;
		$requestData = $_REQUEST;
		$id_book = $requestData['id_book'];
		$id_book = desEncriptarID($id_book);
		$page_number = $requestData['page_number'];
		$telefono_vendedor = $requestData['vendor_phone'];		
		$ip = $_ent->getIP();
		
		$retorno = $this->model->registrarAcceso($id_book, $page_number, $ip, $telefono_vendedor);
		
		echo json_encode($retorno);  // send data as json format

	}

	public function registrarVenta()
	{
		global $_ent;
		$requestData = $_REQUEST;
		$id_book = $requestData['id_book'];
		$id_book = desEncriptarID($id_book);
		$telefono_vendedor = $requestData['vendor_phone'];		
		$ip = $_ent->getIP();
		
		
		$items = json_decode(stripslashes($requestData['items']),true);

//~ var_dump($ventaData);exit;
		$retorno = $this->model->registrarVenta($id_book, $ip, $telefono_vendedor, $items);
		
		echo json_encode($retorno);  // send data as json format

	}


	// ********************************** Index **************************************** //
	public function xlsCierre()
	{
		global $_ent;
		global $conn;

		$requestData = $_REQUEST;
		$id_apertura = $requestData['id_apertura'];

		require_once 'src/aperturas_cierre.xls.view.php';
		
	}


	public function exportarPreciosXls()
	{
		global $_ent;
		global $conn;
		$requestData      = $_REQUEST;
		$id_book   			= $requestData['id_book'];

		//~ $id_book = desEncriptarID($id_book);

		require_once 'src/exportar_precios.xls.view.php';

	}


}
