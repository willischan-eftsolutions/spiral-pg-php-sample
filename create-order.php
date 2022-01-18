<?php
	require_once 'vendor/autoload.php';
	
	use GuzzleHttp\Client;
	
	$merchantRef = (string) (round(microtime(true) * 1000));
	$url = "https://sandbox-api-checkout.spiralplatform.com/v1/merchants/eftit/transactions/" . $merchantRef; 
	$data = json_encode([
		"clientId" => "eftit",
		"cmd" => "SALESESSION",
		"type" => "VM",
		"amt" => 1.00,
		"merchantRef" => $merchantRef,
		"channel" => "WEB",
		"successUrl" => "http://dummyhost/mpgstestsuccess.html",
		"failureUrl" => "http://dummyhost/mpgstestfail.html",
		"webhookUrl" => "http://dummyhost/webhook",
		"goodsName" => "Goods Name"
	]);
	$now = new Datetime("now");
	$now->setTimeZone(new DateTimeZone('UTC'));
	$request_datetime = $now->format('Y-m-d\TH:i:s\Z');
	$payload = "eftit" . $merchantRef . $request_datetime;
	// create digital signature
	$pkeyid = openssl_pkey_get_private("file://./custpri.pem");
	openssl_sign($payload, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
	openssl_free_key($pkeyid);
		
	// initiate request
	$client = new Client();
	$response = $client->request('PUT', $url, [
		'body' => $data,
		'headers' => [
			'Content-Type' => 'application/json',
			'Spiral-Request-Datetime' => $request_datetime,
			'Spiral-Client-Signature' => base64_encode($signature)
		]
	]);
	
	// check server signature
	$pubkeyid = openssl_pkey_get_public("file://./spiralpub.pem");
	$server_sig_payload = "eftit" . $merchantRef . $response->getHeader('Spiral-Request-Datetime')[0];
	$ok = openssl_verify($server_sig_payload, base64_decode($response->getHeader('Spiral-Server-Signature')[0]), $pubkeyid, OPENSSL_ALGO_SHA256);
	if ($ok == 1) {
		$response_body = json_decode($response->getBody());
		echo $response_body->sessionId;
	} else {
		echo "bad error checking signature";
	}
	openssl_free_key($pubkeyid);
	
?>