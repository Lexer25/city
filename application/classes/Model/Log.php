<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Log extends Model
{
	public function getList()// подготовка списка файлов
	{
		$files=$this->getDirectoryTree(Kohana::$config->load('artonitcity_config')->dir_log, 'log');
		return $files;
	}
	public function getListCompare()// подготовка списка файлов
	{
		$files=$this->getDirectoryTree(Kohana::$config->load('artonitcity_config')->dir_compare, 'csv');
		return $files;
	}
	
	public function send_file ($name)// скачать указанный файл
	{
		$file = $name;
		header ("Content-Type: application/force-download");
		header ("Accept-Ranges: bytes");
		header ("Content-Length: ".filesize($file));
		header ("Content-Disposition: attachment; filename=".basename($file));  
		readfile($file);
		return basename($file);
	}
	
	function getDirectoryTree( $outerDir , $x)
	{ 
		$res=array();
		if(file_exists($outerDir))
		{
		
			$dirs = array_diff(scandir( $outerDir ), Array( ".", ".." ) );
			$res=array();
			foreach ($dirs as $key=>$values)
			{
				if (stripos($values, $x)) $res[]=$values;
			}
			
		}
		
		return $res; 
	}
}
