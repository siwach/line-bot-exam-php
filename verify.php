<?php
$access_token = 'o9NZ7KFnqWig2wU0rodJtgQH5I93Wq6W/02r/JyUeptCCJ0mOzH1FONFMFpzK41mUErzxIda5u0LUEAA5vixaRC/XB5owB0HxWoyYeoaPz5yF0FFX4PCHWeL3Nn6TWOSs9NKkReGj6njWyR12R/5jQdB04t89/1O/w1cDnyilFU=';


$url = 'https://api.line.me/v1/oauth/verify';

$headers = array('Authorization: Bearer ' . $access_token);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$result = curl_exec($ch);
curl_close($ch);

echo $result;