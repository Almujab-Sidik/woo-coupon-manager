<?php
// phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists('Parsedown', false) ) {
	require __DIR__ . '/ParsedownModern.php';
}
