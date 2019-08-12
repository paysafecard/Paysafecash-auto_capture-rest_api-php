<?php

// PSC Config File

$config = [

    /*
     * Debug
     * Set Debug, will enable more details on errors
     * 0: No debug messages
     * 1: modal debug messages
     * 2: verbose output (requests, curl & responses)
     */

    'debug_level' => 1,

    /*
     * Key:
     * Set key, your psc key
     */

    'psc_key'     => "psc_XXXXXXXXXXXXXXXXX",

	/*
     * Certificate:
     * Put here your certificate name
     */

    'psc_certificate'     => "merchant_webhook_signer_XXXX.pem",

    /*
     * Logging:
     * enable logging of requests and responses to file, default: true
     * might be disbaled in production mode
     */

    'logging'     => true,

    /*
     * Environment
     * set the systems environment.
     * Possible Values are:
     * TEST = Test environment
     * PRODUCTION = Productive Environment
     *
     */

    'environment' => "TEST",

];
