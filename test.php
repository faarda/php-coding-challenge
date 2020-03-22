<?php

$start_time = microtime(true);

sendRequest();

$end_time = microtime(true);

$execution_time = $end_time - $start_time;

echo "One request takes ".($execution_time)." secs to complete \n";
echo "The request time is at an average of about .6secs, refresh to see a new value \n";



//prepares the curl request
function sendRequest()
{
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,"https://jsonplaceholder.typicode.com/posts");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

    //execute curl request
    $result = curl_exec($ch);

    // Close cURL session handle
    curl_close($ch);

    return $ch;
}