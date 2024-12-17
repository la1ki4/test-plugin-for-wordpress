<?php
/*
Plugin Name: Test Plugin
Description: Test
Version: 1.0
Author: Papas Alexandros
Author URI: https://www.instagram.com/alexandrospapas1/
License: GPL2
*/


/*
Detailed Requirements
1. Plugin Initialization and Setup
Objective: Create a basic WordPress plugin.
Details:
Set up a standard WordPress plugin structure with a main PHP file and a README.
The plugin should be able to activate and deactivate cleanly, without errors.
*/
function main_activate() {
    error_log('My test plugin activated!');
}
register_activation_hook(__FILE__, 'main_activate');

function main_deactivate() {
    error_log('My test plugin deactivated!');
}
register_deactivation_hook(__FILE__, 'main_deactivate');

require_once plugin_dir_path(__FILE__) . 'task2.php';
require_once plugin_dir_path(__FILE__) . 'task3.php';
require_once plugin_dir_path(__FILE__) . 'task4.php';