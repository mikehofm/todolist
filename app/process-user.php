<?php

require '../includes/start.php';

$action = getArg('action');

switch ($action) {
	case 'create':
		$name = getArg('name');
		$password = getArg('password');
		
		$result = User::create($name, $password);
		if ($result !== true)
			fail($result);
			
		die('User created.');
	
	case 'login':
		$name = getArg('name');
		$password = getArg('password');
		
		$result = User::login($name, $password);
		if ($result !== true)
			fail($result);

		die('Logged in.');
		
	case 'logout':
		User::logout();
		die('Logged out');
			
	default:
		break;
}