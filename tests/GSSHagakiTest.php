<?php
/**
 * Just do it.
 */

require_once(__DIR__.'/../vendor/autoload.php');

use GSSHagaki\GSSHagaki;

$url    = 'https://docs.google.com/spreadsheets/d/1yfMIdt8wgBPrMY3UwiCTsX3EN_2gcLCmPAEy8dfYeLY/export\?usp\=sharing\&format\=csv';
$hagaki = new GSSHagaki($url);