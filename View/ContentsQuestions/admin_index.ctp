<?php echo $this->element('admin_menu');?>
<?php $this->start('css-embedded'); ?>
	<style type='text/css'>
		.td-reader
		{
			width:200px;
			text-overflow:ellipsis;
			overflow:hidden;
			white-space:nowrap;
		}
		
		table
		{
			table-layout:fixed;
		}
		
		#sortable-table tbody
		{
			cursor: move;
		}
	</style>
<?php $this->end(); ?>
<?php $this->start('css-embedded'); ?>
	<script>
		$(function(){
			$('#sortable-table tbody').sortable(
			{
				helper: function(event, ui)
				{
					var children = ui.children();
					var clone = ui.clone();

					clone.children().each(function(index)
					{
						$(this).width(children.eq(index).width());
					});
					return clone;
				},
				update: function(event, ui)
				{
					var id_list = new Array();

					$('.target_id').each(function(index)
					{
						console.log(this);
						id_list[id_list.length] = $(this).val();
					});

					$.ajax({
						url: "<?php echo Router::url(array('action' => 'order')) ?>",
						type: "POST",
						data: { id_list : id_list },
						dataType: "text",
						success : function(response){
							//通信成功時の処理
							//alert(response);
						},
						error: function(){
							//通信失敗時の処理
							//alert('通信失敗');
						}
					});
				},
				cursor: "move",
				opacity: 0.5
			});
		});
	</script>
<?php $this->end(); ?>

<div class="contentsQuestions index">
	<div class="ib-breadcrumb">
	<?php 
		$this->Html->addCrumb('コース一覧', array('controller' => 'courses', 'action' => 'index'));
		$this->Html->addCrumb($course_name, array('controller' => 'contents', 'action' => 'index', $this->Session->read('Iroha.course_id')));
		
		echo $this->Html->getCrumbs(' / ');
	?>
	</div>
	<div class="ib-page-title"><?php echo __('コンテンツ問題一覧'); ?></div>
	
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?php echo Router::url(array('action' => 'add')) ?>'">+ 追加</button>
	</div>
	
	<div class="alert alert-warning">ドラッグアンドドロップで出題順が変更できます。</div>
	<table id='sortable-table' cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th><?php echo $this->Paginator->sort('title',		'タイトル'); ?></th>
		<th><?php echo $this->Paginator->sort('body',		'問題文'); ?></th>
		<th><?php echo $this->Paginator->sort('options',	'選択肢'); ?></th>
		<th><?php echo $this->Paginator->sort('correct',	'正解'); ?></th>
		<th><?php echo $this->Paginator->sort('score',		'得点'); ?></th>
		<th class="ib-col-date"><?php echo $this->Paginator->sort('created',	'作成日時'); ?></th>
		<th class="ib-col-date"><?php echo $this->Paginator->sort('modified',	'更新日時'); ?></th>
		<th class="actions text-center"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($contentsQuestions as $contentsQuestion): ?>
	<tr>
		<td class="td-reader"><?php echo h($contentsQuestion['ContentsQuestion']['title']); ?>&nbsp;</td>
		<td class="td-reader"><?php echo h(strip_tags($contentsQuestion['ContentsQuestion']['body'])); ?>&nbsp;</td>
		<td class="td-reader"><?php echo h($contentsQuestion['ContentsQuestion']['options']); ?>&nbsp;</td>
		<td><?php echo h($contentsQuestion['ContentsQuestion']['correct']); ?>&nbsp;</td>
		<td><?php echo h($contentsQuestion['ContentsQuestion']['score']); ?>&nbsp;</td>
		<td class="ib-col-date"><?php echo Utils::getYMDHN($contentsQuestion['ContentsQuestion']['created']); ?>&nbsp;</td>
		<td class="ib-col-date"><?php echo Utils::getYMDHN($contentsQuestion['ContentsQuestion']['modified']); ?>&nbsp;</td>
		<td class="actions text-center">
			<button type="button" class="btn btn-success" onclick="location.href='<?php echo Router::url(array('action' => 'edit', $contentsQuestion['ContentsQuestion']['id'])) ?>'">編集</button>
			<?php
			if($loginedUser['role']=='admin')
			{
				echo $this->Form->postLink(__('削除'), 
						array('action' => 'delete', $contentsQuestion['ContentsQuestion']['id']), 
						array('class'=>'btn btn-danger'), 
						__('[%s] を削除してもよろしいですか?', $contentsQuestion['ContentsQuestion']['title'])
				); 
				echo $this->Form->hidden('id', array('id'=>'', 'class'=>'target_id', 'value'=>$contentsQuestion['ContentsQuestion']['id']));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
</div>