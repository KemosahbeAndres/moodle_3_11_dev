<?php

/*
$text = "Tzo4OiJzdGRDbGFzcyI6NTp7czoxMjoic2ljX2NvdXJzZWlkIjtzOjE6IjIiO3M6MTA6InNpY19zdGF0dXMiO3M6MToiMCI7czoxNjoic2ljX2NvZGlnb19ncnVwbyI7czowOiIiO3M6MTc6InNpY19jb2RpZ29fb2ZlcnRhIjtzOjA6IiI7czo3OiJzaWNfcm9sIjtzOjE6IjYiO30=";

var_dump(unserialize(base64_decode($text)));

$url = 'https://pokeapi.co/api/v2/pokemon';
$url = "https://auladigital.sence.cl/gestor/API/avance-sic/historialEnvios?idSistema=1350";

$curl = curl_init();

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($curl, CURLOPT_HTTPGET, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($curl, CURLOPT_VERBOSE, 0);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Accept: application/json'
));

// execute and return string (this should be an empty string '')
$str = curl_exec($curl);

$code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

$response = json_decode(curl_exec($curl), false);

curl_close($curl);

// the value of $str is actually bool(true), not empty string ''
echo "#############################################################\n";
echo "STATUS CODE: ". $code;
echo "\n#############################################################\n";
var_dump($str);
echo "#############################################################\n";
echo "STATUS CODE: ". $code . PHP_EOL;
echo "ALIVE: ";
echo (is_numeric($code) && $code == 200) ? "true" : "false";
echo "\n#############################################################\n";

if(isset($response->error)){
    var_dump($response->error);
}

*/

/*

$payload = "10610838-4";

$clean = str_replace('.', '', trim($payload));
$splitted = preg_split("/[-]/", $clean);
$rut = array_reverse(str_split($splitted[0]));
$dv = intval($splitted[1]);
$numeros = array(2,3,4,5,6,7);
$i = 0;
$resultados = array();
foreach ($rut as $n){
    $res = intval($n) * $numeros[$i];
    $resultados[] = $res;
    echo "#[${i}] ->  {$n} * {$numeros[$i]} = {$res}#\n";
    $i = ($i < 5) ? $i + 1 : 0;
}
$suma = 0;
foreach($resultados as $numero) {
    $suma += intval($numero);
}
$cociente = intval($suma / 11);
$resto = $suma - ($cociente * 11);
$modulo = intval(11 - $resto);


echo "\n";
echo "{$splitted[0]} - {$splitted[1]}";
echo "\n";
print_r($resultados);
echo "\n";
print_r($suma);
echo "\n";
echo "Modulo: {$modulo}";

*/
/*

function desencriptar($valor){
    // global $CFG;
    // $clave  = $CFG->dbpass;
    //$clave = "S]yp6t!o56";
    $clave = "daoew2ua2gni";
    $method = 'aes-256-cbc';
    $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");
    $encrypted_data = base64_decode($valor);
    return openssl_decrypt($valor, $method, $clave, 0, $iv);
}

$sence = array(
    "urliniciosence"=>"F2k23NyReJdLZD2eTm/5wmRLtT4mflI+J7RF6jRDxte02XM6wApR8RdrIcc+WbASTMhbDnrmroty2CjJXQ1uJg==",
    "urlcierresence"=>"F2k23NyReJdLZD2eTm/5wmRLtT4mflI+J7RF6jRDxtdKGx+ZwHZadQ/Ew89sok8zNguoMMS3cnkR485/DVQ3qg==",
    "urliniciosencet"=>"F2k23NyReJdLZD2eTm/5wgxihLJBBUYGGyO+Li+N9WEGS9kS1SUD/dTQgWr8097Zib0n8buQeY4/u8Yp7Lnsgg==",
    "urlcierresencet"=>"F2k23NyReJdLZD2eTm/5wgxihLJBBUYGGyO+Li+N9WET4+s0iUJxsrcE+DYoYHHOPkvd7d7VHeDvfhAsDn31+A==",
    "rutotec"=>"jXhRae2lL03zFmb1or+L9A==",
    "token"=>"BWM9qyD4OCRnxSS+Dq8UZkPLI6KEQ4SDdbr0HpHUPYXfAsRKXzmXj+aRItJtr8zU",
    "urlexito"=>"WHyyelmG/W49cUE8wE3HmGCaUIIQFwftxX4u04W0oZel7SS7kidHo2Hu67zrko2o",
    "urlfracaso"=>"WHyyelmG/W49cUE8wE3HmKRW0+4iOO2wONcBAXJ92j2X+nEK/g7LoR0T55nofh7f"
);

$token = desencriptar($sence['token']);
$rut = desencriptar($sence['rutotec']);
echo "\n";

$curl = curl_init();
curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($curl, CURLOPT_HTTPGET, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($curl, CURLOPT_VERBOSE, 0);

$url = "https://auladigital.sence.cl/gestor/API/avance-sic/historialEnvios?";
$url .= http_build_query([
    'rutOtec' => $rut,
    'idSistema' => 1350,
    'token' => $token
]);

curl_setopt($curl, CURLOPT_URL, $url);

$curl_data = curl_exec($curl);

print_r($curl_data);


*/

$o = new stdClass();

echo empty(get_object_vars($o)) ? "si" : "no";

echo "\n";
echo "\n";

echo date("H:i", );

$content = 'W3siYWx1bW5vIjp7InJ1dEFsdW1ubyI6OTQ0NTQzNSwiZHZBbHVtbm8iOiIyIiwidGllbXBvQ29uZWN0aXZpZGFkIjowLCJldmFsdWFjaW9uRmluYWwiOjAsImVzdGFkbyI6MSwicG9yY2VudGFqZUF2YW5jZSI6MCwiZmVjaGFJbmljaW8iOiIyMDIxLTExLTE5IDAwOjAwOjAwIiwiZmVjaGFGaW4iOiIyMDIxLTEyLTMxIDAwOjAwOjAwIiwibGlzdGFNb2R1bG9zIjpbeyJjb2RpZ29Nb2R1bG8iOiJDNDY5ODItTzU2NTY1NjU2NS1NMSIsInRpZW1wb0NvbmVjdGl2aWRhZCI6MCwicG9yY2VudGFqZUF2YW5jZSI6MCwiZXN0YWRvIjoxLCJmZWNoYUluaWNpbyI6IjIwMjEtMTEtMTcgMjM6NTM6MDAiLCJmZWNoYUZpbiI6IjIwMjEtMTItMzEgMjM6NTk6MDAiLCJub3RhTW9kdWxvIjowLCJjYW50QWN0aXZpZGFkQXNpbmNyb25pY2EiOjEsImNhbnRBY3RpdmlkYWRTaW5jcm9uaWNhIjowLCJsaXN0YUFjdGl2aWRhZGVzIjpbeyJjb2RpZ29BY3RpdmlkYWQiOiJMZWN0dXJhIn1dfV19LCJjb2RpZ28iOiIwMjUiLCJtZW5zYWplIjoiVW5vIG8gbWFzIGVycm9yZXMgZW5jb250cmFkb3M6IG1vZHVsby5wb3JjZW50YWplQXZhbmNlIGRlYmUgc2VyIG1heW9yIGFsIGFudGVyaW9yICgxMDAuMDAwMDApIiwiY29kaWdvTW9kdWxvIjoiQzQ2OTgyLU81NjU2NTY1NjUtTTEifV0';
echo "\n";
print_r(base64_decode($content));

echo "\n";
echo "\nGOOD\n";
$content = 'W3sicnV0QWx1bW5vIjo5NDQ1NDM1LCJkdkFsdW1ubyI6IjIiLCJ0aWVtcG9Db25lY3RpdmlkYWQiOjAsImV2YWx1YWNpb25GaW5hbCI6MCwiZXN0YWRvIjoxLCJwb3JjZW50YWplQXZhbmNlIjoxMDAsImZlY2hhSW5pY2lvIjoiMjAyMS0xMS0xOSAwMDowMDowMCIsImZlY2hhRmluIjoiMjAyMS0xMi0zMSAwMDowMDowMCIsImxpc3RhTW9kdWxvcyI6W3siY29kaWdvTW9kdWxvIjoiQzQ2OTgyLU81NjU2NTY1NjUtTTEiLCJ0aWVtcG9Db25lY3RpdmlkYWQiOjAsInBvcmNlbnRhamVBdmFuY2UiOjEwMCwiZXN0YWRvIjoxLCJmZWNoYUluaWNpbyI6IjIwMjEtMTEtMTcgMjM6NTM6MDAiLCJmZWNoYUZpbiI6IjIwMjEtMTItMzEgMjM6NTk6MDAiLCJub3RhTW9kdWxvIjowLCJjYW50QWN0aXZpZGFkQXNpbmNyb25pY2EiOjAsImNhbnRBY3RpdmlkYWRTaW5jcm9uaWNhIjoxLCJsaXN0YUFjdGl2aWRhZGVzIjpbeyJjb2RpZ29BY3RpdmlkYWQiOiJMZWN0dXJhIn1dfV0sImZlY2hhRWplY3VjaW9uIjoiMjAyMy0wNy0yMSAwMzo1Mzo0OSJ9XQ';
//$content = 'Tzo4OiJzdGRDbGFzcyI6NTp7czoxMjoic2ljX2NvdXJzZWlkIjtpOjQ7czoxMDoic2ljX3N0YXR1cyI7czoxOiIwIjtzOjE3OiJzaWNfY29kaWdvX29mZXJ0YSI7czo0OiJhYWFhIjtzOjE2OiJzaWNfY29kaWdvX2dydXBvIjtzOjQ6ImJiYmIiO3M6Nzoic2ljX3JvbCI7czoxOiI2Ijt9';
print_r(base64_decode($content));
//$bool = boolval(unserialize(base64_decode($content))->sic_status);
//var_dump($bool);
//echo $bool ? "SI" : "NO";

echo "\n\n";

if(0) {
    echo "\nNOT NULL\n";
}else{
    echo "\nNULL\n";
}