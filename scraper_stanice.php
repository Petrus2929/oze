<?php
include 'conn_cloud.php';

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
    //print_r($data);
    $nazov_stanice = $data->name;

    $sirka = $data->coord->lat;
    $dlzka = $data->coord->lon;

    //uloženie údajov do tabu¾ky openweather_namerane_data z vyparsovanej odpovede API rozhrania
    $sql = "INSERT INTO openweather_stanice(id_stanice,nazov_stanice, sirka, dlzka, id_vuc) "
        . "VALUES ('$iterator', '$nazov_stanice', '$sirka','$dlzka', 1)";

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