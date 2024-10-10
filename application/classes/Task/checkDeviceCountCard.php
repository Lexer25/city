    <?php defined('SYSPATH') or die('No direct script access.');
     
    /**
     * 
     * c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=checkDeviceCountCard
     * @author 
     */
     
    class Task_checkDeviceCountCard extends Minion_Task {
		
        
        protected function _execute(array $params)
        {
				//$devList=Model::factory('device')->getDoorList(2);
				//Log::instance()->add(Log::NOTICE, Debug::vars($devList));exit;
				$devList=array(
				'id_dev'=>array(
					'324' =>  '324',
					'325' =>  '325',
					//'108' =>  '108',
					//'109' =>  '109',
					//'111' =>  '111',
					//'112' =>  '112',
					//'114' =>  '114',
					//'115' =>  '115',
					//'117' =>  '117',
					//'118' =>  '118',
					//'120' =>  '120'
					),
				'readkey' => "1"
				);
				
				$devList1=array(
					'582' =>  '582',
					'583' =>  '583',
					'108' =>  '108',
					'109' =>  '109',
					'111' =>  '111',
					'112' =>  '112',
					'114' =>  '114',
					'115' =>  '115',
					'117' =>  '117',
					'118' =>  '118',
					'120' =>  '120'
					);
				
				foreach($devList as $_key=>$_value)
				{
					$door=new Door($_key);
					$dev= new Device($door->parent);
					$door->getKeyList();

					$ts2client=new TS2client();
					$ts2client->startServer();
					$t1=microtime(true);
					foreach ($door->getKeyList() as $key=>$value)
					{
						
						$message='t56 exec device="'.$dev->name.'", command="readkey key=""'.$key.'"", door='.$door->reader.'"';
						$ts2client->sendMessage($message);
						$ts2client->readMessage();
						if(is_null(strpos ($ts2client->devAnswer, 'Access=Yes')))
						{
							//echo Debug::vars('41 '.$key.' '. $this->devanswer);
							Log::instance()->add(Log::NOTICE, 'Карты '.$key.' нет в контроллере '.$dev->name.' '.$dev->id);
						
						}
									
					}
				
				Log::instance()->add(Log::NOTICE, 'Проверка в контроллере '.$dev->name.' '.$dev->id' завершена. Проверено '. count($door->getKeyList().' карт за время '.microtime(true)-$t1));
				}	
		
		
		
		}
    }