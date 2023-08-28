<?php
/**
 * URLからFileIDを取得する。
 */

$url = 'https://docs.google.com/spreadsheets/d/1yfMIdt8wgBPrMY3UwiCTsX3EN_2gcLCmPAEy8dfYeLY/edit?usp=sharing';

var_dump(getFileIdByURL($url));


/**
 * GoogleスプレッドシートのURLをパースしてファイルIDを取得する。
 * 本来はSDKで提供されるメソッドか
 *
 * @param $url
 *
 * @return mixed|string
 */
function getFileIdByURL($url) {
	$urls = parse_url($url);
	$paths = explode('/', $urls['path']);
	return $paths[3];
}
