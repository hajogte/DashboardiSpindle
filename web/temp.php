<?php
include("../assets/common_db.php");
include("../assets/common_db_query.php");
/* Include the `../src/fusioncharts.php` file that contains functions to embed the charts.*/
include("../assets/fusioncharts.php");

// Display current last density
$sql = "SELECT Temperature FROM Data ORDER BY Timestamp DESC LIMIT 1";
$result = $conn->query($sql);
$row=mysqli_fetch_assoc($result);
$tempactu = $row['Temperature'];
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $tempactu = $row['Temperature'];
    }
}

// Show battery status as a chart
// GET Parameters:
// name = iSpindle name

// Check GET parameters (for now: Spindle name and Timeframe to display)
if(!isset($_GET['hours'])) $_GET['hours'] = 0; else $_GET['hours'] = $_GET['hours'];
//if(!isset($_GET['name'])) $_GET['name'] = 'iSpindel000'; else $_GET['name'] = $_GET['name'];
if(!isset($_GET['name'])) $_GET['name'] = 'iSpindel000'; else $_GET['name'] = $_COOKIE['ispindel_name'];
if(!isset($_GET['reset'])) $_GET['reset'] = defaultReset; else $_GET['reset'] = $_GET['reset'];
if(!isset($_GET['days'])) $_GET['days'] = 0; else $_GET['days'] = $_GET['days'];
if(!isset($_GET['weeks'])) $_GET['weeks'] = 0; else $_GET['weeks'] = $_GET['weeks'];
//if(!isset($_GET['moving'])) $_GET['moving'] = 120; else $_GET['moving'] = $_GET['moving'];

// Calculate Timeframe in Hours
$timeFrame = $_GET['hours'] + ($_GET['days'] * 24) + ($_GET['weeks'] * 168);
if($timeFrame == 0) $timeFrame = defaultTimePeriod;
$tftemp = $timeFrame;
$tfweeks = floor($tftemp / 168);
$tftemp -= $tfweeks * 168;
$tfdays = floor($tftemp / 24);
$tftemp -= $tfdays * 24;
$tfhours = $tftemp;

$time = date("Y-m-d H:i:s");

$sql = "SELECT Timestamp FROM Data ORDER BY Timestamp ASC LIMIT 1";
$result = $conn->query($sql);
$row=mysqli_fetch_assoc($result);
$timeFrame2 = $row['Timestamp'];
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $timeFrame2 = $row['Timestamp'];
    }
}
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diffInSeconds = $now->getTimestamp() - $ago->getTimestamp();
    $diffInHours = $diffInSeconds/3600;
    return (int) $diffInHours;
}
//echo time_elapsed_string($timeFrame2, true);
$timeFrame3 = time_elapsed_string($timeFrame2, true);

$imghops = '<div class="hoppng" id="hoppng"><img src="../assets/img/icons-hops-beer.png" alt="" style="max-width:3%; float: right;"></div>';
list($angle, $temperature, $dens) = getChartValuesPlato($conn, $_COOKIE['ispindel_name'], $timeFrame3, $_GET['reset']);
//list($angle, $temperature, $dens) = getChartValues_ma($conn, $_COOKIE['ispindel_name'], $timeFrame, $_GET['moving'], $_GET['reset']);
//list($isCalib, $dens, $temperature, $angle) = getChartValuesPlato4($conn, $_GET['name'], $timeFrame, $_GET['reset']);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <!--AUTO REFRESH <meta http-equiv="refresh" content="60"> AUTO REFRESH-->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    BEER
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link href="../assets/css/black-dashboard.css?v=1.0.0" rel="stylesheet" />
  <!--   Core JS Files   -->
  <script src="../assets/js/core/jquery.min.js"></script>
  <!-- <script src="../assets/jquery-3.1.1.min.js"></script>-->
  <script type="text/javascript" src="//cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js"></script>
  <script type="text/javascript" src="//cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.fusion.js"></script>
  <script src="../assets/js/moment.min.js"></script>
  <script src="../assets/js/moment-timezone-with-data.js"></script>
  <script src="../assets/js/highcharts.js"></script>

<script type="text/javascript">
// chart courbe temperature et angle
$(function ()
{
  var chart;

  $(document).ready(function()
  {
    Highcharts.setOptions({
      global: {
      	timezone: 'Europe/Paris'
      }
    });

    chart = new Highcharts.Chart(
    {
      chart:
      {
        renderTo: 'containerChartTop',
        type: 'spline',
        zoomType: 'x',
        scrollablePlotArea: {
           minWidth: 700
       }
      },
      title:
      {
        text: '<div class="hoppng" id="hoppng">Température actuelle de fermentation <?php echo number_format((float)$tempactu, 2, '.', '');?> °C</div>',
        useHTML : 'true'
      },
      subtitle:
      { text: '<div class="hoppng" id="hoppng">Fermentation voulue pour <?php echo $_COOKIE[$nom1] , '  de  ', $_COOKIE[$tempebasse1] , '°C  à  ', $_COOKIE[$tempehaute1] , '°C';?></div>'
      },
      xAxis:
      {
    type: 'datetime',
    startOnTick: false,
    endOnTick: false,
    showFirstLabel: true,
    showLastLabel: true,
    tickPixelInterval: 60,
        dateTimeLabelFormats: {
              millisecond: '%H:%M:%S.%L',
              second: '%H:%M:%S',
              minute: '%H:%M',
              hour: '%H:%M',
              day: '%e. %b',
              week: '%e. %b',
              month: '%b \'%y',
              year: '%Y'
                  },
        labels: {
          formatter:function(){
              return Highcharts.dateFormat('%e %b \à\ %H:%M',this.value);
              }
                },
	gridLineWidth: 1,
	title:
        {
          text: ''//heure de la journée
        }
      },
      yAxis: [
      {
	startOnTick: false,
	endOnTick: false,
  showFirstLabel: false,
  showLastLabel: true,
  min: -5,
	max: 35,
  	 gridLineWidth: 1,
	title:
        {
          text: ' °C'
        },
	labels:
        {
          align: 'right',
          reserveSpace: true,
          x: 3,
          y: 16,
          formatter: function()
          {
            return this.value +'°'
          }
        },
      },
      ],
        tooltip:
            {
              valueDecimals: 2,
      	crosshairs: [false, false],
              formatter: function()
              {
      	   if(this.series.name == 'la température') {
                 	return 'Le '+ Highcharts.dateFormat('%e %b', new Date(this.x)) +' à '+ Highcharts.dateFormat('%H:%M', new Date(this.x)) +' <b>'+ this.series.name +' </b> est de  '+' <b>'+ Math.round(this.y * 10) / 10 +' °C'+' </b>';
      	   }
              }
            },
        legend:
      {
        enabled: true
      },
      credits:
      {
        enabled: false
      },
      series:
      [
	  {
          name: 'la température',
	        color: '#9adfff',
          data: [<?php echo $temperature;?>],
          marker:
          {
            symbol: 'diamond',
            enabled: false,
            states:
            {
              hover:
              {
                symbol: 'diamond',
                enabled: true,
                radius: 8
              }
            }
          }
          }
      ] //series
    });
  });
});
</script>
</head>

<body class="white-content">
  <div class="wrapper">
    <div class="sidebar" data="green">
      <!--
        Tip 1: You can change the color of the sidebar using: data-color="blue | green | orange | red"
    -->
      <div class="sidebar-wrapper">
        <div class="logo">
          <a href="javascript:void(0)" class="simple-text logo-mini">

          </a>
          <a href="javascript:void(0)" class="simple-text logo-normal">
            BEER
          </a>
        </div>
        <ul class="nav">
          <li>
            <a href="./dashboard.php">
              <i class="tim-icons icon-tv-2"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li>
            <a href="./densite.php">
              <i class="tim-icons icon-atom"></i>
              <p>Densité</p>
            </a>
          </li>
          <li>
            <a href="./angle.php">
              <i class="tim-icons icon-compass-05"></i>
              <p>Angle °</p>
            </a>
          </li>
          <li class="active ">
            <a href="./temp.php">
              <i class="tim-icons icon-sound-wave"></i>
              <p>Température °C</p>
            </a>
          </li>
          <li>
            <a href="./wifi.php">
              <i class="tim-icons icon-wifi"></i>
              <p>Wifi</p>
            </a>
          </li>
          <li>
            <a href="./settings.php">
              <i class="tim-icons icon-settings"></i>
              <p>Réglages</p>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div class="main-panel">
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-absolute navbar-transparent">
        <div class="container-fluid">
          <div class="navbar-wrapper">
            <div class="navbar-toggle d-inline">
              <button type="button" class="navbar-toggler">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
              </button>
            </div>
            <a class="navbar-brand" href="javascript:void(0)">MENU</a>
          </div>
          </div>
      </nav>
      <!-- End Navbar -->
      <div class="content" style="height: 100%;">
        <div class="row" style="height: 100%;">
          <div class="col-12" style="height: 100%;">
            <div class="card card-chart" style="height: 100%;">
              <div class="card-header">
                <h3 class="card-title cardtop"><i class="text-success"></i><?php echo $imghops;?></h3>
                <div class="row" style="height: 100%;">
                </div>
              </div>
              <div class="card-body cardbodytop" style="height: 100%;">
                <div class="card card-chart" style="height: 100%;">
                <div class="chart-area" style="height: 100%;" id="containerChartTop">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
        <footer class="footer">
        <div class="container-fluid">
          <div class="copyright">
            NIKKO ©
            <script>
              document.write(new Date().getFullYear())
            </script>
            <a href="./copyright.php">Crédits</a>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <!--   Core JS Files   -->
  <!--   <script src="../assets/js/core/popper.min.js"></script>-->
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <!--  Google Maps Plugin    -->
  <!-- Place this tag in your head or just before your close body tag. -->
  <!-- Chart JS
  <script src="../assets/js/plugins/chartjs.min.js"></script>-->
  <!--  Notifications Plugin
  <script src="../assets/js/plugins/bootstrap-notify.js"></script>-->
  <!-- Control Center for Black Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/black-dashboard.min.js?v=1.0.0"></script>

  <script>
    $(document).ready(function() {
      $().ready(function() {
        $sidebar = $('.sidebar');
        $navbar = $('.navbar');
        $main_panel = $('.main-panel');

        $full_page = $('.full-page');

        $sidebar_responsive = $('body > .navbar-collapse');
        sidebar_mini_active = true;
        white_color = false;

        window_width = $(window).width();

        fixed_plugin_open = $('.sidebar .sidebar-wrapper .nav li.active a p').html();



        $('.fixed-plugin a').click(function(event) {
          if ($(this).hasClass('switch-trigger')) {
            if (event.stopPropagation) {
              event.stopPropagation();
            } else if (window.event) {
              window.event.cancelBubble = true;
            }
          }
        });

        $('.fixed-plugin .background-color span').click(function() {
          $(this).siblings().removeClass('active');
          $(this).addClass('active');

          var new_color = $(this).data('color');

          if ($sidebar.length != 0) {
            $sidebar.attr('data', new_color);
          }

          if ($main_panel.length != 0) {
            $main_panel.attr('data', new_color);
          }

          if ($full_page.length != 0) {
            $full_page.attr('filter-color', new_color);
          }

          if ($sidebar_responsive.length != 0) {
            $sidebar_responsive.attr('data', new_color);
          }
        });

        $('.switch-sidebar-mini input').on("switchChange.bootstrapSwitch", function() {
          var $btn = $(this);

          if (sidebar_mini_active == true) {
            $('body').removeClass('sidebar-mini');
            sidebar_mini_active = false;
            blackDashboard.showSidebarMessage('Sidebar mini deactivated...');
          } else {
            $('body').addClass('sidebar-mini');
            sidebar_mini_active = true;
            blackDashboard.showSidebarMessage('Sidebar mini activated...');
          }

          // we simulate the window Resize so the charts will get updated in realtime.
          var simulateWindowResize = setInterval(function() {
            window.dispatchEvent(new Event('resize'));
          }, 180);

          // we stop the simulation of Window Resize after the animations are completed
          setTimeout(function() {
            clearInterval(simulateWindowResize);
          }, 1000);
        });

        $('.switch-change-color input').on("switchChange.bootstrapSwitch", function() {
          var $btn = $(this);

          if (white_color == true) {

            $('body').addClass('change-background');
            setTimeout(function() {
              $('body').removeClass('change-background');
              $('body').removeClass('white-content');
            }, 900);
            white_color = false;
          } else {

            $('body').addClass('change-background');
            setTimeout(function() {
              $('body').removeClass('change-background');
              $('body').addClass('white-content');
            }, 900);

            white_color = true;
          }


        });

        $('.light-badge').click(function() {
          $('body').addClass('white-content');
        });

        $('.dark-badge').click(function() {
          $('body').removeClass('white-content');
        });
      });
    });
  </script>
</body>

</html>
