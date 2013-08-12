<?php
namespace authenticator;
require_once 'pbkdf2.php';
require_once 'shared.php';


class Registration {
	private $DataAccess, $Validator;
	function __construct($Database, $Validator = null, $DataAccess = null) {
		if($DataAccess) {
			$this->DataAccess = $DataAccess;
		}
		else {
			$this->DataAccess = new Registration_Data_Access($Database);
		}

		if($Validator) {
			$this->Validator = $Validator;
		}
		else {
			$this->Validator = new Registration_Validator($this->DataAccess);
		}
	}

	function register(array $submitData) {
		$submitData = sanitize_array($submitData);
		$status = $this->Validator->validate($submitData);
		if($status['success'] === true) {
			$this->DataAccess->insert_user($submitData);
		}
		return $status;
	}
}


class Registration_Validator {

	const MIN_USERNAME_LENGTH = 3;
	const MAX_USERNAME_LENGTH = 32;
	const MIN_PASSWORD_LENGTH = 7;
	const MAX_PASSWORD_LENGTH = 32;

	private $DataAccess;
	function __construct($DataAccess) {
		$this->DataAccess = $DataAccess;
	}

	function validate(array $userData) {
		$status = array();

		if(!$this->DataAccess->is_username_unique($userData['username'])) {
			$status['isUsernameUnique'] = false;
		}

		if(!$this->is_username_valid($userData['username'])) {
			$status['isUsernameValid'] = false;
		}

		if(!$this->is_password_valid($userData['password'])) {
			$status['isPasswordValid'] = false;
		}

		if(!$this->is_access_level_valid($userData['accessLevel'])) {
			$status['isAccessLevelValid'] = false;
		}

		$status['success'] = count($status) === 0 ? true : false;

		return $status;
	}

	private function is_string_in_range($string, $min, $max) {
		return strlen($string) >= $min && strlen($string) <= $max;
	}

	private function is_username_valid($username) {
		return $this->is_string_in_range(
			$username,
			self::MIN_USERNAME_LENGTH,
			self::MAX_USERNAME_LENGTH
		);
 	}

	private function is_password_valid($password) {
		return $this->is_string_in_range(
			$password,
			self::MIN_PASSWORD_LENGTH,
			self::MAX_PASSWORD_LENGTH
		);
	}

	private function is_access_level_valid($accessLevel) {
		return is_integer($accessLevel) || ctype_digit($accessLevel);
	}
}


class Registration_Data_Access extends Data_Access {
	function insert_user(array $data) {
		$stmt = $this->DB->prepare(
			"INSERT INTO User (username, password, access_level, failed_attempts) VALUES (?, ?, ?, 0)"
		);
        $stmt->execute(array(
        	$data['username'],
        	hash_password($data['password']),
        	$data['accessLevel']
        ));
        return $this->DB->lastInsertId();
	}

	function is_username_unique($username) {
		if(! $this->select_first("User", array("username" => $username))) {
			return true;
		}
		else return false;
	}
}
?>