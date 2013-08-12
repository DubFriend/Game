<?php
function tryArray(array $array, $key, $default = null) {
    return array_key_exists($key, $array) ? $array[$key] : $default;
}

//will return a string with $trail removed, if $trail is on the end of the string
function removeTrailing($string, $trail) {
    if(substr($string, strlen($trail) * -1) === $trail) {
        return substr($string, 0, strlen($string) - strlen($trail));
    }
    else return $string;
}

function arrayByColumn($array, $columnName) {
    $column = array();
    foreach($array as $subArray) {
        $column[] = tryArray($subArray, $columnName);
    }
    return $column;
}

//input:  string
//output: sanitized string, safer to use within the application
//        NOTE: not prepped for database insertion!
function sanitize ($var) {
    $var = strip_tags($var);
    $var = stripcslashes($var);
    return $var;
}

function sanitizeArray ($array) {
    $cleanArray = array();
    if(is_array($array)) {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $cleanArray[$key] = sanitizeArray($value);
            }
            else {
                $cleanArray[$key] = sanitize($value);
            }
        }
    }
    return $cleanArray;
}

function uniqueId ($len = 8) {
    $hex = md5("sillyputty" . uniqid("", true));
    $pack = pack('H*', $hex);
    $uid = base64_encode($pack);
    $uid = preg_replace("/[^A-Za-z0-9]/", "", $uid);
    while(strlen($uid) < $len) {
        $uid = $uid . gen_uuid(22);
    }
    return substr($uid, 0, $len);
}
?>
