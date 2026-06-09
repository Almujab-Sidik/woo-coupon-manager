<?php
// phpcs:ignoreFile


namespace YahnisElsts\PluginUpdateChecker\v5;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( !class_exists(PucFactory::class, false) ):

	class PucFactory extends \YahnisElsts\PluginUpdateChecker\v5p4\PucFactory {
	}

endif;
