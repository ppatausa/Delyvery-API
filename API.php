<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

//variables
$user="root";
$password="Cpu25Pro";
$database="traccar";
$user1="cha30ece17_konotopgps";
$password1="7ZfIsiWo50";
$database1="cha30ece17_konotopgps";
$version="1.1";
$unixTime = time();
$timesrever =  date("r", $unixTime);
$maparray = array();
//restrict SQL query to this month.
//sometimes the database gets fed with dates decades in the future when the
//device has no GPS signal. 4 digit year, 2 digit month. In SQL % means *
$sqltoday=date("Y-m")."%";

//connect to base of this app
$link1 = mysql_connect("localhost","$user1","$password1") or die("Сервер недоступен или ошибка подключения" . mysqli_error($link1));
mysql_select_db($database1) or die("Не могу подключиться к базе.");
$query1 = "SELECT * FROM trackers";
$res1 = mysql_query($query1);

//Main loop
while ($row = mysql_fetch_array($res1, MYSQL_NUM)) {
    ${"IMEI".$row[0]}=$row[1];
    ${"type".$row[0]}=$row[2];
    ${"number".$row[0]}=$row[3];
//    printf("IMEI трекера %s: %s, Тип транспорту: %s, Номер маршруту:  %s, </br>", $row[0], $row[1],$row[2],$row[3]);

//    connect to baze traccar
$link = mysql_connect("185.25.117.47","$user","$password") or die("Сервер недоступен или ошибка подключения" . mysqli_error($link));
mysql_select_db($database) or die("Не могу подключиться к базе.");
//position id query
$query = "SELECT `id` FROM `tc_devices` WHERE `uniqueid` = '$row[1]'" or die("Error in the consult.." . mysqli_error($link));
$res = mysql_query($query);
//extract result from array
$deviceid = mysql_fetch_array($res);
$id=$deviceid['id'];
$query = "SELECT `deviceTime`,`longitude`,`latitude`,`speed` FROM `tc_positions` WHERE `deviceId` = '$id' AND `valid` = '1' AND deviceTime LIKE '$sqltoday' ORDER BY deviceTime desc LIMIT 0,1" or die("Ошибка запроса" . mysqli_error($link));
$result = mysql_query($query);
${"latlong".$row[0]} = mysql_fetch_array($result);
${"timestampSQL".$row[0]}= strtotime(${"latlong".$row[0]}['deviceTime']);

   // printf("Широта".${"timestampSQL".$row[0]}."долгота".${"latlong".$row[0]}['latitude'] );

    $maparray[] = array(
        'type' => $row[2],
        'number' => $row[3],
        'gps_id' => $row[1],
        'lat' => ${"latlong".$row[0]}['latitude'],
        'lng' => ${"latlong".$row[0]}['longitude'],
        'timestamp' =>  ${"timestampSQL".$row[0]}
    );

}

//$IMEI6='027046528576';// Трамвай Маршрут 1 8-1
//$IMEI7='027046527594';// Трамвай Маршрут 2 1-2
//$IMEI8='027046529574';// Трамвай Маршрут 1 2-1
//$IMEI9='027046531497';// Трамвай Маршрут 1 3-1
//$IMEI10='027046531042';// Трамвай Маршрут 2 4-2
//$IMEI11='027046531273';// Трамвай Маршрут 1 5-1
//$IMEI12='027046531513';// Трамвай Маршрут 2 6-2
//$IMEI13='027046527628';// Трамвай Маршрут 3 7-3
//$IMEI14='027046528139';// Трамвай Маршрут 3 9-2
//$IMEI15='027046536447';// Трамвай Маршрут 3 10-2
//Data for json

$toparray = array(
    'version' => $version,
    'timestamp' => $unixTime,
    'positions' => $maparray
);

$output = json_encode($toparray, JSON_PRETTY_PRINT);
//file_put_contents('konotop.json',$toparray);
file_put_contents('konotop.json', $output);
echo $output;

$zip = new ZipArchive();
$res = $zip->open('konotop.zip', ZipArchive::CREATE);
$zip->addFromString('konotop.json', $output);
//$zip->addFile('data.txt', 'entryname.txt');
$zip->close();



?>
