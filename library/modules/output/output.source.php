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

if (!defined('CORE'))
	exit();

function output_main()
{
	global $core, $user;

	$types = array(
		'book' => 'b',
		'notebook' => 'n',
		'drawing' => 'd',
	);

	if (!empty($_REQUEST['action']) && isset($types[$_REQUEST['action']]))
		$current_action = $_REQUEST['action'];
	else
		exit();

	$type = $types[$current_action];
	$page = !empty($_REQUEST['output']) ? (int) $_REQUEST['output'] : 0;
	$item = !empty($_REQUEST[$current_action]) ? (int) $_REQUEST[$current_action] : 0;

	$file_dir = $core['storage_dir'] . '/' . $user['ssid'] . '/' . $type . $item . '/page' . $page . '.png';

	if (!file_exists($file_dir))
		exit();

	header('Content-Type: image/png');
	readfile($file_dir);

	exit();
}