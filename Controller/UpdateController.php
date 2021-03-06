<?php
/**
 * iroha Board Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2016 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohaboard.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
App::uses('AppController', 'Controller');

class UpdateController extends AppController
{
	var $name = 'Update';
	var $uses = array();
	var $helpers = array('Html');
	
	public $components = array(
		'Session',
		'Auth' => array(
			'allowedActions' => array(
				'index',
				'error',
			)
		)
	);
	
	function index()
	{
		try
		{
			App::import('Model','ConnectionManager');

			$db = ConnectionManager::getDataSource('default');
			$err_statements = $this->__executeSQLScript($db, APP.DS.'Config'.DS.'update.sql');
			
			if(count($err_statements) > 0)
			{
				$body = '以下のクエリの実行中にエラーが発生しました。<ul>';
				
				foreach($err_statements as $err)
				{
					$body .= '<li>'.$err.'</li>';
				}
				
				$body .= '</ul>';
				
				$this->error($body);
				$this->render('error');
				return;
			}
		}
		catch (Exception $e)
		{
			$this->error('データベースへの接続に失敗しました。<br>Config / database.php ファイル内のデータベースの設定を確認して下さい。');
			$this->render('error');
		}
	}
	
	function error($body)
	{
		$this->set('loginURL', "/users/login/");
		$this->set('loginedUser', $this->Auth->user());
		$this->set('body', $body);
	}
	
	private function __executeSQLScript($db, $fileName)
	{
		//echo "__executeSQLScript()<br>";
		
		$statements = file_get_contents($fileName);
		$statements = explode(';', $statements);
		$err_statements = array();
		
		foreach ($statements as $statement)
		{
			if (trim($statement) != '')
			{
				try
				{
					$db->query($statement);
				}
				catch (Exception $e)
				{
					$err_statements[count($err_statements)] = $statement;
				}
			}
		}
		
		return $err_statements;
	}
}
?>