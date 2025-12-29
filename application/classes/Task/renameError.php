    <?php defined('SYSPATH') or die('No direct script access.');
/*
удаление "лишних" записей в ошибках.
c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=renameError --id_dev=469
*/
 
    class Task_renameError extends Minion_Task {
		
		    protected $_options = array(
        // param name => default value
     
        'id_dev'   => '107',
		);
	
        
        protected function _execute(array $params)
        {
			$sql='select  cdx.id_card, cdx.id_dev, cdx.load_result from cardidx cdx
			where cdx.load_result containing \'trans\'';
			
			$query = DB::query(Database::SELECT, $sql)
				->execute(Database::instance('fb'));
				
			foreach($query as $key=>$value){

				//echo Debug::vars('26', $value); //exit;
				//echo Debug::vars('26', Arr::get($value, 'LOAD_RESULT')); //exit;
				//$string = 'Trans922 ERR ErrClass=ParamError, ErrDesc="Device "1" not found.", ErrSource="TS"';
				$result = strstr(Arr::get($value, 'LOAD_RESULT'), 'ERR ErrClass');
				//echo Debug::vars('26', $result); exit;
				
				$sql=__('update cardidx cdx
					set cdx.load_result=\':result\'
					where cdx.id_card=\':card\'
					and cdx.id_dev=:id_dev',
					array(
					':result'=>$result,
					':card'=>Arr::get($value, 'ID_CARD'),
					':id_dev'=>Arr::get($value, 'ID_DEV'),
					));
				$query = DB::query(Database::UPDATE, $sql)
				->execute(Database::instance('fb'));


			}

			echo Debug::vars('47 the emd'); exit;	
				
        }
		
    }
	