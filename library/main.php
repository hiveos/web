<?php

/**
 * @package HIVE-web
 *
 * @author Selman Eser
 * @copyright 2014 Selman Eser
 * @license BSD 2-clause
 *
 * @version 1.0
 */

error_reporting(E_ALL);

define('CORE', 1);

$start_time = microtime();

ob_start();

require $core['includes_dir'] . '/database.mysqli.php';
require $core['includes_dir'] . '/common.php';

$template = array();
$user = array();
$api_user = array();

clean_request();
db_initiate();
start_session();
load_user();
load_template();

$modules = array('home', 'about', 'api');
if ($user['logged'])
	$modules = array_merge($modules, array('logout', 'profile', 'mybook', 'mynotebook', 'mydrawing', 'output'));
else
	$modules = array_merge($modules, array('login', 'register'));
if ($user['admin'])
	$modules = array_merge($modules, array('user', 'class', 'subject', 'book'));

$core['current_module'] = 'home';
if (!empty($_REQUEST['module']) && in_array($_REQUEST['module'], $modules))
	$core['current_module'] = $_REQUEST['module'];

load_module($core['current_module']);

call_user_func($core['current_module'] . '_main');

ob_exit();