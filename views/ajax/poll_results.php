<?php
global $result;
$hasVoted = $polls->hasVoted($result);

foreach(json_decode($result["choices"], true) as $choice) {
    $id = (int)$result["id"];
    $_choice = strtolower(str_replace(" ", "_", $choice));
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

    $choice = stripslashes($choice);
    echo '<h4 style="margin-top:0px;">
        <button title="Click to vote" class="btn btn-info btn-sm vote-button' . ($result["perIP"] == 1 ? " uno" : "") . '" data-vote="' . strtolower(str_replace(" ", "_", $choice)) . '"' . ($hasVoted ? " disabled" : "") . '>Vote</button>
        &nbsp;' . $choice . (strlen($choice) > 30 ? "<br>" : "") . ' <small>has <span id="the_vote">' . ($count == 1 ? "1</span> vote" : number_format($count) . "</span> votes") . '</small>
    </h4>';
}
