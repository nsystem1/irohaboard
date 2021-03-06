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
App::uses('Record', 'Record');

/**
 * ContentsQuestions Controller
 *
 * @property ContentsQuestion $ContentsQuestion
 * @property PaginatorComponent $Paginator
 */
class ContentsQuestionsController extends AppController
{

	public $components = array(
			'Paginator'
	);

	public function index($id, $record_id = null)
	{
		$this->ContentsQuestion->recursive = 0;
		$contentsQuestions = $this->ContentsQuestion->find('all', 
				array(
						'conditions' => array(
								'content_id' => $id
						),
						'order' => array('ContentsQuestion.sort_no' => 'asc')
				));
		
		// 管理者以外の場合、コンテンツの閲覧権限の確認
		if($this->Session->read('Auth.User.role') != 'admin')
		{
			$this->loadModel('Course');
			
			if(count($contentsQuestions) > 0)
			{
				if(! $this->Course->hasRight($this->Session->read('Auth.User.id'), $contentsQuestions[0]['Content']['course_id']))
				{
					throw new NotFoundException(__('Invalid access'));
				}
			}
		}
		
		// 過去の成績を取得
		if ($record_id)
		{
			$this->loadModel('Record');
			$record = $this->Record->find('first', 
					array(
							'conditions' => array(
									'Record.id' => $record_id
							)
					));
			
			$this->set('mode',   "record");
			$this->set('record', $record);
			// debug($record);
			// echo "get record";
		}
		else
		{
			$this->set('mode',   "test");
		}
		
		// 採点処理
		if ($this->request->is('post'))
		{
			$details = array();
			$full_score = 0;
			$pass_score = 0;
			$my_score = 0;
			$pass_rate = 0;
			
			// 成績の詳細情報の作成
			$i = 0;
			foreach ($contentsQuestions as $contentsQuestion)
			{
				$question_id = $contentsQuestion['ContentsQuestion']['id'];
				$answer = @$this->request->data['answer_' . $question_id];
				$correct = $contentsQuestion['ContentsQuestion']['correct'];
				$is_correct = ($answer == $correct) ? 1 : 0;
				$score = $contentsQuestion['ContentsQuestion']['score'];
				
				$full_score += $score;
				$pass_rate = $contentsQuestion['Content']['pass_rate'];
				
				if ($is_correct == 1)
					$my_score += $score;
					
					// echo $answer.'<br>';
					// echo
					// $this->request->data['ContentsQuestion']['correct_'.$contentsQuestion['ContentsQuestion']['id']].'<br>';
					// echo $contentsQuestion['ContentsQuestion']['score'];
					// echo '<hr>';
				$details[$i] = array(
						'question_id' => $question_id,
						'answer' => $answer,
						'correct' => $correct,
						'is_correct' => $is_correct,
						'score' => $score
				);
				$i ++;
			}
			
			$pass_score = ($full_score * $pass_rate) / 100;
			
			$record = array(
					'full_score' => $full_score,
					'pass_score' => $pass_score,
					'score' => $my_score,
					'is_passed' => ($my_score >= $pass_score) ? 1 : 0,
					'study_sec' => $this->request->data['ContentsQuestion']['study_sec']
			);
			
			$this->loadModel('Record');
			$this->Record->create();
			
			// debug($this->Record);
			$data = array(
					'user_id' => $this->Session->read('Auth.User.id'),
					'course_id' => $this->Session->read('Iroha.course_id'),
					'content_id' => $id,
					'full_score' => $record['full_score'],
					'pass_score' => $record['pass_score'],
					'score' => $record['score'],
					'is_passed' => $record['is_passed'],
					'study_sec' => $record['study_sec'],
					'is_complete' => 1
			);
			
			if ($this->Record->save($data))
			{
				$this->loadModel('RecordsQuestion');
				$record_id = $this->Record->getLastInsertID();
				
				foreach ($details as $detail)
				:
					$this->RecordsQuestion->create();
					$detail['record_id'] = $record_id;
					$this->RecordsQuestion->save($detail);
				endforeach
				;
				
				$this->redirect(
						array(
								'action' => 'record',
								$id,
								$this->Record->getLastInsertID()
						));
			}
		}
		
		$this->loadModel('Content');
		$content = $this->Content->find('first', 
				array(
						'conditions' => array(
								'Content.id' => $id
						)
				));
		
		
		$is_record = (($this->action == 'record') || ($this->action == 'admin_record'));
		
		$this->set('course_name',       $this->Session->read('Iroha.course_name'));
		$this->set('content_title',     $content['Content']['title']);
		$this->set('content_timelimit', $content['Content']['timelimit']);
		$this->set('contentsQuestions', $contentsQuestions);
		$this->set('is_record',         $is_record);
	}

	public function record($id, $record_id)
	{
		$this->index($id, $record_id);
		$this->render('index');
	}

	public function admin_record($id, $record_id)
	{
		$this->index($id, $record_id);
		$this->render('index');
	}

	public function admin_index($id)
	{
		$this->ContentsQuestion->recursive = 0;
		$contentsQuestions = $this->ContentsQuestion->find('all', 
				array(
						'conditions' => array(
								'content_id' => $id
						),
						'order' => array('ContentsQuestion.sort_no' => 'asc')
				));
		
		// コースの情報を取得
		$this->loadModel('Content');
		
		$content = $this->Content->find('first',
				array(
						'conditions' => array(
								'Content.id' => $id
						)
				));
		
		$this->Session->write('Iroha.content_id',   $id);
		$this->Session->write('Iroha.content_name', $content['Content']['title']);
		
		$this->set('course_name',   $this->Session->read('Iroha.course_name'));
		$this->set('contentsQuestions', $contentsQuestions);
	}

	public function view($id = null)
	{
		if (! $this->ContentsQuestion->exists($id))
		{
			throw new NotFoundException(__('Invalid contents question'));
		}
		$options = array(
				'conditions' => array(
						'ContentsQuestion.' . $this->ContentsQuestion->primaryKey => $id
				)
		);
		$this->set('contentsQuestion', $this->ContentsQuestion->find('first', $options));
	}

	public function admin_add()
	{
		$this->admin_edit();
		$this->render('admin_edit');
	}

	public function admin_edit($id = null)
	{
		if ($this->action == 'edit' && ! $this->Post->exists($id))
		{
			throw new NotFoundException(__('Invalid contents question'));
		}
		if ($this->request->is(array(
				'post',
				'put'
		)))
		{
			if ($id == null)
			{
				$this->request->data['ContentsQuestion']['user_id'] = $this->Session->read('Auth.User.id');
				$this->request->data['ContentsQuestion']['content_id'] = $this->Session->read('Iroha.content_id');
			}
			
			if (! $this->ContentsQuestion->validates())
				return;
			
			if ($this->ContentsQuestion->save($this->request->data))
			{
				$this->Flash->success(__('問題が保存されました'));
				return $this->redirect(
						array(
								'controller' => 'contents_questions',
								'action' => 'index',
								$this->Session->read('Iroha.content_id')
						));
			}
			else
			{
				$this->Flash->error(__('The contents question could not be saved. Please, try again.'));
			}
		}
		else
		{
			$options = array(
					'conditions' => array(
							'ContentsQuestion.' . $this->ContentsQuestion->primaryKey => $id
					)
			);
			$this->request->data = $this->ContentsQuestion->find('first', $options);
		}
		
		$this->set('course_name',   $this->Session->read('Iroha.course_name'));
		$this->set('content_name',  $this->Session->read('Iroha.content_name'));
	}

	/**
	 * delete method
	 *
	 * @throws NotFoundException
	 * @param string $id        	
	 * @return void
	 */
	public function admin_delete($id = null)
	{
		$this->ContentsQuestion->id = $id;
		if (! $this->ContentsQuestion->exists())
		{
			throw new NotFoundException(__('Invalid contents question'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ContentsQuestion->delete())
		{
			$this->Flash->success(__('問題が削除されました'));
			return $this->redirect(
					array(
							'controller' => 'contents_questions',
							'action' => 'index',
							$this->Session->read('Iroha.content_id')
					));
		}
		else
		{
			$this->Flash->error(__('The contents question could not be deleted. Please, try again.'));
		}
		return $this->redirect(array(
				'action' => 'index'
		));
	}

	public function admin_order()
	{
		$this->autoRender = FALSE;
		if($this->request->is('ajax'))
		{
			$this->ContentsQuestion->setOrder($this->data['id_list']);
			return "OK";
		}
	}
}
