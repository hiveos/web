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

function mydrawing_main()
{
	global $core;

	$actions = array('list', 'view', 'edit', 'delete');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function mydrawing_list()
{
	global $core, $template, $user;

	$request = db_query("
		SELECT id_drawing, name
		FROM mydrawing
		WHERE id_user = $user[id]
		ORDER BY name");
	$template['drawings'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['drawings'][] = array(
			'id' => $row['id_drawing'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Drawing List';
	$core['current_template'] = 'mydrawing_list';
}

function mydrawing_view()
{
	global $core, $template, $user;

	$id_drawing = !empty($_REQUEST['mydrawing']) ? (int) $_REQUEST['mydrawing'] : 0;
	$drawing_dir = $core['storage_dir'] . '/' . $user['ssid'] . '/d' . $id_drawing;

	$request = db_query("
		SELECT id_drawing, name
		FROM mydrawing
		WHERE id_drawing = $id_drawing
			AND id_user = $user[id]
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['drawing'] = array(
			'id' => $row['id_drawing'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['drawing']) || !file_exists($drawing_dir))
		fatal_error('The drawing requested does not exist!');

	$page = 0;
	$temp = list_dir($drawing_dir);

	foreach ($temp as $file)
	{
		if (preg_match('~^page(\d+).png$~', $file, $match))
		{
			$page = $match[1];

			break;
		}
	}

	if ($page < 1)
		fatal_error('There are no pages in the requested drawing!');

	$template['drawing']['page'] = $page;

	$template['page_title'] = 'View Drawing';
	$core['current_template'] = 'mydrawing_view';
}

function mydrawing_edit()
{
	global $core, $template, $user;

	$id_drawing = !empty($_REQUEST['mydrawing']) ? (int) $_REQUEST['mydrawing'] : 0;
	$is_new = empty($id_drawing);

	if (!empty($_POST['save']))
	{
		check_session('mydrawing');

		$values = array();
		$fields = array(
			'name' => 'string',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'string')
				$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';
		}

		if ($values['name'] === '')
			fatal_error('Drawing name field cannot be empty!');

		if ($is_new)
		{
			$insert = array(
				'id_user' => $user['id'],
			);
			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO mydrawing
					(" . implode(', ', array_keys($insert)) . ")
				VALUES
					(" . implode(', ', $insert) . ")");

			$id_drawing = db_insert_id();

			mkdir($core['storage_dir'] . '/' . $user['ssid'] . '/d' . $id_drawing);
		}
		else
		{
			$update = array();
			foreach ($values as $field => $value)
				$update[] = $field . " = '" . $value . "'";

			db_query("
				UPDATE mydrawing
				SET " . implode(', ', $update) . "
				WHERE id_drawing = $id_drawing
				LIMIT 1");
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('mydrawing'));

	if ($is_new)
	{
		$template['drawing'] = array(
			'is_new' => true,
			'id' => 0,
			'name' => '',
		);
	}
	else
	{
		$request = db_query("
			SELECT id_drawing, name
			FROM mydrawing
			WHERE id_drawing = $id_drawing
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['drawing'] = array(
				'is_new' => false,
				'id' => $row['id_drawing'],
				'name' => $row['name'],
			);
		}
		db_free_result($request);

		if (!isset($template['drawing']))
			fatal_error('The drawing requested does not exist!');
	}

	$template['page_title'] = (!$is_new ? 'Edit' : 'Add') . ' Drawing';
	$core['current_template'] = 'mydrawing_edit';
}

function mydrawing_delete()
{
	global $core, $template, $user;

	$id_drawing = !empty($_REQUEST['mydrawing']) ? (int) $_REQUEST['mydrawing'] : 0;

	$request = db_query("
		SELECT id_drawing, name
		FROM mydrawing
		WHERE id_drawing = $id_drawing
			AND id_user = $user[id]
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['drawing'] = array(
			'id' => $row['id_drawing'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['drawing']))
		fatal_error('The drawing requested does not exist!');

	if (!empty($_POST['delete']))
	{
		check_session('mydrawing');

		db_query("
			DELETE FROM mydrawing
			WHERE id_drawing = $id_drawing
			LIMIT 1");
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('mydrawing'));

	$template['page_title'] = 'Delete Drawing';
	$core['current_template'] = 'mydrawing_delete';
}