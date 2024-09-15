<?php
error_reporting(E_ERROR | E_PARSE | E_WARNING | E_NOTICE | E_COMPILE_ERROR);
include "conn_cloud.php";
include "googlemap.php";
?>
<html>
 <head>
 <meta name="viewport" content="width=device-width, initialscale=1, minimum-scale=1" />
 <title>Energeticka kapacita</title>
 <meta charset="utf-8">
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
 <script src="googlemap.js<?php echo "?v=" . rand(); ?>"></script>
 <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCeMxgYGIQCKdP3zFrg5vIdEsoFHj44co4&callback=initMap">
 </script>
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
 <style>
<?php include 'main.css'; ?>
 </style>
 </head>
 <body>
<div class="header">
 <h1>Energetická kapacita OZE regiónov SR</h1>
 </div>
 <form class="form" action="#" method="post">
 <label for="sel1" >Mesto:</label>
 <select name="mesto" class="siblings" id="mesto" >
 <option disabled selected value> -- Vyber -- </option>
 <?php
 $sql = "SELECT nazov_stanice FROM openweather_stanice";
 $result = $conn->query($sql);
 while ($row = mysqli_fetch_array($result)) {
 ?> <option <?php if ($_POST['mesto'] ==
$row['nazov_stanice']) { ?>selected="true" <?php }; ?>><?php echo
$row['nazov_stanice']; ?></option >
 <?php }
 ?>
 </select>
 <label for="sel2">Ukazovateľ:</label>
 <select name="ukazovatel">
 <option disabled selected value> -- Vyber -- </option>
 <option <?php if ($_POST['ukazovatel'] == 'Rýchlosť vetra') { ?>selected="true" <?php }; ?>>Rýchlosť vetra</option>
 <option <?php if ($_POST['ukazovatel'] == 'Slnečné žiarenie') { ?>selected="true" <?php }; ?>>Slnečné žiarenie</option>
 </select>
 <label for="sel3" >Čas:</label>
 <select name="cas"/>
 <option disabled selected value> -- Vyber -- </option>
 <option <?php if ($_POST['cas'] == 'Minútový') {
?>selected="true" <?php }; ?>>Minútový</option>
 <option <?php if ($_POST['cas'] == 'Hodinový') {
?>selected="true" <?php }; ?>>Hodinový</option>
 <option <?php if ($_POST['cas'] == 'Denný') {
?>selected="true" <?php }; ?>>Denný</option>
 </select>
 <label>N:</label>
 <input type="number" class="cisla" name="n" id="n"value="<?php
 if (isset($_POST['n']))
 echo $_POST['n'];
 else
 echo "5";
 ?>"/>
 <label>Polomer turbiny:</label>
 <input type="text" class="cisla" name="polomer_turbiny"
id="polomer_turbiny" value="<?php
 if (isset($_POST['polomer_turbiny']))
 echo $_POST['polomer_turbiny'];
 else
 echo "0.7";
 ?>"/>
 <label for="sel1" >VUC:</label>
 <select name="vuc" class="siblings" id="vuc">
 <option disabled selected value> -- Vyber -- </option>
 <?php
 $sql = "SELECT nazov_vuc FROM openweather_vuc";
 $result = $conn->query($sql);
 while ($row = mysqli_fetch_array($result)) {
 ?> <option <?php if ($_POST['vuc'] == $row['nazov_vuc'])
{ ?>selected="true" <?php }; ?>><?php echo $row['nazov_vuc']; ?></option
>
 <?php }
 ?>
 </select>
 <input type="submit" name="submit" value="Zobraz data" />
 </form>

 <?php
 if ($_POST['ukazovatel'] !== null) {
 if ($_POST['ukazovatel'] === 'Rýchlosť vetra') {
 $ukazovatel = 'rychlost_vetra';
 $legendaGrafu = $_POST['ukazovatel'] . ' [m/s]';
 $legendaGrafu2[0] = 'P[kW]';
 $legendaGrafu2[1] = 'E[kWh]';
 $legendaGrafu2[2] = 'PL[kW]';
 $legendaGrafu2[3] = 'EL[kWh]';
 } else {
 $ukazovatel = 'oblacnost';
 $legendaGrafu = 'Oblačnosť' . ' [%]';
 $legendaGrafu2 = $_POST['ukazovatel'] . ' [%]';
 }
 }
  $N = $_POST['n'];
 $mesto = $_POST['mesto'];
 $cas = $_POST['cas'];
 $vuc = $_POST['vuc'];
 $GLOBALS['polomer_turbiny'] = floatval($_POST['polomer_turbiny']);
 $GLOBALS['plocha_turbiny'] = pow($GLOBALS['polomer_turbiny'], 2) * M_PI;
 $namerane_data = '';
 $namerane_dataLimited = '';
 $vykonNormal = '';
 $vykonLimited = '';
 $energiaNormal = '';
 $energiaLimited = '';
 $sql = "SELECT value FROM admin_setup WHERE param='hustota_vzduchu'";
 $result = $conn->query($sql);
 while ($row = mysqli_fetch_array($result)) {
 $GLOBALS['hustota_vzduchu'] = $row['value'];
 }

 if ($cas == 'Minútový') {
 $GLOBALS['typ'] = 'line';
 $GLOBALS['deltaT'] = (60 * 5) / 3600;
 } elseif ($cas == 'Hodinový') {
 $GLOBALS['typ'] = 'bar';
 $GLOBALS['deltaT'] = 1;
 } else {
 $GLOBALS['typ'] = 'bar';
 $GLOBALS['deltaT'] = (3600 * 24) / 3600;
 }

     //ak je vybrate mesto
    if ($mesto !== NULL) {
        if ($cas == 'Minútový') {
            //query pre nelimitovane data
            $sql = "SELECT $ukazovatel FROM openweather_namerane_data
WHERE nazov_stanice='$mesto' order by id_merania desc LIMIT $N";
            //query pre limitovane data
            $sql2 = "SELECT
 CASE
 WHEN $ukazovatel < (SELECT value from admin_setup where
param='min_rychlost') THEN 0
 WHEN $ukazovatel > (SELECT value from admin_setup where
param='max_rychlost') THEN 0
 ELSE $ukazovatel
 END as $ukazovatel
 FROM openweather_namerane_data WHERE nazov_stanice='$mesto'
order by id_merania desc LIMIT $N";
        } elseif ($cas == 'Hodinový') {
            //query pre nelimitovane data
            $sql = "SELECT ROUND(avg($ukazovatel),3) as priemer,
hour(datum_cas_vlozenia) as hour
 FROM `openweather_namerane_data`
WHERE datum_cas_vlozenia BETWEEN
 DATE_FORMAT(DATE_SUB(NOW(), INTERVAL $N HOUR), '%Y-
%m-%d %H:00:00')
 AND
DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 0 HOUR), '%Y-%m-
%d %H:59:59')
 AND nazov_stanice='$mesto'
 group by hour
 ORDER BY hour DESC";
            //query pre limitovane data
            $sql2 = "SELECT ROUND(avg($ukazovatel),3) as priemer,
hour(datum_cas_vlozenia) as hour
 FROM (SELECT
 CASE
WHEN $ukazovatel < (SELECT value from
admin_setup where param='min_rychlost') THEN 0
 WHEN $ukazovatel > (SELECT value from
admin_setup where param='max_rychlost') THEN 0
 ELSE $ukazovatel
END as $ukazovatel, datum_cas_vlozenia
 FROM openweather_namerane_data WHERE
nazov_stanice='$mesto' order by id_merania desc) alias
 WHERE datum_cas_vlozenia BETWEEN
 DATE_FORMAT(DATE_SUB(NOW(), INTERVAL $N HOUR), '%Y-
%m-%d %H:00:00')
 AND
DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 0 HOUR), '%Y-%m-
%d %H:59:59')
 group by hour
 ORDER BY hour DESC";
        } else {
            //query pre nelimitovane data
            $sql = "SELECT ROUND(avg($ukazovatel), 3) as priemer,
day(datum_cas_vlozenia) as day
 FROM `openweather_namerane_data`
 WHERE nazov_stanice = '$mesto'
 AND datum_cas_vlozenia <= DATE_ADD(CURDATE(), INTERVAL -0
DAY)
 AND datum_cas_vlozenia >= DATE_ADD(CURDATE(), INTERVAL -$N
DAY)
 GROUP BY day
 ORDER BY day DESC";
            //query pre limitovane data
            $sql2 = "SELECT ROUND(avg($ukazovatel), 3) as priemer,
day(datum_cas_vlozenia) as day
 FROM
 (SELECT
CASE
WHEN $ukazovatel < (SELECT value from admin_setup
where param='min_rychlost') THEN 0
 WHEN $ukazovatel > (SELECT value from admin_setup
where param='max_rychlost') THEN 0
 ELSE $ukazovatel
END as rychlost_vetra, datum_cas_vlozenia
 FROM openweather_namerane_data WHERE
nazov_stanice='$mesto' order by id_merania desc) alias
 WHERE datum_cas_vlozenia <= DATE_ADD(CURDATE(), INTERVAL 0
DAY)
 AND datum_cas_vlozenia >= DATE_ADD(CURDATE(), INTERVAL -$N
DAY)
 GROUP BY day
 ORDER BY day DESC";
        }
        // ak je vybrate VUC
    }

else {
        $sql = "SELECT count(id_stanice) as pocet FROM
openweather_stanice, openweather_vuc
 where openweather_stanice.id_vuc = openweather_vuc.id_vuc
 and openweather_vuc.nazov_vuc = '$vuc' ";
        $result = $conn->query($sql);
        while ($row = mysqli_fetch_array($result)) {
            $GLOBALS['pocet'] = $row['pocet'];
        }
        $limit = $GLOBALS['pocet'] * $N;
        if ($cas == 'Minútový') {
            //query pre nelimitovane data
            $sql = "SELECT ROUND(avg($ukazovatel),3) as priemer
 FROM
 (SELECT openweather_namerane_data.$ukazovatel,
openweather_namerane_data.datum_cas_vlozenia
 FROM `openweather_namerane_data`, openweather_vuc,
openweather_stanice
 where openweather_vuc.id_vuc = openweather_stanice.id_vuc
 AND openweather_stanice.id_stanice =
openweather_namerane_data.id_stanice
 AND openweather_vuc.nazov_vuc = '$vuc'
 order by openweather_namerane_data.id_merania desc
 LIMIT $limit) d
 GROUP BY datum_cas_vlozenia";
            //query pre limitovane data
            $sql2 = "SELECT ROUND(avg($ukazovatel), 3) as priemer
 FROM
 (SELECT
 CASE
 WHEN $ukazovatel < (SELECT value from admin_setup where
param='min_rychlost') THEN 0
 WHEN $ukazovatel > (SELECT value from admin_setup where
param='max_rychlost') THEN 0
 ELSE $ukazovatel
 END as $ukazovatel,
 datum_cas_vlozenia
 FROM `openweather_namerane_data`, openweather_vuc,
openweather_stanice
 where openweather_vuc.id_vuc = openweather_stanice.id_vuc
 AND openweather_stanice.id_stanice =
openweather_namerane_data.id_stanice
 AND openweather_vuc.nazov_vuc = '$vuc'
 order by openweather_namerane_data.id_merania desc
 LIMIT $limit) d
 GROUP BY datum_cas_vlozenia";
        } elseif ($cas == 'Hodinový') {
            //query pre nelimitovane data
            $sql = "SELECT
ROUND(avg(openweather_namerane_data.$ukazovatel), 3) as priemer,
hour(openweather_namerane_data.datum_cas_vlozenia) as hour
 FROM `openweather_namerane_data`, openweather_vuc,
openweather_stanice
 where openweather_vuc.id_vuc = openweather_stanice.id_vuc
 AND openweather_stanice.id_stanice =
openweather_namerane_data.id_stanice
 AND openweather_namerane_data.datum_cas_vlozenia BETWEEN
 DATE_FORMAT(DATE_SUB(NOW(), INTERVAL $N HOUR), '%Y-%m-%d
%H:00:00')
 AND
 DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 0 HOUR), '%Y-%m-%d
%H:59:59')
 AND openweather_vuc.nazov_vuc = '$vuc'
 GROUP by hour
 ORDER BY HOUR DESC";
            //query pre limitovane data
            $sql2 = "SELECT ROUND(avg($ukazovatel), 3) as priemer,
hour(datum_cas_vlozenia) as hour
 FROM
 (SELECT
 CASE
 WHEN $ukazovatel < (SELECT value from admin_setup where
param='min_rychlost') THEN 0
 WHEN $ukazovatel > (SELECT value from admin_setup where
param='max_rychlost') THEN 0
 ELSE $ukazovatel
 END as $ukazovatel,
 datum_cas_vlozenia
 FROM
 `openweather_namerane_data`, openweather_vuc,
openweather_stanice
 where openweather_vuc.id_vuc = openweather_stanice.id_vuc
 AND openweather_stanice.id_stanice =
openweather_namerane_data.id_stanice
 AND openweather_namerane_data.datum_cas_vlozenia BETWEEN
 DATE_FORMAT(DATE_SUB(NOW(), INTERVAL $N HOUR), '%Y-%m-%d
%H:00:00')
 AND
 DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 0 HOUR), '%Y-%m-%d
%H:59:59')
 AND openweather_vuc.nazov_vuc = '$vuc' ) alias
 GROUP by hour
 ORDER BY HOUR DESC";
        } else {
            //query pre nelimitovane data
            $sql = "SELECT
ROUND(avg(openweather_namerane_data.$ukazovatel), 3) as priemer,
day(openweather_namerane_data.datum_cas_vlozenia) as day
 FROM `openweather_namerane_data`, openweather_vuc,
openweather_stanice
 where openweather_vuc.id_vuc = openweather_stanice.id_vuc
 AND openweather_stanice.id_stanice =
openweather_namerane_data.id_stanice
 AND openweather_namerane_data.datum_cas_vlozenia <=
DATE_ADD(CURDATE(), INTERVAL -0 DAY)
 AND openweather_namerane_data.datum_cas_vlozenia >=
DATE_ADD(CURDATE(), INTERVAL -$N DAY)
 AND openweather_vuc.nazov_vuc = '$vuc'
 GROUP by day
 order by day desc";
            //query pre limitovane data
            $sql2 = "SELECT ROUND(avg($ukazovatel), 3) as priemer,
day(datum_cas_vlozenia) as day
 FROM
 (SELECT
 CASE
 WHEN $ukazovatel < (SELECT value from admin_setup where
param='min_rychlost') THEN 0
 WHEN $ukazovatel > (SELECT value from admin_setup where
param='max_rychlost') THEN 0
 ELSE $ukazovatel
 END as $ukazovatel, datum_cas_vlozenia
 FROM
 `openweather_namerane_data`, openweather_vuc,
openweather_stanice
 where openweather_vuc.id_vuc = openweather_stanice.id_vuc
 AND openweather_stanice.id_stanice =
openweather_namerane_data.id_stanice
 AND openweather_namerane_data.datum_cas_vlozenia <=
DATE_ADD(CURDATE(), INTERVAL -0 DAY)
 AND openweather_namerane_data.datum_cas_vlozenia >=
DATE_ADD(CURDATE(), INTERVAL -$N DAY)
 AND openweather_vuc.nazov_vuc = '$vuc' )alias
 GROUP by day
 order by day desc";
        }
    }
//rychlost_vetra
    if ($ukazovatel === 'rychlost_vetra') {
        //nelimitované nameraná dáta + nelimitovaný výkon a energia
        if ($mesto !== NULL) {
            $i = 0;
            $energia[$i] = 0;
            $result = $conn->query($sql);
            while ($row = mysqli_fetch_array($result)) {
                if ($cas == 'Minútový') {
                    $namerane_data = $namerane_data . '"' . $row[$ukazovatel] . '",';
                    $vykonNormal = $vykonNormal . '"' . round(0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row[$ukazovatel], 3), 3) . '",';
                    $i += 1;
                    $energia[$i] = round($energia[$i - 1] + (0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row[$ukazovatel], 3)) * $GLOBALS['deltaT'], 3);
                    $energiaNormal = $energiaNormal . '"' . $energia[$i] . '",';
                } else {
                    $namerane_data = $namerane_data . '"' . $row['priemer'] . '",';
                    $vykonNormal = $vykonNormal . '"' . round(0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row['priemer'], 3), 3) . '",';
                    $i += 1;
                    $energia[$i] = round($energia[$i - 1] + (0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row['priemer'], 3)) * $GLOBALS['deltaT'], 3);
                    $energiaNormal = $energiaNormal . '"' . $energia[$i]
                        . '",';
                }
            }
            //VUC
        } else {
            $i = 0;
            $energia[$i] = 0;
            $result = $conn->query($sql);
            while ($row = mysqli_fetch_array($result)) {
                $namerane_data = $namerane_data . '"' . $row['priemer'] . '",';
                $vykonNormal = $vykonNormal . '"' . round(0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row['priemer'], 3), 3) . '",';
                $i += 1;
                $energia[$i] = round($energia[$i - 1] + (0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row['priemer'], 3)) * $GLOBALS['deltaT'], 3);
                $energiaNormal = $energiaNormal . '"' . $energia[$i] .
                    '",';
            }
        }
        //limitované nameraná dáta + limitovaný výkon a energia
        if ($mesto !== NULL) {
            $i = 0;
            $energia[$i] = 0;
            $result = $conn->query($sql2);
            while ($row = mysqli_fetch_array($result)) {
                if ($cas == 'Minútový') {
                    $namerane_dataLimited = $namerane_dataLimited . '"' . $row[$ukazovatel] . '",';
                    $vykonLimited = $vykonLimited . '"' . round(0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row[$ukazovatel], 3), 3) . '",';
                    $i += 1;
                    $energia[$i] = round($energia[$i - 1] + (0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row[$ukazovatel], 3)) * $GLOBALS['deltaT'], 3);
                    $energiaLimited = $energiaLimited . '"' .
                        $energia[$i] . '",';
                } else {
                    $namerane_dataLimited = $namerane_dataLimited . '"' . $row['priemer'] . '",';
                    $vykonLimited = $vykonLimited . '"' . round(0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row['priemer'], 3), 3) . '",';
                    $i += 1;
                    $energia[$i] = round($energia[$i - 1] + (0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row['priemer'], 3)) * $GLOBALS['deltaT'], 3);
                    $energiaLimited = $energiaLimited . '"' . $energia[$i] . '",';
                }
            }
            //VUC
        } else {
            $i = 0;
            $energia[$i] = 0;
            $result = $conn->query($sql2);
            while ($row = mysqli_fetch_array($result)) {
                $namerane_dataLimited = $namerane_dataLimited . '"' . $row['priemer'] . '",';
                $vykonLimited = $vykonLimited . '"' . round(0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row['priemer'], 3), 3) . '",';
                $i += 1;
                $energia[$i] = round($energia[$i - 1] + (0.0005 * $GLOBALS['hustota_vzduchu'] * $GLOBALS['plocha_turbiny'] * pow($row['priemer'], 3)) * $GLOBALS['deltaT'], 3);
                $energiaLimited = $energiaLimited . '"' . $energia[$i] . '",';
            }
        }
        //viditelnost
    }

  ?>


















 <div class="containerOut">
 <div id="map" class="container"></div>
 <div class="container">
 <canvas id="chart"></canvas>
 <script>
window.chartColors = {
red: 'rgb(255, 99, 132)',
 orange: 'rgb(255, 159, 64)',
 yellow: 'rgb(255, 205, 86)',
 green: 'rgb(75, 192, 192)',
 blue: 'rgb(54, 162, 235)',
 purple: 'rgb(153, 102, 255)',
 grey: 'rgb(201, 203, 207)'
};
var years = [];
for (var i = 1; i < <?php echo $N + 1; ?>; i++) {
years.push(i);
}
var ctx = document.getElementById("chart").getContext('2d');
var myChart = new Chart(ctx, {
type: '<?php echo $GLOBALS['typ']; ?>',
 backgroundColor: 'rgb(255, 99, 132)',
 data: {
 labels: years,
 datasets:
 [{
 label: '<?php echo $legendaGrafu; ?>',
 data: [<?php echo $namerane_data; ?>],
<?php if ($GLOBALS['typ'] === 'line') { ?>
 //backgroundColor: 'transparent',
 backgroundColor: 'rgb(255, 99, 132)',
 borderColor: 'rgb(255, 99, 132)', <?php } else { ?>
 backgroundColor: 'rgb(75, 192, 192)',
 borderColor: 'rgb(75, 192, 192)', <?php } ?>
 borderWidth: 3
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 scales: {yAxes: [{display: true}],
 xAxes: [{autoskip: true, maxTicketsLimit: 20}]},
 tooltips: {mode: 'index'},
 title: {
 display: true,
 text: 'Vybraná veličina',
 fontSize: 20
 },
 legend: {display: true, position: 'bottom', labels:
{fontColor: 'rgb(0,0,0)', boxWidth:20, fontSize: 16}}
 }
});
 </script>
 </div>
 <div class="container">
 <canvas id="chart2"></canvas>
 <script>
 var ctx =
document.getElementById("chart2").getContext('2d');
 var myChart = new Chart(ctx, {
 type: 'line',
 backgroundColor: 'rgb(255, 99, 132)',
 data: {
labels: years,
 datasets:
 [ {
label: '<?php
if ($_POST['ukazovatel'] === 'Rýchlosť vetra') {
 echo $legendaGrafu2[0];
} else {
 echo $legendaGrafu2;
}
?>',
 data: [<?php echo $vykonNormal;
?>],
 backgroundColor: 'transparent',
 borderColor: 'rgb(255, 99, 132)',
 borderWidth: 3
 },
{
label: '<?php
if ($_POST['ukazovatel'] === 'Rýchlosť vetra') {
 echo $legendaGrafu2[1];
} else {
 echo '';
}
?>',
 data: [<?php echo $energiaNormal; ?>],
 backgroundColor: 'transparent',
 borderColor: 'rgb(54, 162, 235)',
 borderWidth: 3
 },
{
label: '<?php
if ($_POST['ukazovatel'] === 'Rýchlosť vetra') {
 echo $legendaGrafu2[2];
} else {
 echo '';
}
?>',
 data: [<?php echo $vykonLimited; ?>],
 backgroundColor: 'transparent',
 borderColor: 'rgb(153, 102, 255)',
 borderWidth: 3
 },
{
label: '<?php
if ($_POST['ukazovatel'] === 'Rýchlosť vetra') {
 echo $legendaGrafu2[3];
} else {
 echo '';
}
?>',
 data: [<?php echo $energiaLimited; ?>],
 backgroundColor: 'transparent',
 borderColor: 'rgb(255, 205, 86)',
 borderWidth: 3
 } ]
},
options: {
responsive: true,
 maintainAspectRatio: false,
 scales: {scales: {yAxes: [{beginAtZero:
false}], xAxes: [{autoskip: true, maxTicketsLimit: 20}]}},
 tooltips: {mode: 'index'},
 title: {
display: true,
 text: 'Energetické ukazovatele',
 fontSize: 20
 },
legend: {display: true, position:
'bottom', labels: {boxWidth:20, fontSize: 16}}
 }
 });
 $('.siblings').change(function() {
 $(this)
 .siblings('.siblings')
 .attr('disabled', true)
 .siblings().removeAttr('disabled');
 });
 </script>
 </div>
 </div>
</body>
</html>
