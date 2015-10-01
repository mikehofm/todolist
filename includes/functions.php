<?php

function getArg($name) {
	if (isset($_GET[$name]))
		return $_GET[$name];
	else if (isset($_POST[$name]))
		return $_POST[$name];
		
	return null;
}

function fail($message) {
	http_response_code(400);
	die($message);
}

function returnJson($obj) {
	die(json_encode($obj));
}