<?php
require_once __DIR__ . '/../vendor/autoload.php';

main();

/**
 * 単独で動かす場合のサンプルコードです。
 */
function main() {
	/**
	 *
	 */
	$shortopts = "u:";  // 値が必須

	$longopts = array(
		"url:",     // 値が必須
	);
	$options  = getopt( $shortopts, $longopts );
	if ( empty( $options['url'] ) && empty( $options['u'] ) ) {
		var_dump( '-u or --url option is required.' );
		exit( 1 );
	}
	$url                 = isset( $options['url'] ) ? $options['url']
		: ( isset( $options['u'] ) ?? $options['u'] );
	$options['template'] = true;
	$options['debug']    = true;
	$hagaki              = new \GSSHagaki\GSSHagaki( $url, $options );
}
