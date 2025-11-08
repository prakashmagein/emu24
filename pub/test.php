<?php
$url = "https://emu24d.pracoda.com/wp-json/wc/v3/products";
$ck = "q830wc8zq7ggtiuo5lz8do0uhfs8nl1i";
$cs = "nbdelm92fm0g3knrowbddy23krluqspd";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "$ck:$cs");
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpcode\n";
echo "Response:\n$response";
