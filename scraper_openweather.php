<?php

include 'conn_cloud.php';

$date = new DateTime("now", new DateTimeZone('Europe/Bratislava'));
$datum_cas_vlozenia = $date->format('Y-m-d H:i:s');
if (!function_exists('curl_init')) {
    die('Sorry cURL is not installed!');
}

$iterator = 1;
$apiKey = "1649293eef5e4722e544967dd84c9e63";
//všetky ID-èka lokalít, pre ktoré je volané API rozhranie
$cities = array(
    "3060972",
    "3061186",
    "3060589",
    "724800",
    "724627",
    "724535",
    "724503",
    "724443",
    "3058986",
    "3058897",
    "3058780",
    "724144",
    "3058531",
    "3058268",
    "3058202",
    "3058083",
    "723846",
    "723819",
    "3058000",
    "723747",
    "723468",
    "723455",
    "723358",
    "3057124",
    "723195",
    "3056589",
    "3056508",
    "3057630",
    "725168",
    "3061015",
    "3061188",
    "3057757",
    "3056523", 
    "3058210",
    "3058477",
    "3058472",
    "3059821",
    "3056459",
    "3057174",
    "3060835",
    "723417",
    "723736", 
    "3059101",
    "723713",
    "723526",
    "3059436",
    "724377",
    "3059179",
    "3057086",
    "723674",
    "723411",
    "3056495", 
    "3057197",
    "3058611",
    "3058493",
    "723559",
    "724015",
    "3061184",
    "724156",
    "3059244"
);
/*v cykle zavoláme API rozhranie pre kažú lokalitu
s využítím knižnice CURL, ktorá na tieto úèely slúži */
foreach ($cities as $city) {
    $googleApiUrl = "http://api.openweathermap.org/data/2.5/weather?id=" . $city . "&lang=en&units=metric&APPID=" . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response);

    $nazov_stanice = $data->name;
    //echo $nazov_stanice . "<br>";
    date_default_timezone_set('Europe/Bratislava');
    $datum = date('Y-m-d H:i:s', $data->dt);
    $datetime = new DateTime($datum);
    $datum_cas = $datetime->format('Y-m-d H:i:s');

    $meranie_timestamp = $data->dt;
    //echo $datum_cas . "<br>";
    $rychlost_vetra = $data->wind->speed;
    //echo $rychlost_vetra . "<br>";
    if ($rychlost_vetra === NULL) {
        $rychlost_vetra = 0;
    }        

    $smer_vetra = $data->wind->deg;
    //echo $smer_vetra . "<br>";
    if ($smer_vetra === NULL) {
        $smer_vetra = 0;
    }
    $oblacnost = $data->clouds->all;
    //echo $oblacnost . "<br>";
    if ($oblacnost === NULL) {
        $oblacnost = 0;
    }

    //uloženie údajov do tabu¾ky openweather_namerane_data z vyparsovanej odpovede API rozhrania
    $sql = "INSERT INTO openweather_namerane_data
(id_stanice,nazov_stanice,datum_cas_merania,meranie_timestamp,rychlost_vetra,smer_vetra,oblacnost,datum_cas_vlozenia) "
        . "VALUES ('$iterator', '$nazov_stanice', '$datum_cas',
'$meranie_timestamp', '$rychlost_vetra', '$smer_vetra', '$oblacnost','$datum_cas_vlozenia')";

    $iterator = $iterator + 1;

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        $chyba = "Error: " . $sql . "<br>" . $conn->error;
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>