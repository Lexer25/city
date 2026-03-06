<?php
//проверка подключения к базе данных. Если проверка не выполняется, то переходить на страницу ошибок.
			try
			{
				$db=Database::instance('fb')->connect();
				$query = DB::query(Database::SELECT, 'select count(*) from setting')
					->execute(Database::instance('fb'));		
		
			} catch (Exception $e) {
				$query = http_build_query(['err' => Text::limit_chars($e->getMessage(), 200)]);
				$this->redirect('errorpage' . '?' . iconv('windows-1251','UTF-8',$query));
			
			}