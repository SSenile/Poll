<?php
if(!$polls->spamCheck()) {
    $error = "You can only do this once every 15 seconds";
}
global $result;
$hasVoted = $polls->hasVoted($result);
$choice = isset($_POST["choice"]) ? addslashes($_POST["choice"]) : "";
if(empty($choice)) {
    $error = "Bad Request";
}

header("Content-type: application/json");
if(isset($error)) {
    die(json_encode(array('status' => 500, 'error' => $error)));
} else if($hasVoted) {
    die(json_encode(array('status' => 500, 'error' => 'You have already voted on this poll!')));
} else {
    $id = (int)$result["id"];
    $ip = (isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR']);
    $client = addslashes($_SERVER["HTTP_USER_AGENT"]);
    $stmt = $sql->prepare("INSERT INTO `" . $config["tables"]["poll_votes"] . "` (poll, choice, ip, client, time) VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(now()));");
    $stmt->bind_param('isss', $id, $choice, $ip, $client);
    $stmt->execute();
    $stmt->close();
    $sql->close();
    
    die(json_encode(array('status' => 200, 'message' => 'Vote added')));
}