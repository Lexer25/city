<?php defined('SYSPATH') or die('No direct script access.');

//class Controller_Errorpage extends Controller_Template {
class Controller_odbc extends Controller{
	
	public function action_index()
	{
		
		$conn = odbc_connect("SDUO", "sysdba", "temp");
		$sql = "SELECT count(*) from card";
			$rs = odbc_exec($conn,$sql);
			odbc_fetch_row($rs);
			echo odbc_result($rs, 1);

			odbc_close($conn);
		
	}
	
	
	
}

