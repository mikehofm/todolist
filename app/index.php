<?php
require_once '../includes/start.php';
require_once '../includes/header.php';

//die(print_r($_SESSION));
if (empty($_SESSION['user'])) {
	require_once '../includes/marketing.php';
} else {
	require_once '../includes/todolist.php';
}
      
require_once '../includes/footer.php';