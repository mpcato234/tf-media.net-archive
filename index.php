<?php
error_reporting(E_ALL);
require_once '3rdparty/htmlpurifier/library/HTMLPurifier.auto.php';
include_once 'config.php';
include_once 'src/php/functions_php.php';


/*
 * include php classes
 *
 * can not be automated, because order is important here
 */
include_once 'src/php/CTFManager.php';
include_once 'src/php/CNodeCYOT.php';
include_once 'src/php/CNodeStory.php';




/*
 * Setup a htmlpurifier as filter for uunwatned html in user entries
 *
 * Is a replacement for the original concept of drupal filters
 */
$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.TidyLevel', 'heavy'); // all changes, minus...
$config->set('HTML.AllowedElements', 'a, em, strong, cite, code, ul, ol, li, dl, dt, dd, br, p');
$config->set('AutoFormat.AutoParagraph', true);
$purifier_filter = new HTMLPurifier($config);


/*
 * Setup a htmlpurifier object for use of repairing broken html
 *
 * This is a replacement for drupal's paragraph completion filter
 */
$config = HTMLPurifier_Config::createDefault();
$config->set('AutoFormat.RemoveEmpty', true); // remove empty tag pairs
$config->set('HTML.TidyLevel', 'light'); // all changes, minus...
$config->set('AutoFormat.AutoParagraph', true);
$config->set('AutoFormat.RemoveEmpty.Predicate', array(
    'colgroup' => array(),
    'th' => array(),
    'td' => array(),
    'iframe' => array(0 => 'src',),
    'p' => array(0 => 'class',),
));
// $config->set('HTML.AllowedElements', 'a, em, strong, cite, code, ul, ol, li, dl, dt, dd, br, p');
$purifier_wrapper = new HTMLPurifier($config);

// setup the object managing the database connection and row retrieval
$manager = new CTFManager($cfg);



// include the html webapp skeleton
include 'webapp.html';
