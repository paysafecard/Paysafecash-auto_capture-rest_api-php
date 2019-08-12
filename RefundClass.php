<?php
/**
 * @author
 *
 */
class PaysafecardCashRefundController
{
    private $response;
    private $request = array();
    private $curl;
    private $key         = "";
    private $url         = "";
    private $environment = 'TEST';

    public function __construct($key = "", $environment = "TEST")
    {
        $this->key         = $key;
        $this->environment = $environment;
        $this->setEnvironment();
    }

    /**
     * send curl request
     * @param assoc array $curlparam
     * @param httpmethod $method
     * @return null
     */
    private function doRequest($curlparam, $method, $headers = array())
    {
        $ch = curl_init();

        $header = array(
            "Authorization: Basic " . base64_encode($this->key),
            "Content-Type: application/json",
        );

        $header = array_merge($header, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlparam));
            curl_setopt($ch, CURLOPT_POST, true);
        } elseif ($method == 'GET') {
            curl_setopt($ch, CURLOPT_URL, $this->url . $curlparam);
            curl_setopt($ch, CURLOPT_POST, false);
        }
        curl_setopt($ch, CURLOPT_PORT, 443);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (is_array($curlparam)) {
            $curlparam['request_url'] = $this->url;

        } else {
            $requestURL               = $this->url . $curlparam;
            $curlparam                = array();
            $curlparam['request_url'] = $requestURL;
        }
        $this->request  = $curlparam;
        $this->response = json_decode(curl_exec($ch), true);

        $this->curl["info"]        = curl_getinfo($ch);
        $this->curl["error_nr"]    = curl_errno($ch);
        $this->curl["error_text"]  = curl_error($ch);
        $this->curl["http_status"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->setEnvironment();
    }

    /**
     * check request status
     * @return bool
     */
    public function requestIsOk()
    {
        if (($this->curl["error_nr"] == 0) && ($this->curl["http_status"] < 300)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get the request
     * @return mixed request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * get curl
     * @return mixed curl
     */
    public function getCurl()
    {
        return $this->curl;
    }

    /**
     * get details of a payment
     * @param string $payment
     * @return response
     */

    public function getPaymentDetail($payment = "")
    {
        $this->doRequest($payment, "GET");
        return $this->response;
    }

    /**
     * refund a payment directly
     * @param string $payment_id
     * @param double $amount
     * @param string|currencycode $currency
     * @param string $merchantclientid
     * @param string $customer_mail
     * @param string $correlation_id
     * @return reponse|false
     */
    public function captureRefund($payment_id, $amount, $currency, $merchantclientid, $customer_mail, $correlation_id = "", $submerchant_id = "")
    {
        $amount    = str_replace(',', '.', $amount);
        $jsonarray = array(
            "amount"   => $amount,
            "currency" => $currency,
            "type"     => "PAYSAFECARD",
            "customer" => array(
                "id"            => $merchantclientid,
                "email"         => $customer_mail,
            ),
            "capture" => "true"
        );

        if ($submerchant_id != "") {
            array_push($jsonarray, [
                "submerchant_id" => $submerchant_id,
            ]);
        }

        if ($correlation_id != "") {
            $headers = ["Correlation-ID: " . $correlation_id];
        } else {
            $headers = [];
        }
        $this->url = $this->url . $payment_id . "/refunds";
        $this->doRequest($jsonarray, "POST", $headers);
        return $this->response;
    }

    /**
     * get the response
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * set environment
     * @return mixed
     */
    private function setEnvironment()
    {
        if ($this->environment == "TEST") {
            $this->url = "https://apitest.paysafecard.com/v1/payments/";
        } else if ($this->environment == "PRODUCTION") {
            $this->url = "https://api.paysafecard.com/v1/payments/";
        } else {
            echo "Environment not supported";
            return false;
        }
    }

    /**
     * get error
     * @return response
     */
    public function getError()
    {
        if (!isset($this->response["number"])) {
            switch ($this->curl["info"]['http_code']) {
                case 400:
                    $this->response["number"]  = "HTTP:400";
                    $this->response["message"] = 'Logical error. Please check logs.';
                    break;
                case 403:
                    $this->response["number"]  = "HTTP:403";
                    $this->response["message"] = 'IP not whitelisted! Your IP:' . $_SERVER["SERVER_ADDR"];
                    break;
                case 500:
                    $this->response["number"]  = "HTTP:500";
                    $this->response["message"] = 'Server error. Please check logs.';
                    break;
            }
        }
        switch ($this->response["number"]) {
            case 3100:
                $this->response["message"] = 'Product not available.';
                break;
            case 3103:
                $this->response["message"] = 'Duplicate order request.';
                break;
            case 3106:
                $this->response["message"] = 'Invalid facevalue format.';
                break;
            case 3150:
                $this->response["message"] = 'Missing paramenter.';
                break;
            case 3151:
                $this->response["message"] = 'Invalid currency.';
                break;
            case 3161:
                $this->response["message"] = 'Merchant not allowed to perform this Action.';
                break;
            case 3162:
                $this->response["message"] = 'No customer account found by provided credentials.';
                break;
            case 3163:
                $this->response["message"] = 'Invalid paramater.';
                break;
            case 3164:
                $this->response["message"] = 'Transaction already exists.';
                break;
            case 3165:
                $this->response["message"] = 'Invalid amount.';
                break;
            case 3167:
                $this->response["message"] = 'Customer limit exceeded.';
                break;
            case 3168:
                $this->response["message"] = 'Feature not activated in this country for this kyc Level.';
                break;
            case 3169:
                $this->response["message"] = 'Payout id collides with existing disposition id';
                break;
            case 3170:
                $this->response["message"] = 'Top-up limit exceeded.';
                break;
            case 3171:
                $this->response["message"] = 'Payout amount is below minimum payout amount of the merchant.';
                break;
            case 3179:
                $this->response["message"] = 'Merchant refund exceeds original transaction.';
                break;
            case 3180:
                $this->response["message"] = 'Original Transaction of Merchant Refund is in invalid state.';
                break;
            case 3181:
                $this->response["message"] = 'Merchant Client Id not matching with original Payment.';
                break;
            case 3182:
                $this->response["message"] = 'merchant client Id missing.';
                break;
            case 3184:
                $this->response["message"] = 'No original Transaction found.';
                break;
            case 3185:
                $this->response["message"] = 'my paysafecard account not found on original transaction and no additional credentials provided.';
                break;
            case 3193:
                $this->response["message"] = 'Customer not active.';
                break;
            case 3194:
                $this->response["message"] = 'Customer yearly payout limit exceeded.';
                break;
            case 3195:
                $this->response["message"] = '	Customer details from request don\'t match with database.';
                break;
            case 3198:
                $this->response["message"] = 'There is already the maximum number of pay-out merchant clients assigned to this account.';
                break;
            case 3199:
                $this->response["message"] = 'Payout blocked due to security reasons.';
                break;
        }
        return $this->response;
    }

    /**
     * get refunded Amount
     * @return double
     */

    public function getRefundedAmount()
    {
        if (isset($this->response["refunds"])) {
            $refunds  = $this->response["refunds"];
            $refunded = 0;
            foreach ($refunds as $refund) {
                if ($refund["status"] == "SUCCESS") {
                    $refunded = $refunded + $refund["amount"];
                }
            }
            return $refunded;
        } else {
            return 0;
        }
    }
}
