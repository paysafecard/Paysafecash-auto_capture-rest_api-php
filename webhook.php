<?php

/*
 * this script handles the notification requests made by the paysafecard api
 * handling the requests after a payment was successful / failed
 */

error_reporting(E_ALL);
include_once 'PaymentClass.php';
include_once "PaysafeLogger.php";

if (!function_exists('apache_request_headers')) {
    function apache_request_headers()
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
            }
        }
        return $headers;
    }
}


/**
 *
 * Check config.php for configuration
 *
 */

include_once "config.php";

// Init Paysafe Controller with Key and Environment

$pscpayment = new PaysafecardCashController($config['psc_key'], $config['environment']);
$logger = new PaysafeLogger();

// Get Data from the Webhook Request and fill it into the variables.

$signature = str_replace('"', '', str_replace('signature="', '', explode(",", apache_request_headers()["Authorization"])[2]));
$payment_str = file_get_contents("php://input");
$json_obj = json_decode($payment_str);


$pubkey = openssl_pkey_get_public(file_get_contents(getcwd() . "/test/webhook_signer_MAN4325607404_1.pem"));


if ($config['logging'] == true) {
    $logger->log("WEBHOOK SIGNATUR KEY: " . $signature, "", "");

    $logger->log("WEBHOOK SIGNATUR Body: " . $payment_str, "", "");
}


$signatur_check = openssl_verify($payment_str, base64_decode($signature), $pubkey, OPENSSL_ALGO_SHA256);


openssl_free_key($pubkey);

if ($signatur_check == 1) {
    $logger->log("WEBHOOK SIGNATUR: Signatur is correct", "", "");
} elseif ($signatur_check == 0) {
    $logger->log("WEBHOOK SIGNATUR: Signatur is not correct", "", "");
} else {
    $logger->log("WEBHOOK SIGNATUR: ERROR: " . openssl_error_string(), "", "");
}


// checking for actual action
if (isset($json_obj->data->mtid)) {

    $response = $pscpayment->retrievePayment($json_obj->data->mtid);

    $logger->log("PAYMENT MTID: " . $json_obj->data->mtid, "", "");

    if ($config['logging'] == true) {
        $logger->log("WEBHOOK: " . $pscpayment->getRequest(), $pscpayment->getCurl(), $pscpayment->getResponse());
    }

}
