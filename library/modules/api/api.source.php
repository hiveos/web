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

	$actions = array('none', 'login', 'list', 'add', 'edit', 'delete', 'output');

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

function api_add()
{
	global $core, $api_user;

	if (!empty($_REQUEST['type']) && in_array($_REQUEST['type'], array('book', 'notebook', 'drawing')))
		$type = $_REQUEST['type'];
	else
		exit('Sorry, the type provided is not valid!');

	if ($type == 'book')
	{
		if (!empty($_POST['id_book']) && (int) $_POST['id_book'] > 0)
			$id_parent = (int) $_POST['id_book'];
		else
			exit('Sorry, the book id provided is not valid!');

		$request = db_query("
			SELECT id_book, name
			FROM book
			WHERE id_book = $id_parent
				AND FIND_IN_SET($api_user[id_class], class)
			LIMIT 1");
		list ($id_parent, $name) = db_fetch_row($request);
		db_free_result($request);

		if (empty($id_parent))
			exit('Sorry, the item provided is not valid!');

		$insert = array(
			'name' => "'" . $name . "'",
			'id_user' => $api_user['id'],
		);

		db_query("
			INSERT INTO mybook
				(" . implode(', ', array_keys($insert)) . ")
			VALUES
				(" . implode(', ', $insert) . ")");

		$id_book = db_insert_id();

		$book_dir = $core['storage_dir'] . '/' . $api_user['ssid'] . '/b' . $id_book;
		$parent_dir = $core['storage_dir'] . '/shared/' . $id_parent;

		mkdir($book_dir);

		if (($handle = opendir($parent_dir)))
		{
			while ($file = readdir($handle))
			{
				if (in_array($file, array('.', '..')))
					continue;

				if (is_file($parent_dir . '/' . $file))
					copy($parent_dir . '/' . $file, $book_dir . '/' . $file);
			}

			closedir($handle);
		}
	}
	elseif ($type == 'notebook')
	{
		$values = array();
		$fields = array('name', 'style', 'color');

		foreach ($fields as $field)
			$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';

		$styles = array('Lines', 'Grid', 'Plain');
		$colors = array('White', 'Gray', 'Blue', 'Dark Blue', 'Purple', 'Dark Purple', 'Green', 'Dark Green', 'Orange', 'Dark Orange', 'Red', 'Dark Red');

		if ($values['name'] === '')
			exit('Sorry, the name field provided is not valid!');
		elseif (!in_array($values['style'], $styles))
			exit('Sorry, the style field provided is not valid!');
		elseif (!in_array($values['color'], $colors))
			exit('Sorry, the color field provided is not valid!');

		$insert = array(
			'id_user' => $api_user['id'],
		);
		foreach ($values as $field => $value)
			$insert[$field] = "'" . $value . "'";

		db_query("
			INSERT INTO mynotebook
				(" . implode(', ', array_keys($insert)) . ")
			VALUES
				(" . implode(', ', $insert) . ")");

		$id_notebook = db_insert_id();

		mkdir($core['storage_dir'] . '/' . $api_user['ssid'] . '/n' . $id_notebook);
	}
	elseif ($type == 'drawing')
	{
		$values = array();
		$fields = array('name');

		foreach ($fields as $field)
			$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';

		if ($values['name'] === '')
			exit('Sorry, the name field provided is not valid!');

		$insert = array(
			'id_user' => $api_user['id'],
		);
		foreach ($values as $field => $value)
			$insert[$field] = "'" . $value . "'";

		db_query("
			INSERT INTO mydrawing
				(" . implode(', ', array_keys($insert)) . ")
			VALUES
				(" . implode(', ', $insert) . ")");

		$id_drawing = db_insert_id();

		mkdir($core['storage_dir'] . '/' . $api_user['ssid'] . '/d' . $id_drawing);
	}

	exit('return=1');
}

function api_edit()
{
	global $api_user;

	if (!empty($_REQUEST['type']) && in_array($_REQUEST['type'], array('notebook', 'drawing')))
		$type = $_REQUEST['type'];
	else
		exit('Sorry, the type provided is not valid!');

	if (!empty($_POST['item']) && (int) $_POST['item'] > 0)
		$id_item = (int) $_POST['item'];
	else
		exit('Sorry, the item provided is not valid!');

	$types = array(
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

	$values = array();
	$fields = array('name');

	if ($type == 'notebook')
	{
		$fields[] = 'style';
		$fields[] = 'color';
	}

	foreach ($fields as $field)
	{
		$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';

		if ($values[$field] === '')
			exit('Sorry, the ' . $field .' field provided is not valid!');
	}

	if ($type == 'notebook')
	{
		$styles = array('Lines', 'Grid', 'Plain');
		$colors = array('White', 'Gray', 'Blue', 'Dark Blue', 'Purple', 'Dark Purple', 'Green', 'Dark Green', 'Orange', 'Dark Orange', 'Red', 'Dark Red');

		if (!in_array($values['style'], $styles))
			exit('Sorry, the style field provided is not valid!');
		elseif (!in_array($values['color'], $colors))
			exit('Sorry, the color field provided is not valid!');
	}

	$update = array();
	foreach ($values as $field => $value)
		$update[] = $field . " = '" . $value . "'";

	db_query("
		UPDATE {$types[$type][0]}
		SET " . implode(', ', $update) . "
		WHERE {$types[$type][1]} = $id_item
		LIMIT 1");

	exit('return=1');
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

function api_output()
{
	global $core, $api_user;

	if (!empty($_REQUEST['type']) && in_array($_REQUEST['type'], array('book', 'notebook', 'drawing')))
		$type = $_REQUEST['type'];
	else
		exit('Sorry, the type provided is not valid!');

	if (!empty($_POST['item']) && (int) $_POST['item'] > 0)
		$item = (int) $_POST['item'];
	else
		exit('Sorry, the item provided is not valid!');

	if (!empty($_POST['page']) && (int) $_POST['page'] > 0)
		$page = (int) $_POST['page'];
	else
		exit('Sorry, the page provided is not valid!');

	$file_dir = $core['storage_dir'] . '/' . $api_user['ssid'] . '/' . $type[0] . $item . '/page' . $page . '.png';

	if (!file_exists($file_dir))
		exit('Sorry, the page provided is not valid!');

	header('Content-Type: image/png');
	readfile($file_dir);

	exit();
}