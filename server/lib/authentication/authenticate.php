<?php
namespace authenticator;
require_once 'pbkdf2.php';
require_once 'shared.php';

// --------------------------------------- Authenticator --------------------------------------------

//Authenticator Interface
//    function authenticate($requiredAccessLevel);
//    function login($username, $password);
class Authenticator {
    const SALT_LENGTH = 25;
    const DEFAULT_MAX_SESSION_DURATION = 15;
    const DEFAULT_MAX_FAILED_ATTEMPTS = 50;

    private $Model,
            $server,
            $Session,
            $Clock,
            $MAX_SESSION_DURATION,
            $MAX_FAILED_ATTEMPTS;
    
    function __construct(array $config = array()) {
        if(isset($config['query'])) {
            $this->Model = $config['query'];
        }
        else {
            $this->Model = new Authenticate_Data_Access($config['database']);
        }

        $this->server = sanitize_array(try_array($config, 'server', $_SERVER));
        
        if(isset($config['session'])) {
            $this->Session = $config['session'];
        }
        else {
            $this->Session = new Session();
        }

        if(isset($config['clock'])) {
            $this->Clock = $config['clock'];
        }
        else {
            $this->Clock = new Clock();
        }

        $this->MAX_SESSION_DURATION = try_array(
            $config,
            'MAX_SESSION_DURATION',
            self::DEFAULT_MAX_SESSION_DURATION
        );

        $this->MAX_FAILED_ATTEMPTS = try_array(
            $config,
            'MAX_FAILED_ATTEMPTS',
            self::DEFAULT_MAX_FAILED_ATTEMPTS
        );
    }

//------------------------------------- Authenticate ---------------------------------------

    function authenticate($requiredAccessLevel) {
        $isAuthentic = false;
     
        if(
            $this->Session->get('IS_LOGGED_IN') === true
         && $this->is_signature_valid() === true
         && $this->is_timed_out() === false
         && $this->is_max_attempts_ip() === false
        ) {
            $userData = $this->Model->get_user_data($this->Session->get('USERNAME'));
            if(
                $userData['access_level'] >= $requiredAccessLevel
             && $userData['failed_attempts'] <= $this->MAX_FAILED_ATTEMPTS
            ) {
                $isAuthentic = true;
                $this->Session->set('LAST_ACTIVITY', $this->Clock->get_time());
            }
        }
        else {
            $isAuthentic = false;
            $this->Session->set('IS_LOGGED_IN', FALSE);
            $this->Session->destroy();
        }
        
        return $isAuthentic;
    }

    private function is_signature_valid() {

        $sessionSignature = $this->Session->get('SIGNATURE');
        $salt = substr($sessionSignature, 0, self::SALT_LENGTH);

        $originalHash = substr($sessionSignature, self::SALT_LENGTH, 40);
        $submittedHash = sha1($salt . $this->server['REMOTE_ADDR'] . $this->server['HTTP_USER_AGENT']);

        return $submittedHash === $originalHash ? true : false;
    }

    private function is_timed_out() {
        $isTimedOut = true;
        $lastActivity = $this->Session->get('LAST_ACTIVITY');
        if($lastActivity) {
            $currentTime = $this->Clock->get_time();
            if($currentTime - $lastActivity <= $this->MAX_SESSION_DURATION) {
                $isTimedOut = false;
            }
        }


        return $isTimedOut;
    }

    private function is_max_attempts_ip() {
        if($this->Model->get_login_attempts($this->server['REMOTE_ADDR']) > $this->MAX_FAILED_ATTEMPTS) {
            return true;
        }
        else return false;
    }

//--------------------------------------- Login --------------------------------------------------

    function login($submittedUsername, $submittedPassword) {
        $isAuthentic = false;

        $submittedUsername = sanitize($submittedUsername);
        $submittedPassword = sanitize($submittedPassword);

        $userData = $this->Model->get_user_data($submittedUsername);
        $isUsernameValid = $userData ? true : false;

        if(
            $isUsernameValid === true
         && validate_password($submittedPassword, try_array($userData, 'password')) === true
         && $this->is_max_attempts_ip() === false
        ) {
            $isAuthentic = true;
            $this->Model->reset_failed_attempts_user($userData['username']);
            $this->Model->reset_failed_attempts_ip($this->server['REMOTE_ADDR']);
            $this->set_session_success($userData['username']);
        }
        else {
            $isAuthentic = false;
            if($isUsernameValid === true) {
                $this->Model->increment_failed_attempts_user($userData['username'], $userData['failed_attempts']);
            }
            $this->Model->increment_failed_attempts_ip($this->server['REMOTE_ADDR']);
            $this->Session->destroy();
        }

        return $isAuthentic;
    }

    private function set_session_success($username) {
        $this->Session->regenerate();
        $this->Session->set('SIGNATURE', $this->generate_signature());
        $this->Session->set('IS_LOGGED_IN', true);
        $this->Session->set('LAST_ACTIVITY', $this->Clock->get_time());
        $this->Session->set('USERNAME', $username);
    }

    private function generate_signature() {
        $salt = random_string(self::SALT_LENGTH);
        $userHash = sha1($salt . $this->server['REMOTE_ADDR'] . $this->server['HTTP_USER_AGENT']);
        return $salt . $userHash;
    }
}



//------------------------------------------ Data Access ---------------------------------------------


class Authenticate_Data_Access extends Data_Access {

    function get_user_data($username) {
        return $this->select_first("User", array("username" => $username));
    }

    function get_login_attempts($ip) {
        $row = $this->select_first("Ip_Check", array("ip" => $ip), array("failed_attempts"));
        return try_array($row, 'failed_attempts');
    }

    function reset_failed_attempts_user($username) {
        $stmt = $this->DB->prepare("UPDATE User SET failed_attempts = 0 WHERE username = ?");
        $stmt->execute(array($username));
    }

    private function is_ip_logged($ip) {
        if($this->select_first("Ip_Check", array('ip' => $ip), array('ip'))) {
            return true;
        }
        else {
            return false;
        }
    }

    function reset_failed_attempts_ip($ip) {
        if($this->is_ip_logged($ip)) {
            $stmt = $this->DB->prepare("UPDATE Ip_Check SET failed_attempts = 0 WHERE ip = ?");
            $stmt->execute(array($ip));
        }
        else {
            $stmt = $this->DB->prepare("INSERT INTO Ip_Check (ip, failed_attempts) VALUES (?, 0)");
            $stmt->execute(array($ip));
        }
    }

    function increment_failed_attempts_user($username, $failedAttempts) {
        $stmt = $this->DB->prepare("UPDATE User SET failed_attempts = ? WHERE username = ?");
        $stmt->execute(array($failedAttempts + 1, $username));
    }

    function increment_failed_attempts_ip($ip) {
        $row = $this->select_first(
            "Ip_Check",
            array("ip" => $ip),
            array("failed_attempts")
        );

        if($row) {
            $stmt = $this->DB->prepare("UPDATE Ip_Check SET failed_attempts = ? WHERE ip = ?");
            $stmt->execute(array($row['failed_attempts'] + 1, $ip));
        }
        else {
            $stmt = $this->DB->prepare("INSERT INTO Ip_Check (ip, failed_attempts) VALUES (?, 1)");
            $stmt->execute(array($ip));
        }
    }
}



// ------------------------------------- Session Wrapper -----------------------------------

//Session interface :
//    function get($key);
//    function set($key, $value);
//    function destroy();
//    function regenerate();
class Session {

    function __construct() {
        if(session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
    
    function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    function destroy() {
        if($_SESSION) {
            $_SESSION = array();
            if(session_id() != ""  ||  isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time()-2592000, '/');
            }
            session_unset();
            session_destroy();
        }
    }

    function regenerate() {
        session_regenerate_id();
    }
}
?>