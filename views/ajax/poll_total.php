<?php
global $result;

$id = (int)$result["id"];
$stmt = $sql->prepare("SELECT COUNT(*) FROM `" . $config["tables"]["poll_votes"] . "` WHERE poll=? AND removed=0 LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
while($assoc_array = fetchAssocStatement($stmt)) {
    $result2 = $assoc_array;
    break;
}

$stmt->close();
$count = is_null($result2) ? 0 : $result2["COUNT(*)"];
die("Total: " . number_format($count));