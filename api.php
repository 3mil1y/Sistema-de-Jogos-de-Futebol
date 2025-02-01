<?php
function fetchApiData($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-Auth-Token: " . API_KEY
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    if($response === false) {
        return null;
    }

    return json_decode($response, true);
}
?>
