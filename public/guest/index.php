<?php
if (strlen($_REQUEST["os"]) > 16)
    exit();


$ch = curl_init("http://127.0.0.1:2049/api/localOnly/guest/fromHostOnly");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $query = http_build_query([
    "ip" => $_SERVER["REMOTE_ADDR"],
    "os" => $_REQUEST["os"],
]));

if (($response = curl_exec($ch)) == false)
    exit();

$decodedResult = json_decode($response);
if (@$decodedResult->result)
    echo "true";

$oldUmask = umask(0077);
$logFile = "/tmp/ccms-guest.log";
$fp = fopen($logFile, "a");
flock($fp, LOCK_EX);
fseek($fp, 0, SEEK_END);
fprintf($fp, "%s\n-----QUERY-----\n", date("Y-m-d H:i:s"));
fprintf($fp, "%s\n-----RESPONSE-----\n", $query);
fprintf($fp, "%s\n\n\n", $response);
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);
umask($oldUmask);