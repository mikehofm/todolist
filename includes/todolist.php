<?php 
$tasks = User::current()->withCondition('deleted = 0 ORDER BY `order` DESC, id DESC')->xownTaskList;
$colors = R::getCol('SELECT LOWER(name) FROM color ORDER BY id');
?>

<div class="container">
	<div class="row">
		<div class="col-md-offset-3 col-md-6">
			<h3>Things I should do</h3>
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

<?= '<script type="handlebars-template" id="task-template">' ?>
	<li class="list-group-item {{lowercase color.name}} clearfix" data-id="{{id}}">
		<span class="glyphicon glyphicon-menu-hamburger grip" aria-hidden="true"></span>
		<span class="color-block collapsed" data-toggle="collapse" data-target="#color-panel-{{id}}"></span>
		<span class="task-text">{{{text}}}</span>
		<button type="button" class="close" aria-label="Delete"><span aria-hidden="true">×</span></button>
		<div class="collapse color-panel" id="color-panel-{{id}}">
			<h4>Pick a colour</h4>
			<div>
				{{#each colors}}
				<div class="color-cell" data-color="{{lowercase this}}"><span class="color-block {{lowercase this}}"></span></div>
				{{/each}}
			</div>
			<div class="color-description"></div>
			<a href="#" class="close-panel" data-target="#color-panel-{{id}}" data-toggle="collapse">close</a>
		</div>
	</li>	
<?= '</script>' ?>

<?= '<script type="handlebars-template" id="color-description-template">' ?>
	<strong>{{capitalize name}}</strong>
	<p>{{description}}</p>
	{{#if example}}
	<p>Example</p>
	<blockquote><span class="color-block"></span> {{example}}</blockquote>
	{{/if}}
<?= '</script>' ?>

<?= '<script type="handlebars-template" id="task-deleted-template">' ?>
	<div class="alert alert-warning alert-dimissible task-deleted">Task deleted.&nbsp; 
		<a href="#" class="alert-link"><span class="glyphicon glyphicon-share-alt undo"></span> Undo</a>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	</div>
<?= '</script>' ?>
	
<script>
	$(function() {
		var tasks = <?= json_encode(R::exportAll($tasks, true, ['color'])) ?>;
		var colors = <?= json_encode($colors) ?>;
		
		$('.container').on('click', 'a[href=#]', function(e) { 
			e.preventDefault(); 
		});
		
		$('.todolist').append(tasks.map(function(x) { x.colors = colors; return $(renderTemplate('task-template', x)) }));
		$('.todolist').sortable({ animation: 200, handle: '.grip', onUpdate: function(e) {
			var id = +$(e.item).data('id');
			var index = tasks.length - e.newIndex;
			
			$.post('process-task.php?action=move', { id: id, newIndex: index });
		} });
		
		$('.todolist .collapse').collapse({ toggle: false });
				
		$('.add-task-form').on('ajax:done', function(e, response) {
			response.colors = colors;
			
			var newItem = $(renderTemplate('task-template', response));
			$('.todolist .add-task-form').after(newItem);
			newItem.find('.collapse').collapse({ toggle: false });
			$(this).find('[name=text]').val('');
			var $colorDescription = $('.todolist .list-group-item[data-id=' + response.id + '] .color-description');
			updateColorDescription($colorDescription, response.color.name.toLowerCase())
		});
		
		$('.color-block[data-toggle=collapse]').click(function() {
			var listItem = $(this).closest('.list-group-item');
			var color = listItem[0].classList[1];
			updateColorDescription(listItem.find('.color-description'), color);
		});
		
		$('.todolist').on('show.bs.collapse', function(e) {
			$('.todolist .collapse').not(e.target).collapse('hide');
		});
		
		$('.todolist').on('click', '.color-panel .color-cell', function() {
			var listItem = $(this).closest('.list-group-item');
			var color = $(this).data('color');
			var id = listItem.data('id')
			
			listItem[0].className = 'list-group-item ' + color;
			listItem.find('.selected').removeClass('selected');
			$(this).find('.color-block').addClass('selected');
			updateColorDescription(listItem.find('.color-description'), color);
			
			$.post('process-task.php?action=setcolor', { id: id, color: color });
		});
		
		$('.todolist').on('click', '.list-group-item > .close', function() {
			$('.task-deleted').remove();
			$(this).closest('.container').prepend(renderTemplate('task-deleted-template'));
			var listItem = $(this).closest('.list-group-item');
			var id = listItem.data('id');
			var index = listItem.index();
			
			$('.task-deleted > a').click(function() {
				$.post('process-task.php?action=undodelete', function() {
					$($('.todolist').children()[index - 1]).after(listItem);
					$('.task-deleted').remove();
				});
			});			
			
			$.post('process-task.php?action=delete', { id: id }, function() {
				$('.task-deleted').css('top', '10px');
				listItem.remove();
			});
		});
		
		$('.todolist').on('click', '.task-text', function() {
			stopEditing($('.edit-task-text'));
			
			var text = $(this).html().replace(/\n/g, '').replace(/<br>/g, '\n'); 
			$(this).replaceWith('<textarea class="edit-task-text">' + text + '</textarea>')
			$('.edit-task-text').focus();
		});
		
		$('.todolist').on('keydown', '.edit-task-text', function(e) {
			if (e.which == 13 && !e.shiftKey)
				$(this).blur();
		});
		
		$('.todolist').on('blur', '.edit-task-text', function() {
			var text = $(this).val();
			var id = $(this).closest('.list-group-item').data('id');
			
			$.post('process-task.php?action=settext', { id: id, text: text });
			stopEditing($(this));
		});
	});
	
	function stopEditing($elements) {
		$elements.each(function() {
			var text = $(this).val().replace(/\n/g, '<br>');
			$(this).replaceWith('<span class="task-text">' + text + '</span>');	
		});
	}
	
	function updateColorDescription(container, color) {
		var data = colorMessages[color] || {};
		data.name = color;
		container.html(renderTemplate('color-description-template', data));
	}
	
	var colorMessages = {
		red: {
			description: 'Red is the colour of love and blood. Use it for tasks like romantic nights out or going to war.'
		},
		blue: {
			description: 'Blue is reserved exclusively for tasks related to the cancellation of Firefly and the Futurama episode with Fry’s dog.'
		},		
		green: {
			description: 'Green is the colour of plants and envy. Use it when you’re jealous of a tree.',
			example: 'Buy chainsaw and show that stupid pine tree who’s boss.'
		},		
		yellow: {
			description: 'Yellow is the colour of the sun when viewed through Earth’s atmosphere. Use it for all your space exploration tasks.',
			example: 'Rescue Jebediah Kerman from the moon.'
		},			
		pink: {
			description: 'Women and young girls have an inexplicable attraction to this colour. Use it wisely.'
		},		
		white: {
			description: 'White is the digital equivalent of invisible ink. Use it for tasks you’d rather not see.',
			example: 'Host overbearing in-laws.'
		},		
		gold: {
			description: 'Gold is the traditional colour of gold, before the emergence of the new-fangled white gold and rose gold. Use it for tasks involving wealth.',
			example: 'Get rich.'
		},		
		silver: {
			description: 'Silver is just glorified grey. And unless your screen can display specular highlights, you’re looking at actual grey. Use it for tasks that are grey but more expensive.',
			example: 'Pay an American law school to become a corporate lawyer.'
		},		
		grey: {
			description: 'Grey is what other colours become when you suck the life out of them. Use it for soul-sucking tasks.',
			example: 'Become corporate lawyer.'
		},
		none: {
			description: 'Leaves the task uncoloured. Use this if you find choosing colours is hard.',
			example: 'Create colour-choosing committee.'
		}
	};
</script>