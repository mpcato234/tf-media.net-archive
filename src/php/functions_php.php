<?php

/**
 * normalize all line endings to UNIX format
 */
function normalize($str)
{
    // Normalize line endings
    // Convert all line-endings to UNIX format
    $s = str_replace("\r\n", "\n", $str);
    $s = str_replace("\r", "\n", $s);
    // Don't allow out-of-control blank lines
    $s = preg_replace("/\n{2,}/", "\n\n", $s);
    return $s;
}

/**
 * convert newlines to paragraphs in markdown style
 */
function nl2p($text)
{

    global $purifier_filter, $purifier_wrapper;

    $string = $text;


    $string = preg_replace('/<hr(\s)?\/?>/', "######", $string);

    // $string = $purifier_filter->purify($string);


    // $string = preg_replace('/([-])\\1{1,}/', "######", $string);

    // $string = preg_replace('/(\r\n){2,}/', "<p>", $string);
    // $string = preg_replace('/(\r\n\t){1,}/', "<p>", $string);
    // $string = preg_replace('/(\r\n){1,}/', "<br/>", $string);
    // $string = "<p>{$string}";

    // introduce horizontal separators
    $string = preg_replace('/(\d|[^a-zA-Z!\.,"“”\'])( {0,}\\1 {0,}){2,}/', "<p class=\"divider\" /><p>", $string);


    // replace horizontal dividers




    // $string = json_encode($string);
    $string = $purifier_wrapper->purify($string);

    $string = preg_replace('/<p><\/p>/', "", $string);


    return $string;
}
