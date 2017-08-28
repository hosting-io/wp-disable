<?php
/**
 * Script to update local-ga.js-file
 * Credits go to: Matthew Horne | http://diywpblog.com/leverage-browser-cache-optimize-google-analytics/
 * I adjusted this script to work with wp-cron. It will still run in crontab, though.
 * To run the script in crontab, remove the code on lines 8 and 9.
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Remote file to download.
$remote_file = 'https://www.google-analytics.com/analytics.js';
$local_file = dirname( dirname( __FILE__ ) ) . '/cache/local-ga.js';

// Connection time out.
$conn_timeout = 10;
$url = wp_parse_url( $remote_file );
$host = $url['host'];
$path = isset($url['path']) ? $url['path'] : '/';

if (isset($url['query'])) {
	$path .= '?' . $url['query'];
}

$port = isset( $url['port'] ) ? $url['port'] : '80';
$fp = @fsockopen( $host, '80', $errno, $errstr, $conn_timeout );

if ( ! $fp ) {
	// On connection failure return the cached file (if exists).
	if ( file_exists( $local_file ) ) {
		readfile( $local_file );
	}
} else {
	// Send the header information
	$header = "GET $path HTTP/1.0\r\n";
	$header .= "Host: $host\r\n";
	$header .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6\r\n";
	$header .= "Accept: */*\r\n";
	$header .= "Accept-Language: en-us,en;q=0.5\r\n";
	$header .= "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n";
	$header .= "Keep-Alive: 300\r\n";
	$header .= "Connection: keep-alive\r\n";
	$header .= "Referer: http://$host\r\n\r\n";
	fputs($fp, $header);
	$response = '';

	// Get the response from the remote server.
	while ( $line = fread( $fp, 4096 ) ) {
		$response .= $line;
	}

	// Close the connection.
	fclose( $fp );

	// Remove the headers.
	$pos = strpos( $response, "\r\n\r\n" );
	$response = substr( $response, $pos + 4 );

	// Return the processed response.
	echo $response;

	// Save the response to the local file.
	if ( ! file_exists( $local_file ) ) {
		// Try to create the file, if doesn't exist.
		fopen( $local_file, 'w' );
	}

	if ( is_writable( $local_file ) ) {
		$fp = fopen( $local_file, 'w' );
		if ( $fp ) {
			fwrite( $fp, $response );
			fclose( $fp );
		}
	}
}// End if().
