<?php
	require_once 'vendor/autoload.php';
	
	use GuzzleHttp\Client;
	   
	$merchantRef = "1596619544341";
	//$url = "http://127.0.0.1:3000/merchants/000000000000001/transactions/" . $merchantRef;
	$url = "https://cjpazdufok.execute-api.ap-east-1.amazonaws.com/v1/merchants/000000000000001/transactions/" . $merchantRef; 
	$now = new Datetime("now");
	$now->setTimeZone(new DateTimeZone('UTC'));
	$request_datetime = $now->format('Y-m-d\TH:i:s\Z');
	$payload = "000000000000001" . $merchantRef . $request_datetime;
	// create digital signature
	$pkeyid = openssl_pkey_get_private("file://./custpri.pem");
	openssl_sign($payload, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
	openssl_free_key($pkeyid);
		
	// initiate request
	$client = new Client();
	$response = $client->request('GET', $url, [
		'headers' => [
			'Content-Type' => 'application/json',
			//'Spiral-Client-Id' => '000000000000001',
			'Spiral-Request-Datetime' => $request_datetime,
			'Spiral-Client-Signature' => base64_encode($signature)
		]
	]);
	
	// check server signature
	$pubkeyid = openssl_pkey_get_public("file://./spiralpub.pem");
	$server_sig_payload = "000000000000001" . $merchantRef . $response->getHeader('Spiral-Request-Datetime')[0];
	$ok = openssl_verify($server_sig_payload, base64_decode($response->getHeader('Spiral-Server-Signature')[0]), $pubkeyid, OPENSSL_ALGO_SHA256);
	if ($ok == 1) {
		echo $response->getBody();
	} elseif ($ok == 0) {
		echo "bad";
	} else {
		echo "ugly, error checking signature";
	}
	openssl_free_key($pubkeyid);
	
?>