<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_mqtt extends Model {
	
	
	public function send_message($mess)
	{
		//echo Debug::vars('8', $mess); exit;
		$server = '194.87.237.67';     // change if necessary
		$port = 1883;                     // change if necessary
		$username = '1';                   // set your username
		$password = '1';                   // set your password
		$client_id = 'phpMQTT-publisher'; // make sure this is unique for connecting to sever - you could use uniqid()

		$mqtt = new phpMQTT($server, $port, $client_id);
		if ($mqtt->connect(true, NULL, $username, $password)) {
			$mqtt->publish('/test/tema', $mess . ' ' . date('r'), 0, false);
			$mqtt->close();
			$res=' Connect OK, send = hz.';
		} else {
			$res = "Error. Time out!\n";
			
		}
		return $res;
	}
	
	
	
	
	
	
	
}
