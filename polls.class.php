<?php

class polls {

    public function hasVoted($poll) {
        if($poll['perIP'] != 1) {
            return false;
        }

        $id = (int)$poll["id"];
        $ip = (isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR']);
        global $sql, $config;
        $stmt = $sql->prepare("SELECT * FROM `" . $config["tables"]["poll_votes"] . "` WHERE poll=? AND removed=0 AND ip=? LIMIT 1");
        $stmt->bind_param("is", $id, $ip);
        $stmt->execute();
        $stmt->store_result();
        while($assoc_array = fetchAssocStatement($stmt)) {
            $result = $assoc_array;
            break;
        }

        $stmt->close();
        if(!isset($result) || is_null($result)) {
            return false;
        }
        return true;
    }

    public function spamCheck($creating = false) {
        if(isset($_SESSION['spam_check' . ($creating ? "_c" : "")]) && time() - $_SESSION['spam_check' . ($creating ? "_c" : "")] < 15) {
            return false;
        } else {
            $_SESSION['spam_check' . ($creating ? "_c" : "")] = time();
            return true;
        }
    }

    public function cleanArray($array) {
        $arr = array();
        foreach ($array as $o) {
            $arr[] = addslashes(utf8_encode(preg_replace('/[^(\x20-\x7F)]*/', '', $o)));
        }
        return $arr;
    }

}

$polls = new polls();
