<?php
define("POLL_APP", 1);
include_once(__DIR__ . "/config.php");

include_once(__DIR__ . "/page.php");
include_once(__DIR__ . "/polls.class.php");

$request = explode('/', substr($_SERVER["REQUEST_URI"], strlen($config["install_directory"])));
array_shift($request);
if(count($request) == 0) {
    $request[0] = "";
}
$first = preg_replace('/\\?.*/', '', $request[0]);

if($first == '') {
    showFile("/views/home.php");
}

if(count($request) >= 1) {
    $id = (int)addslashes($first);
    $stmt = $sql->prepare("SELECT * FROM `" . $config["tables"]["poll"] . "` WHERE id=? AND removed=0 LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    while($assoc_array = fetchAssocStatement($stmt)) {
        $result = $assoc_array;
        break;
    }

    $stmt->close();

    if(isset($result) && !is_null($result)) {
        if(count($request) == 1) {
            showFile("/views/view_poll.php");
        } else if(count($request) == 2) {
            $action = preg_replace('/\\?.*/', '', $request[1]);

            if($action == 'results') {
                showFile("/views/ajax/poll_results.php");
            } else if($action == 'pie') {
                showFile("/views/ajax/poll_pie.php");
            } else if($action == 'vote') {
                showFile("/views/ajax/poll_vote.php");
            } else if($action == 'total') {
                showFile("/views/ajax/poll_total.php");
            }
        }
    }
}

header("HTTP/1.0 404 Not Found");
showFile("/views/error.php");

function redirect($destination) {
    header("Location: $destination");
    die();
}

function showFile($path) {
    global $sql, $first, $request, $page, $config, $polls;
    if(file_exists(__DIR__ . $path) && is_readable(__DIR__ . $path)) {
        include_once(__DIR__ . $path);
        die();
    } else {
        header("HTTP/1.0 404 Not Found");
        include_once(__DIR__ . "/views/error.php");
        die();
    }
}

function fetchAssocStatement($stmt) {
    if($stmt->num_rows>0) {
        $result = array();
        $md = $stmt->result_metadata();
        $params = array();
        while($field = $md->fetch_field()) {
            $params[] = &$result[$field->name];
        }
        call_user_func_array(array($stmt, 'bind_result'), $params);
        if($stmt->fetch()) {
            return $result;
        }
    }

    return null;
}

function xss_clean($data) {
    // Fix &entity\n;
    $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
    $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
    $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
    // Remove any attribute starting with "on" or xmlns
    $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
    // Remove javascript: and vbscript: protocols
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
    // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
    // Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
    do {
        // Remove really unwanted tags
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    }
    while ($old_data !== $data);
    // we are done...
    return $data;
}