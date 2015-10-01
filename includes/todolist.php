<?php 
$tasks = User::current()->with('ORDER BY `order` DESC, id DESC')->xownTaskList;
?>

<div class="container">
	<div class="row">
		<div class="col-md-offset-3 col-md-6">
			<h3>Your to-do list</h3>
			<ul class="todolist list-group">
				<form class="add-task-form" action="process-task.php" method="post" data-ajax>
					<li class="input-group">
						<input type="hidden" name="action" value="add">
						<input class="form-control" type="text" name="text" placeholder="Add a new task"/>
						<span class="input-group-btn">
							<button class="btn btn-default">Add</button>
						</span>
					</li>
				</form>
			</ul>
		</div>
	</div>

<script type="handlebars-template" id="task-template">
	<li class="list-group-item {{lowercase color.name}}" data-id="{{id}}">
		<span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span>
		<input type="checkbox" />
		<span class="color-block"></span>
		{{text}}
		<span class="pull-right due-date">{{dueDate}}</span>
	</li>	
</script>

<script>
	$(function() {
		var tasks = <?= json_encode(R::exportAll($tasks, true, ['color'])) ?>;
		console.log(tasks);
		
		$('.todolist').append(tasks.map(function(x) { return $(renderTemplate('task-template', x)) }));
		$('.todolist').sortable({ animation: 200, onUpdate: function(e) {
			$.post('process-task.php?action=move', { id: +$(e.item).data('id'), newIndex: e.newIndex - 1 });
		} });
		
		$('.add-task-form').on('ajax:done', function(e, response) {
			$('.todolist .add-task-form').after(renderTemplate('task-template', response));
		});
	});
</script>