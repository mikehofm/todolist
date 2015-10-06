<?php
require_once '../includes/start.php';

if (!User::isLoggedIn())
	fail('You must be logged in to do that.');

$action = getArg('action');
$id = getArg('id');
$user = User::current();

switch ($action) {
	case 'add':
		$text = getArg('text');
		if ($text == '') 
			fail('No task description given.');
		
		$task = R::dispense('task');
		$task->text = $text;
		$task->color = R::enum('color:none');
		$task->order = 0;
		try {
			$task->order = R::getCell('SELECT MAX(`order`) FROM task') + 1;
		} catch (Exception $e) { }
		
		$task->user = $user;
		$id = R::store($task);
		
		header('Content-Type: application/json');
		returnJson($task->export(false, true, false, ['color']));
	
	case 'move':
		$newIndex = (int)getArg('newIndex');
		$allTasks = $user->xownTaskList;
		$count = count($allTasks);
		
		$targetTask = R::load('task', $id);
		if (!$targetTask || $targetTask->user_id !== $user->id)
			fail('Invalid task id.');
		if ($newIndex < 0 || $newIndex > $count)
			fail("New index is out of range. Specify an index between 0 and $count inclusive.");
		
		$oldIndex = $targetTask->order;
		$userID = $user->id;
		
		R::exec('UPDATE task SET `order` = `order` - 1 WHERE user_id = ? AND `order` >= ?', [$userID, $oldIndex]);
		R::exec('UPDATE task SET `order` = `order` + 1 WHERE user_id = ? AND `order` >= ?', [$userID, $newIndex]);

		$targetTask->order = $newIndex;
		R::store($targetTask);
		die('Task moved');
		
	case 'setcolor':
		$task = R::load('task', $id);
		if (!$task || $task->user_id !== $user->id)
			fail('Invalid task id.');
			
		$colorStr = strtoupper(getArg('color'));
		$color = R::findOne('color', 'name = ?', [$colorStr]);
		if (!$color) 
			fail('Stop making up colors.');
		
		$task->color = $color;
		R::store($task);
		die('Color changed');
		
	case 'settext':
		$task = R::load('task', $id);
		if (!$task || $task->user_id !== $user->id)
			fail('Invalid task id.');
			
		$text = getArg('text');
		if ($text == '') 
			fail('No task description given.');
		
		$text = nl2br($text);
		$task->text = $text;
		R::store($task);
		die('Text changed');
	
	case 'delete':
		// You can only undo the most recently deleted task. Other tasks are permanently deleted.
		try {
			R::exec('DELETE FROM task WHERE user_id = ? AND deleted = 1', [$user->id]);
		} catch (Exception $e) { }
		
		$task = R::load('task', $id);
		if (!$task || $task->user_id !== $user->id)
			fail('Invalid task id.');
			
		$task->deleted = 1;
		R::store($task);
		die('Task deleted');
		
	case 'undodelete':
		$task = R::findOne('task', 'user_id = ? AND deleted = 1', [$user->id]);
		if (!$task)
			fail('No deletion to undo.');
			
		$task->deleted = 0;
		R::store($task);
		die('Task restored');
		
	default:
		break;
}