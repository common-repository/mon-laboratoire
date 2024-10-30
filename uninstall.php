<?php
/**
 *
 * This file runs when the plugin in uninstalled (deleted).
 * This will not run when the plugin is deactivated.
 * Ideally you will add all your clean-up scripts here
 * that will clean-up unused meta, options, etc. in the database.
 *
 */

// Do something here if plugin is being uninstalled.
// If uninstall is not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit ();

use MonLabo\Lib\Db;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

require_once ( __DIR__ . '/autoload.php' );

$MonLaboDB = new Db();
$MonLaboDB->delete_tables(); // Effacer les tables
foreach ( array_keys( \MonLabo\Lib\App::get_options_DEFAULT() ) as $group ) {
    unregister_setting( 'pluginSettings_' . $group , $group );
}
$MonLaboDB->delete_options(); // Effacer les options
