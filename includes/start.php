<?php

require_once 'rb.php';
require_once 'functions.php';
require_once 'user.php';
require_once 'task.php';

R::setup( 'mysql:host=localhost;dbname=todolist', 'root', '' );
session_start();