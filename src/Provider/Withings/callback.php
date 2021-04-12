<?php

    $code = $_GET['code'];

    if($code == ""){
        header('Location: http://localhost:5000/user');
        exit;
    }

    $CLIENT_ID = "68438600e7e6225de1a735879cdee096cbd9a1f32cd8ff75d28c6bfc537ffa7e";
    $CLIENT_SECRET = "775d652a2d7bddf617b7bb650c4dde4ce526d22ab6eb4e422c9aa2d3eaac0aad";
    $URL = "https://wbsapi.withings.net/v2/oauth2";

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/v2/oauth2");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'action' => 'requesttoken',
        'grant_type' => 'authorization_code',
        'client_id' => '68438600e7e6225de1a735879cdee096cbd9a1f32cd8ff75d28c6bfc537ffa7e',
        'client_secret' => '775d652a2d7bddf617b7bb650c4dde4ce526d22ab6eb4e422c9aa2d3eaac0aad',
        'code' => 'mtwsikawoqleuroqcluggflrqilrnqbgqvqeuhhh',
        'redirect_uri' => 'https://www.withings.com'
    ]));

    $rsp = curl_exec($ch);
    curl_close($ch);

    var_dump($rsp);