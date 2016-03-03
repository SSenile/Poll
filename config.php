<?php
if(!defined("POLL_APP")) {
    die('This file cannot be directly accessed.');
}

$config["database"]["hostname"] = "localhost";
$config["database"]["username"] = "paste";
$config["database"]["password"] = "password";
$config["database"]["port"] = 3306;
$config["database"]["name"] = "database";

$config["tables"]["poll"] = "polls";
$config["tables"]["poll_votes"] = "poll_votes";

$config["timezone"] = "Europe/Amsterdam";
$config["app_title"] = "Poll";
$config["max_poll_options"] = 12;
// Set this to where this is installed? Eg. /poll
$config["install_directory"] = "/poll";

// ------------------------------------
// --- Please don't edit below here ---
// ------------------------------------

session_start();
date_default_timezone_set($config["timezone"]);

$database = $config["database"];
$sql = new mysqli($database["hostname"], $database["username"], $database["password"], $database["name"], $database["port"]);
$sql->query("CREATE TABLE IF NOT EXISTS `" . $config["tables"]["poll"] . "` ( `id` int(255) NOT NULL AUTO_INCREMENT, `question` text NOT NULL, `choices` longtext NOT NULL, `ip` text NOT NULL, `created` bigint(20) NOT NULL, `removed` int(5) NOT NULL DEFAULT '0', `open` int(5) NOT NULL DEFAULT '1', `perIP` int(5) NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");
$sql->query("CREATE TABLE IF NOT EXISTS `" . $config["tables"]["poll_votes"] . "` ( `id` int(255) NOT NULL AUTO_INCREMENT, `poll` int(255) NOT NULL, `choice` text NOT NULL, `ip` text NOT NULL, `client` text NOT NULL, `time` bigint(20) NOT NULL, `removed` int(5) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

if(endsWith($config["install_directory"], "/")) {
    $config["install_directory"] = rtrim($config["install_directory"], "/");
}

function endsWith($haystack, $needle) {
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}
