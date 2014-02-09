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

function api_main()
{
	global $core, $api_user;

	if (!empty($_REQUEST['unique']) && preg_match('~^[a-f0-9]{20}$~', $_REQUEST['unique']))
	{
		$unique = "'" . $_REQUEST['unique'] . "'";

		$request = db_query("
			SELECT
				u.id_user, u.id_unique, u.ssid, u.name,
				u.id_class, c.name AS class_name, u.admin
			FROM user AS u
				LEFT JOIN class AS c ON (c.id_class = u.id_class)
			WHERE u.id_unique = $unique
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$api_user = array(
				'id' => (int) $row['id_user'],
				'unique' => $row['id_unique'],
				'ssid' => $row['ssid'],
				'name' => $row['name'],
				'id_class' => $row['id_class'],
				'class_name' => $row['class_name'],
				'admin' => !empty($row['admin']),
			);
		}
		db_free_result($request);
	}

	if (empty($api_user))
		exit('Sorry, the ID provided is not valid!');

	$actions = array('none', 'login');

	$core['current_action'] = 'none';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function api_none()
{
	exit('Why might you be here?!');
}

function api_login()
{
	global $api_user;

	$data = array(
		'photo' => get_photo_src($api_user['unique']),
		'name' => $api_user['name'],
		'class' => $api_user['class_name'],
		'unique' => $api_user['unique'],
	);

	$output = array();
	foreach ($data as $key => $value)
		$output[] = $key . '=' . $value;
	$output = implode('&', $output);

	exit($output);
}