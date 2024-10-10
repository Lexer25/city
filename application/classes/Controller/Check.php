<?
class Controller_check extends Controller_Template {

public function before()
	{
			parent::before();
			$session = Session::instance();
	}



public function action_index()
	{
		$_SESSION['menu_active']='check';
		$server_list=Model::factory('Check')->getServerList();
		$content = View::factory('check', array(
			'server_list' => $server_list,
		));
        $this->template->content = $content;
		
	}
	
public function action_selector()
	{
		
		$_SESSION['menu_active']='check';
		$_SESSION['door']=Arr::get($_POST, 'door', 0);
		$_SESSION['cellfrom']=Arr::get($_POST, 'cellfrom', 0);
		$_SESSION['cellto']= (Arr::get($_POST, 'cellto')==0) ? 10 : Arr::get($_POST, 'cellto') ;
		$_SESSION['cellfrom_write']=Arr::get($_POST, 'cellfrom_write', 0);
		$_SESSION['cellto_write']= (Arr::get($_POST, 'cellto_write')==0) ? 10 : Arr::get($_POST, 'cellto_write') ;

		
		
		if(Arr::get($_POST, 'getDeviceList')) // получить список устройств из транспортного сервера.
		{
			$device_list=Model::factory('Check')->getDeviceListFromServer(Arr::get($_POST, 'id_server'));
			
			$server_list=Model::factory('Check')->getServerList();
			$server_select=Arr::get($_POST, 'id_server', 'no');
			$content = View::factory('check', array(
			'server_list' => $server_list,
			'device_list' => $device_list,
			'server_select' => $server_select,
			));
			
		}
		
		if(Arr::get($_POST, 'read_data_from_device')) // вычитать все данные из указанного контроллера
		{
			echo Debug::vars('25', $_POST); exit;
			$device_name=Arr::get($_POST, 'device_name');
			$id_server=Arr::get($_POST, 'id_server');
			$res=Model::factory('Check')->readKeyFromDevice($device_name, $id_server);
			$content = View::factory('result', array(
				'content' => $res,
			));
		}
		
		if(Arr::get($_POST, 'write_data_to_device')) // вычитать все данные из указанного контроллера
		{
			$device_name=Arr::get($_POST, 'device_name');
			$id_server=Arr::get($_POST, 'id_server');
			$res=Model::factory('Check')->writeKeyToDevice($device_name, $id_server);
			$content = View::factory('result', array(
				'content' => $res,
			));
		}
		
		
		
		
        $this->template->content = $content;
		
	}


}