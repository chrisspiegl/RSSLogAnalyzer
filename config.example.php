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


// Analyzer Config
$email = false;
$email_from = '%ADMIN_EMAIL%';
$email_full_stats = '%RECEVING_THE_FULL_STATS_EMAIL%';
$email_stats_per_domain = array(
    '%DOMAIN_URL%' => '%EMAIL_SHOULD_RECEIVE_STATS_OF_THIS_URL%',
);

$email_smtp['host'] = 'smtp.gmail.com';
$email_smtp['port'] = 465;
$email_smtp['auth'] = "ssl";
$email_smtp['username'] = '%USERNAME_MAIL%';
$email_smtp['password'] = '%PASSWORD_MAIL%';

$feed_uris = array(
    '/feed',
    '/feed/',
    '/feed.xml',
    '/rss',
    '/rss/',
    '/rss.xml',
    '/atom.xml',
    '/atom',
    '/atom/',
);

$minimum_subscribers_to_display = 2;
$include_google_reader = false;

// Default Time Zone
date_default_timezone_set('Europe/Berlin');     // Set this to the timezone you can think in

// Stop editing here!
require_once DOC_ROOT . '/functions.php';
