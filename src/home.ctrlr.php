<?php
require_once 'src/home.mdl.php';

class HomeCtrlr
{
	
	private $model;
	
	// ********************************** CONSTRUCTOR **************************************** //
	public function __CONSTRUCT()
	{
		$this->model = new Home();
	}
	
	// ********************************** Index **************************************** //
	public function Index()
	{
		global $_ent;
		require_once 'header.view.php';
		require_once 'src/home.view.php';
		require_once 'footer.view.php';
	}

}
