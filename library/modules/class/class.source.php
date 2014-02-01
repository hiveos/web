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

function class_main()
{
	global $core;

	$actions = array('list', 'edit', 'delete');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function class_list()
{
	global $core, $template;

	$request = db_query("
		SELECT id_class, name
		FROM class
		ORDER BY name");
	$template['classes'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['classes'][] = array(
			'id' => $row['id_class'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Class List';
	$core['current_template'] = 'class_list';
}

function class_edit()
{
	global $core, $template;

	$id_class = !empty($_REQUEST['class']) ? (int) $_REQUEST['class'] : 0;
	$is_new = empty($id_class);

	if (!empty($_POST['save']))
	{
		check_session('class');

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
			fatal_error('Class name field cannot be empty!');

		if ($is_new)
		{
			$insert = array();
			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO class
					(" . implode(', ', array_keys($insert)) . ")
				VALUES
					(" . implode(', ', $insert) . ")");
		}
		else
		{
			$update = array();
			foreach ($values as $field => $value)
				$update[] = $field . " = '" . $value . "'";

			db_query("
				UPDATE class
				SET " . implode(', ', $update) . "
				WHERE id_class = $id_class
				LIMIT 1");
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('class'));

	if ($is_new)
	{
		$template['class'] = array(
			'is_new' => true,
			'id' => 0,
			'name' => '',
		);
	}
	else
	{
		$request = db_query("
			SELECT id_class, name
			FROM class
			WHERE id_class = $id_class
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['class'] = array(
				'is_new' => false,
				'id' => $row['id_class'],
				'name' => $row['name'],
			);
		}
		db_free_result($request);

		if (!isset($template['class']))
			fatal_error('The class requested does not exist!');
	}

	$template['page_title'] = (!$is_new ? 'Edit' : 'Add') . ' Class';
	$core['current_template'] = 'class_edit';
}

function class_delete()
{
	global $core, $template;

	$id_class = !empty($_REQUEST['class']) ? (int) $_REQUEST['class'] : 0;

	$request = db_query("
		SELECT id_class, name
		FROM class
		WHERE id_class = $id_class
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['class'] = array(
			'id' => $row['id_class'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['class']))
		fatal_error('The class requested does not exist!');

	if (!empty($_POST['delete']))
	{
		check_session('class');

		db_query("
			DELETE FROM class
			WHERE id_class = $id_class
			LIMIT 1");

		db_query("
			UPDATE user
			SET id_class = 0
			WHERE id_class = $id_class");
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('class'));

	$template['page_title'] = 'Delete Class';
	$core['current_template'] = 'class_delete';
}