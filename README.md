# Paysafecash payment api PHP class & examples


## minimal basic usage
index.php
```php
// include the payment class
include_once 'PaymentClass.php';

// include config
include_once "config.php";

// create a new payment controller object
$pscpayment = new PaysafecardCashController($config['psc_key'], $config['environment']);

// define needed payment parameters

        // Amount of this payment, i.e. "10.00"
        $amount = $_POST["amount"];
        
        // Currency of this payment , i.e. "EUR"
        $currency = $_POST["currency"];
        
        // the customer ID (merchant client id), it needs to be unique per customer and must not submit customer sensitive data in plain text
        $customer_id = $_POST["customer_id"];
        
        // the customers IP adress
        $customer_ip = $_SERVER['REMOTE_ADDR'];
        
        // the redirect url after a successful payment, the customer will be sent to this url on success
        $success_url = getURL() . "success.php?payment_id={payment_id}";
        
        // the redirect url after a failed / aborted payment, the customer will be redirected to this url on failure
        $failure_url = getURL() . "failure.php?payment_id={payment_id}";
        
        // your scripts notification URL, this url is called to notify your script a payment has been processed
        $webhook_url = getURL() . "webhook.php";

// creating a payment and receive the response
$response = $pscpayment->initiatePayment($amount, $currency, $customer_id, $customer_ip, $success_url, $failure_url, $webhook_url, $customer_data, $_POST["variable_time"], $correlation_id);

// handle the response
if ($response == false) {
            $error = $pscpayment->getError();
            if ($debug == true) {
                echo 'ERROR: ' . $error["number"] . '</strong> ' . $error["message"];
            } else {
                if (($error["number"] == 4003) || ($error["number"] == 4003)) {
                    echo '<strong>ERROR: ' . $error["number"] . '</strong> ' . $error["message"];
                } else {
                    echo 'Transaction could not be initiated due to connection problems. If the problem persists, please contact our support.';
                }
            }
        } else if (isset($response["object"])) {
                if (isset($response["redirect"])) {
                       header("Location: " . $response["redirect"]['auth_url']);
                }
            }
```
##Important

look at the comment on line 237 at index.php
#

config.php

put here your API Key and Certificate.
```php

    /*
     * Key:
     * Set key, your psc key
     */

    'psc_key'     => "psc_XXXXXXXXXXXXXXXX",

	/*
     * Certificate:
     * Put here your certificate name
     */

    'psc_certificate'     => "merchant_webhook_signer_XXXX.pem",

```

## examples and extended usage can be found within the script.
