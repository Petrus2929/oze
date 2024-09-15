<?php
$sql = "SELECT nazov_stanice,sirka,dlzka,id_vuc FROM openweather_stanice";
$result = $conn->query($sql);
$sirky = [];
$dlzky = [];
$nazvy = [];
$vucky = [];
if ($result->num_rows > 0) {
 // output data of each row
 while ($row = $result->fetch_assoc()) {
 array_push($sirky, $row["sirka"]);
 array_push($dlzky, $row["dlzka"]);
 array_push($nazvy, $row["nazov_stanice"]);
 array_push($vucky, $row["id_vuc"]);
 }
} else {
 echo "0 results";
}
?>
<script type="text/javascript">
 var passedsirky =
<?php echo '["' . implode('", "', $sirky) . '"]' ?>;
 var passeddlzky =
<?php echo '["' . implode('", "', $dlzky) . '"]' ?>;
 var passednazvy =
<?php echo '["' . implode('", "', $nazvy) . '"]' ?>;
 var passedvucky =
<?php echo '["' . implode('", "', $vucky) . '"]' ?>;
</script>
