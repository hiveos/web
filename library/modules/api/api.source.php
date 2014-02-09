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

	$actions = array('none', 'login', 'books', 'notebooks', 'drawings');

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
		'ssid' => $api_user['ssid'],
		'unique' => $api_user['unique'],
	);

	$output = array();
	foreach ($data as $key => $value)
		$output[] = $key . '=' . $value;
	$output = implode(',', $output);

	exit($output);
}

function api_books()
{
	global $api_user;

	$request = db_query("
		SELECT id_book, name
		FROM mybook
		WHERE id_user = $api_user[id]
		ORDER BY name");
	$books = array();
	while ($row = db_fetch_assoc($request))
	{
		$books[] = array(
			'id' => $row['id_book'],
			'name' => $row['name'],
			'cover' => build_url(array(
				'module' => 'api',
				'unique' => $api_user['unique'],
				'action' => 'output',
				'type' => 'book',
				'page' => 1,
				'book' => $row['id_book'],
			), false),
		);
	}
	db_free_result($request);

	$output = array();
	foreach ($books as $data)
	{
		$item = array();
		foreach ($data as $key => $value)
			$item[] = $key . '=' . $value;
		$output[] = implode(',', $item);
	}
	$output = implode(";\n", $output);

	exit($output);
}

function api_notebooks()
{
	global $api_user;

	$request = db_query("
		SELECT id_notebook, name, style, color
		FROM mynotebook
		WHERE id_user = $api_user[id]
		ORDER BY name");
	$notebooks = array();
	while ($row = db_fetch_assoc($request))
	{
		$notebooks[] = array(
			'id' => $row['id_notebook'],
			'name' => $row['name'],
			'style' => $row['style'],
			'color' => $row['color'],
			'cover' => build_url(array(
				'module' => 'api',
				'unique' => $api_user['unique'],
				'action' => 'output',
				'type' => 'notebook',
				'page' => 1,
				'notebook' => $row['id_notebook'],
			), false),
		);
	}
	db_free_result($request);

	$output = array();
	foreach ($notebooks as $data)
	{
		$item = array();
		foreach ($data as $key => $value)
			$item[] = $key . '=' . $value;
		$output[] = implode(',', $item);
	}
	$output = implode(";\n", $output);

	exit($output);
}

function api_drawings()
{
	global $api_user;

	$request = db_query("
		SELECT id_drawing, name
		FROM mydrawing
		WHERE id_user = $api_user[id]
		ORDER BY name");
	$drawings = array();
	while ($row = db_fetch_assoc($request))
	{
		$drawings[] = array(
			'id' => $row['id_drawing'],
			'name' => $row['name'],
			'cover' => build_url(array(
				'module' => 'api',
				'unique' => $api_user['unique'],
				'action' => 'output',
				'type' => 'drawing',
				'page' => 1,
				'drawing' => $row['id_drawing'],
			), false),
		);
	}
	db_free_result($request);

	$output = array();
	foreach ($drawings as $data)
	{
		$item = array();
		foreach ($data as $key => $value)
			$item[] = $key . '=' . $value;
		$output[] = implode(',', $item);
	}
	$output = implode(";\n", $output);

	exit($output);
}