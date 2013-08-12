<?php
namespace authenticator;

function validate_password($submitted, $hashed) {
    return \pbkdf2\validate_password($submitted, $hashed);
}

//Hashes and salts password for better safety
function hash_password($input) {
    return \pbkdf2\create_hash($input);
}

//for use in random salt generation.
function random_string($length) {
    $characters = "0123456789abcdef";
    $size = strlen($characters)-1;
    $string = "";     
    for ($p = 0; $p < $length ; $p++) {
        $string .= $characters[mt_rand(0, $size)];
    }
    return $string;
}

function try_array($array, $key, $default = null) {
    if(is_array($array)) {
        return isset($array[$key]) ? $array[$key] : $default;
    }
    else {
        return $default;
    }
}

function sanitize($var) {
    return stripslashes(htmlentities(strip_tags($var)));
}

function sanitize_array(array $array) {
    $cleanArray = array();
    foreach($array as $key => $value) {
        if(is_array($value)) {
            $cleanArray[$key] = sanitize_array($value);
        }
        else {
            $cleanArray[$key] = sanitize($value);
        }
    }
    return $cleanArray;
}


// ------------------------- Time Wrapper (for unit testing) -------------------------------------
class Clock {
    function get_time() {
        return time();
    }
}

abstract class Data_Access {
	protected $DB;

	function __construct($Database) {
		$this->DB = $Database;
	}

	protected function select($table, array $where, array $selected = null) {
        if($selected) {
            $selectStatement = implode(', ', $selected);
        }
        else {
            $selectStatement = "*";
        }

        $whereStatement = array();
        foreach(array_keys($where) as $field) {
            $whereStatement[] = "$field = ?";
        }
        $whereStatement = implode(" ", $whereStatement);

        $stmt = $this->DB->prepare("SELECT $selectStatement FROM $table WHERE $whereStatement");
        if($stmt->execute(array_values($where))) {
        	$stmt->setFetchMode(\PDO::FETCH_ASSOC);
            return $stmt;
        }
    }

    protected function select_first($table, array $where, array $selected = null) {
        $stmt = $this->select($table, $where, $selected);
        return $stmt ? $stmt->fetch() : null;
    }
}
?>