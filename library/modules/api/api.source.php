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

	$actions = array('none', 'login', 'list', 'delete');

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
		'admin' => $api_user['admin'] ? 1 : 0,
	);

	$output = array();
	foreach ($data as $key => $value)
		$output[] = $key . '=' . $value;
	$output = implode(',', $output);

	exit($output);
}

function api_list()
{
	global $api_user;

	if (!empty($_REQUEST['type']) && in_array($_REQUEST['type'], array('book', 'notebook', 'drawing')))
		$type = $_REQUEST['type'];
	else
		exit('Sorry, the type provided is not valid!');

	$types = array(
		'book' => array('mybook', 'id_book', array('name')),
		'notebook' => array('mynotebook', 'id_notebook', array('name', 'style', 'color')),
		'drawing' => array('mydrawing', 'id_drawing', array('name')),
	);

	$request = db_query("
		SELECT {$types[$type][1]}, " . implode(', ', $types[$type][2]) . "
		FROM {$types[$type][0]}
		WHERE id_user = $api_user[id]
		ORDER BY name");
	$data = array();
	while ($row = db_fetch_assoc($request))
	{
		$data[$row[$types[$type][1]]]['id'] = $row[$types[$type][1]];

		foreach ($types[$type][2] as $field)
			$data[$row[$types[$type][1]]][$field] = $row[$field];

		$data[$row[$types[$type][1]]]['cover'] = build_url(array(
			'module' => 'api',
			'unique' => $api_user['unique'],
			'action' => 'output',
			'type' => $type,
			'page' => 1,
			'item' => $row[$types[$type][1]],
		), false);
	}
	db_free_result($request);

	$output = array();
	foreach ($data as $set)
	{
		$item = array();
		foreach ($set as $key => $value)
			$item[] = $key . '=' . $value;
		$output[] = implode(',', $item);
	}
	$output = implode(";\n", $output);

	exit($output);
}

function api_delete()
{
	global $api_user;

	if (!empty($_REQUEST['type']) && in_array($_REQUEST['type'], array('book', 'notebook', 'drawing')))
		$type = $_REQUEST['type'];
	else
		exit('Sorry, the type provided is not valid!');

	if (!empty($_POST['item']) && (int) $_POST['item'] > 0)
		$id_item = (int) $_POST['item'];
	else
		exit('Sorry, the item provided is not valid!');

	$types = array(
		'book' => array('mybook', 'id_book'),
		'notebook' => array('mynotebook', 'id_notebook'),
		'drawing' => array('mydrawing', 'id_drawing'),
	);

	$request = db_query("
		SELECT {$types[$type][1]}
		FROM {$types[$type][0]}
		WHERE id_user = $api_user[id]
			AND {$types[$type][1]} = $id_item
		LIMIT 1");
	list ($id_item) = db_fetch_row($request);
	db_free_result($request);

	if (empty($id_item))
		exit('Sorry, the item provided is not valid!');

	db_query("
		DELETE FROM {$types[$type][0]}
		WHERE {$types[$type][1]} = $id_item
		LIMIT 1");

	exit('return=1');
}