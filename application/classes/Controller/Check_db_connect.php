<?php
//проверка подключения к базе данных. Если проверка не выполняется, то переходить на страницу ошибок.
			try
			{
				$db=Database::instance('fb')->connect();
				$query = DB::query(Database::SELECT, 'select count(*) from setting')
					->execute(Database::instance('fb'));		
		
			} catch (Exception $e) {
				//(Database_Exception $e)
				$this->redirect('errorpage?err='.Text::limit_chars($e->getMessage()));
			}