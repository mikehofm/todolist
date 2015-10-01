<?php
require_once '../includes/start.php';

if (!User::isLoggedIn())
	fail('You must be logged in to do that.');

$action = getArg('action');

switch ($action) {
	case 'add':
		$text = getArg('text');
		$task = R::dispense('task');
		$task->text = $text;
		$task->color = R::enum('color:none');
		$task->order = 0;
		try {
			$task->order = R::getCell('SELECT MAX(`order`) FROM task') + 1;
		} catch (Exception $e) { }
		
		$task->user = User::current();
		$id = R::store($task);
		
		header('Content-Type: application/json');
		returnJson($task->export());
	
	case 'move':
		$id = getArg('id');
		$newIndex = (int)getArg('newIndex');
		$allTasks = User::current()->xownTaskList;
		$count = count($allTasks);
		
		$targetTask = R::load('task', $id);
		if (!$targetTask)
			fail('Invalid task id.');
		if ($newIndex < 0 || $newIndex > $count)
			fail("New index is out of range. Specify an index between 0 and $count inclusive.");
		
		$oldIndex = $targetTask->order;
		
		R::exec('UPDATE task SET `order` = `order` + 1 WHERE `order` >= ?', [$newIndex]);
		R::exec('UPDATE task SET `order` = `order` - 1 WHERE `order` > ?', [$oldIndex]);

		$targetTask->order = $newIndex;
		R::store($targetTask);
		die('Task moved');
		
	default:
		break;
}