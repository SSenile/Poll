<?php
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $question = isset($_POST["question"]) ? htmlspecialchars(addslashes(utf8_encode($_POST["question"]))) : "";
    if(empty($question)) {
        $error = "You need to provide a question!";
    }

    $q = $config["max_poll_options"];
    $questions = array();
    for ($i=1; $i < $q; $i++) {
        if(isset($_POST["answer_" . $i])) {
            $item = trim($_POST["answer_" . $i]);
            $item = strlen($item) > 35 ? substr($item, 0, 35) : $item;
            if(!empty($item) && !in_array($item, $questions)) {
                $questions[] = addslashes(utf8_encode(preg_replace('/[^(\x20-\x7F)]*/','',$item)));
            }
        }
    }

    if(count($questions) < 2) {
        $error = "You need to provide at least two answers!";
    } else if(!$polls->spamCheck(true)) {
        $error = "You can only do this once every 15 seconds";
    }

    if(isset($error)) {
        header("Content-type: application/json");
        die(json_encode(array("status" => 500, "error" => $error, "reality" => json_encode($questions))));
    } else {
        $perIP = isset($_POST["one_vote"]) ? true : false;// one vote per ip

        $nextPollId = $sql->query('SELECT id FROM ' . $config["tables"]["poll"] . ' ORDER BY id DESC LIMIT 1');
        $nextAssoc = $nextPollId->num_rows > 0 ? mysqli_fetch_assoc($nextPollId) : 0;
        $nextPollId = is_integer($nextAssoc) || $nextAssoc <= 0 ? 1 : (rand(3, 66) + $nextAssoc["id"]);
        $id = (int)$nextPollId;
        $json = json_encode($questions);
        $ip = (isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR']);

        $stmt = $sql->prepare("INSERT INTO `" . $config["tables"]["poll"] . "` (id, question, choices, ip, created) VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(now()));");
        $stmt->bind_param('isss', $id, $question, $json, $ip);
        $stmt->execute();
        $stmt->close();
        $sql->close();

        header("Content-type: application/json");
        die(json_encode(array("status" => 200, "id" => (int)$id)));
    }
}

$page->styles = array('//cdnjs.cloudflare.com/ajax/libs/sweetalert/0.5.0/sweet-alert.css');
$page->header();
$page->nav(); ?>
<div class="container" id="site-container">
    <div class="page-header">
        <h2><i class="fa fa-bars"></i> Make a poll</h2>
    </div>

    <div class="alert alert-danger" id="alert">
        <strong id="type">Error:</strong>
        <span id="message">Javascript is required</span>
    </div>

    <form method="post" class="create-form" action="<?php echo $_SERVER["REQUEST_URI"] ?>">
        <h4><strong>Options</strong></h4>
        <div class="form-group">
            <label>Poll Question</label>
            <input tabIndex="1" maxlength="90" type="text" class="form-control" name="question" placeholder="What is your poll asking?" required autofocus autocomplete="off">
        </div>
        <div class="checkbox">
            <input type="checkbox" checked name="one_vote" value="one_vote"> <span rel="tooltip" data-toggle="tooltip" data-placement="right" data-original-title="Should voting be limited to once per ip address?">One vote per IP? <span style="color:#0D8FB6;">(what's this?)</span></span>
        </div>
        <h4><strong>Answers</strong><span class="pull-right"><button tabindex="-1" class="btn btn-info btn-sm" name="add_answer" type="button" id="add_button"><i class="fa fa-plus"></i> Add Answer</button></span></h4>
        <hr>
        <div class="row text-center">
            <div id="answer_boxes">
                <i class="fa fa-spin fa-cog fa-4x"></i>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Create your poll &raquo;</button>
    </form>
<?php $page->footer(); ?>
</div>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/sweetalert/0.5.0/sweet-alert.min.js"></script>
<script type="text/javascript">
    var max_answers = <?php echo $config["max_poll_options"] ?>;
    $('.create-form').submit(function() {
        $.ajax({
            data: $(this).serialize(),
            type: $(this).attr("method"),
            url: $(this).attr("action"),
            success: function(response) {
                if(response.status == 500) {
                    $('#alert #message').html(response.error);
                    $('#alert').slideDown();
                    setTimeout(function() {
                        $('#alert').slideUp();
                    }, 7500);
                    console.log(response);
                } else if(response.status == 200) {
                    $('#alert').removeClass("alert-danger");
                    $('#alert').addClass("alert-success");
                    $('button[type="submit"]').fadeOut();
                    $('#alert #type').html("Success!");
                    $('#alert #message').html("Created your poll, redirecting..");
                    $('#alert').slideDown();
                    setTimeout(function() {
                        if(window.location.pathname.endsWith('/')) {
                            window.location.href = window.location.pathname + response.id;
                        } else {
                            window.location.href = window.location.pathname + "/" + response.id;
                        }                        
                    }, 800);
                } else {
                    $('#alert #message').html("Something has gone wrong, please refresh and try again. <br> If the problem persists contact a site administrator!");
                    $('#alert').slideDown();
                    $('form').fadeOut("slow");
                    console.log(response);
                }
            }
        });
        return false;
    });

    String.prototype.endsWith = function(suffix) {
        return this.indexOf(suffix, this.length - suffix.length) !== -1;
    };

    $(document).ready(function() {
        $("#alert").hide();
        var box = $('#answer_boxes');
        box.html("");

        for(boxId = 1; boxId <= 4; boxId++) {
            box.append(renderBox(boxId));
        }

        $('button[name="add_answer"]').click(function() {
            addButton();
        })
    });

    function addButton() {
        var bcount = $('#answer_boxes > div').length;
        if(bcount + 1 <= max_answers) {
            if(bcount + 1 >= max_answers) {
                $('button[name="add_answer"]').attr('disabled', 'disabled');
                setTimeout(function() {
                    swal('Notice', "You have added the maximum " + max_answers + " answers", 'warning');
                }, 600);
            }
            $('#answer_boxes').append(renderBox($('#answer_boxes > div').length + 1)).children(':last').hide().fadeIn(800);
        } else {
            swal('Error', "You can't add more than " + max_answers + " answers!", 'error');
        }
    }

    function renderBox(boxId) {
        return '<div class="col-md-3"><div class="form-group"><label>Answer #' + boxId + '</label><input maxlength="35" tabindex="' + (boxId + 1) + '" class="form-control" name="answer_' + boxId + '" placeholder="Enter a possible answer #' + boxId + '"' + (boxId <= 2 ? " required" : "") + '></div></div>';
    }
</script>
</body>
</html>