<?php
/**
 * config.example.php
 *
 * @author Christoph Spiegl <chris@chrissp.com>
 * @package default
 */


// Your user authentication. Add new users via adding an element to the array and generating a new AUTH_KEY
// Second Parameter is a restriction to domains, you can use RegEx to tailor your needs.
// Wildcard: '(.*)'
$API_AUTH = array(
    'YOUR_USERNAME' => array('YOUR_SHA1_ENCRYPTED_PASSWORD', '/(.*)/'),
);

// Define some folders for the process
define('DOC_ROOT', dirname(__FILE__));
define('LOG_DIR', DOC_ROOT . '/');
define('LOG_FILE', DOC_ROOT . '/rssStats.log');

// Default Time Zone
date_default_timezone_set('Europe/Berlin');     // Set this to the timezone you can think in

// Stop editing here!
require_once DOC_ROOT . '/functions.php';
