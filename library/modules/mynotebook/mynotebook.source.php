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

function mynotebook_main()
{
	global $core;

	$actions = array('list', 'edit', 'delete');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function mynotebook_list()
{
	global $core, $template, $user;

	$request = db_query("
		SELECT id_notebook, name
		FROM mynotebook
		WHERE id_user = $user[id]
		ORDER BY name");
	$template['notebooks'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['notebooks'][] = array(
			'id' => $row['id_notebook'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Notebook List';
	$core['current_template'] = 'mynotebook_list';
}

function mynotebook_edit()
{
	global $core, $template, $user;

	$id_notebook = !empty($_REQUEST['mynotebook']) ? (int) $_REQUEST['mynotebook'] : 0;
	$is_new = empty($id_notebook);

	if (!empty($_POST['save']))
	{
		check_session('mynotebook');

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
			fatal_error('Notebook name field cannot be empty!');

		if ($is_new)
		{
			$insert = array(
				'id_user' => $user['id'],
			);
			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO mynotebook
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
				UPDATE mynotebook
				SET " . implode(', ', $update) . "
				WHERE id_notebook = $id_notebook
				LIMIT 1");
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('mynotebook'));

	if ($is_new)
	{
		$template['notebook'] = array(
			'is_new' => true,
			'id' => 0,
			'name' => '',
		);
	}
	else
	{
		$request = db_query("
			SELECT id_notebook, name
			FROM mynotebook
			WHERE id_notebook = $id_notebook
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['notebook'] = array(
				'is_new' => false,
				'id' => $row['id_notebook'],
				'name' => $row['name'],
			);
		}
		db_free_result($request);

		if (!isset($template['notebook']))
			fatal_error('The notebook requested does not exist!');
	}

	$template['page_title'] = (!$is_new ? 'Edit' : 'Add') . ' Notebook';
	$core['current_template'] = 'mynotebook_edit';
}

function mynotebook_delete()
{
	global $core, $template, $user;

	$id_notebook = !empty($_REQUEST['mynotebook']) ? (int) $_REQUEST['mynotebook'] : 0;

	$request = db_query("
		SELECT id_notebook, name
		FROM mynotebook
		WHERE id_notebook = $id_notebook
			AND id_user = $user[id]
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['notebook'] = array(
			'id' => $row['id_notebook'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['notebook']))
		fatal_error('The notebook requested does not exist!');

	if (!empty($_POST['delete']))
	{
		check_session('mynotebook');

		db_query("
			DELETE FROM mynotebook
			WHERE id_notebook = $id_notebook
			LIMIT 1");
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('mynotebook'));

	$template['page_title'] = 'Delete Notebook';
	$core['current_template'] = 'mynotebook_delete';
}