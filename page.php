<?php

class page {

    public $styles = array();

    public function header($title = "") { global $config; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo empty($title) ? $config["app_title"] : "$title &raquo; " . $config["app_title"] ?></title>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <script type="text/javascript"> var site_url = "<?php echo $_SERVER['SERVER_NAME'] ?>";</script>
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/bootswatch/3.3.4/darkly/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.css">
    <style type="text/css">
    .loader {
        text-align: center;
        padding-bottom: 7px;
    }

    .fa-spin-quick {
        -webkit-animation: fa-spin 0.7s infinite linear;
        animation: fa-spin 0.7s infinite linear;
    }

    @-webkit-keyframes fa-spin-quick {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(359deg);
            transform: rotate(359deg);
        }
    }

    @keyframes fa-spin-quick {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(359deg);
            transform: rotate(359deg);
        }
    }
    </style>
    <?php
        if(!empty($this->styles)) {
            foreach ($this->styles as $style) {
                echo '<link rel="stylesheet" type="text/css" href="' . $style . '">' . "\n";
            }
        }
    ?>
</head>
<body>
    <?php }
    public function nav() { global $config ?>
<nav class="navbar navbar-default navbar-static-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo $config["install_directory"]; ?>"><?php echo $config["app_title"]; ?></a>
        </div>
    </div>
</nav>
<div class="container">
        <?php
            if(isset($_SESSION["_cross_page_notice"])) {
                $notice = $_SESSION["_cross_page_notice"];
                unset($_SESSION["_cross_page_notice"]);
                echo '<div class="alert alert-' . $notice["type"] . '">
                    <h5 class="text-center" style="color:inherit;margin-top:0px;margin-bottom:0px;font-size:120%">' . $notice["message"] . '</h5>
                </div>' . "\n";
            }
        ?>
</div>
    <?php }

    public function footer() { global $config; ?>
<hr>
<footer class="footer">
    <ul class="nav nav-pills">
        <li class="pull-right"><a>&copy; <?php echo $config["app_title"]; ?> <?php echo date("Y"); ?></a></li>
    </ul>
</footer>
        <?php $this->scripts();
    }

    public function scripts() { ?>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                if($("[rel=tooltip]").length) {
                    $("[rel=tooltip]").tooltip();
                }
                $('a').on('click', function() {
                    $(this).blur();
                });
            });
        </script>
    <?php }

}

$page = new page();