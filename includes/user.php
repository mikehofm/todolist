<?php 

class Model_User extends RedBean_SimpleModel {
	public function dispense() {
		$this->bean->created = R::isoDateTime();
	}
}

class User {
	
	public static function current() {
		return isset($_SESSION['user']) ? $_SESSION['user'] : null;
	}
	
	public static function isLoggedIn() {
		return !empty($_SESSION['user']);
	}
	
	public static function login($username, $password) {
		
		$user = R::findOne('user', 'username = ?', [$username]);
		if (empty($user))
			return 'Username not found.';
			
		if (!password_verify($password, $user->password))
			return 'Invalid password.';
			
		$_SESSION['user'] = $user;
		
		return true;
	} 
	
	public static function logout() {
		session_unset();
		session_destroy();
		session_regenerate_id(true);
	}
	
	public static function create($username, $password) {
		if (empty($username))
			return 'You must choose a username.';
			
		if (!empty(R::find('user', 'username = ?', [$username])))
			return 'The username is taken.';
			
		if (strlen($password) < 5)
			return 'Password must be at least 5 characters.';
			
		$user = R::dispense('user');
		$user->username = $username;
		$user->password = password_hash($password, PASSWORD_DEFAULT); 
		
		$id = R::store($user);
		$_SESSION['user'] = R::load('user', $id);
	}
}