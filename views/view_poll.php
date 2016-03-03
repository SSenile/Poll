<?php
global $result;
$question = trim($result["question"]);
$question = xss_clean($question);

$page->styles = array('//cdnjs.cloudflare.com/ajax/libs/sweetalert/0.5.0/sweet-alert.css');
$page->header($question);
$page->nav(); ?>
<div class="container" id="site-container">
    <h2><i class="fa fa-bars"></i> <?php echo $question; ?><span class="pull-right" id="total_votes"></span></h2>
    <hr>
    <div id="poll_loader">
        <div class="row">
            <div class="col-md-5" id="left">
                <div class="loader">
                    <i class="fa fa-spin-quick fa-refresh fa-3x" id="spin"></i>
                    <p id="crunch">Please wait.. crunching data</p>
                </div>
            </div>
            <div class="col-md-7" id="right">
                <div class="loader">
                    <i class="fa fa-spin-quick fa-refresh fa-3x" id="spin"></i>
                    <p id="crunch">Please wait.. crunching data</p>
                </div>
            </div>
            </div>
        </div>
    </div>
<?php $page->footer(); ?>
</div>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/sweetalert/0.5.0/sweet-alert.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/highcharts/4.1.5/highcharts.js"></script>
<script type="text/javascript">
    var poll_id = <?php echo $result["id"]; ?>;
    var site_url =  '<?php echo $_SERVER["SERVER_NAME"]; ?>';

    $(document).ready(function() {
        _load("results", "#left");
        _load("total", "#total_votes");
        setInterval(function() {
            _load("results", "#left");
            _load("total", "#total_votes");
        }, 6000);

        var options = {
            chart: {
                renderTo: 'right',
                type: 'pie',
                // backgroundColor: $('body').css('background-color'),
                backgroundColor: 'transparent',
                events : {
                    load : function () {
                        var series = this.series[0];
                        setInterval(function () {
                            $.getJSON(window.location.pathname + "/pie", function(json) {
                                series.setData(json);
                            });
                        }, 1500);
                    }
                },
            },
            credits: {
                href: "http://" + site_url,
                text: site_url
            },
            title: {
                style: {
                    color: 'grey'
                },
                text: 'Poll Results (#' + poll_id + ')'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: false,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Votes',
                data: []
            }]
        }
        $.getJSON(window.location.pathname + "/pie", function(json) {
            options.series[0].data = json;
            chart = new Highcharts.Chart(options);
        });
    });

function _load(file, element) {
    $.ajax({
        url: window.location.pathname + "/" + file,
        type: 'get',
        async: true,
        success: function(response) {
            $('[rel=tooltip]').tooltip();
            $(element).html(response);

            if(file == 'results') {
                $('.vote-button').on('click', function() {
                    // var _button = $(this);
                    $.ajax({
                        url: window.location.pathname + "/vote",
                        type: "post",
                        data: {
                            choice: $(this).attr('data-vote')
                        },
                        success: function(resp) {
                            if(resp.status == 200) {
                                if($('.vote-button').hasClass('uno')) {
                                    $('.vote-button').attr('disabled', 'disabled');
                                }
                            } else if(resp.status == 500) {
                                swal('Error', resp.error, 'error');
                            } else {
                                swal('Error', 'An internal error occured while trying to vote, sorry try again later.', 'error');
                            }
                        },
                        error: function(e1, e2, e3) {
                            swal('Error', 'An internal error occured while trying to vote, sorry try again later.', 'error');
                            console.log('SEVERE >> ' + e3);
                        }
                    });
                });
            }
        }
    });
}
</script>
</body>
</html>