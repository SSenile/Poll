<?php
global $result;
header("Content-type: application/json");

$votes = array();
foreach(json_decode($result["choices"], true) as $choice) {
    $cleanChoice = stripslashes($choice);
    if(!empty($cleanChoice)) {
        $id = $id = (int)$result["id"];
        $_choice = $_choice = strtolower(str_replace(" ", "_", $choice));
        $stmt = $sql->prepare("SELECT COUNT(*) FROM `" . $config["tables"]["poll_votes"] . "` WHERE poll=? AND removed=0 AND choice=? LIMIT 1");
        $stmt->bind_param("is", $id, $_choice);

        $stmt->execute();
        $stmt->store_result();
        while($assoc_array = fetchAssocStatement($stmt)) {
            $result2 = $assoc_array;
            break;
        }

        $stmt->close();

        $count = is_null($result2) ? 0 : $result2["COUNT(*)"];
        $votes[((string)$cleanChoice) . " "] = $count;
    }
}

die(getJSONVotes($votes));

function getJSONVotes($raw) {
    $info = array();
    if(isset($raw) && is_array($raw)) {
        foreach ($raw as $key => $value) {
            $inf[0] = $key;
            $inf[1] = $value;
            array_push($info, $inf);
        }
    }
    return json_encode($info, JSON_NUMERIC_CHECK);
}