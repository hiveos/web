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

function subject_main()
{
	global $core;

	$actions = array('list', 'edit', 'delete');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function subject_list()
{
	global $core, $template;

	$request = db_query("
		SELECT id_subject, name
		FROM subject
		ORDER BY name");
	$template['subjects'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['subjects'][] = array(
			'id' => $row['id_subject'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Subject List';
	$core['current_template'] = 'subject_list';
}

function subject_edit()
{
	global $core, $template;

	$id_subject = !empty($_REQUEST['subject']) ? (int) $_REQUEST['subject'] : 0;
	$is_new = empty($id_subject);

	if (!empty($_POST['save']))
	{
		check_session('subject');

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
			fatal_error('Subject name field cannot be empty!');

		if ($is_new)
		{
			$insert = array();
			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO subject
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
				UPDATE subject
				SET " . implode(', ', $update) . "
				WHERE id_subject = $id_subject
				LIMIT 1");
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('subject'));

	if ($is_new)
	{
		$template['subject'] = array(
			'is_new' => true,
			'id' => 0,
			'name' => '',
		);
	}
	else
	{
		$request = db_query("
			SELECT id_subject, name
			FROM subject
			WHERE id_subject = $id_subject
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['subject'] = array(
				'is_new' => false,
				'id' => $row['id_subject'],
				'name' => $row['name'],
			);
		}
		db_free_result($request);

		if (!isset($template['subject']))
			fatal_error('The subject requested does not exist!');
	}

	$template['page_title'] = (!$is_new ? 'Edit' : 'Add') . ' Subject';
	$core['current_template'] = 'subject_edit';
}

function subject_delete()
{
	global $core, $template;

	$id_subject = !empty($_REQUEST['subject']) ? (int) $_REQUEST['subject'] : 0;

	$request = db_query("
		SELECT id_subject, name
		FROM subject
		WHERE id_subject = $id_subject
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['subject'] = array(
			'id' => $row['id_subject'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['subject']))
		fatal_error('The subject requested does not exist!');

	if (!empty($_POST['delete']))
	{
		check_session('subject');

		db_query("
			DELETE FROM subject
			WHERE id_subject = $id_subject
			LIMIT 1");

		db_query("
			UPDATE book
			SET id_subject = 0
			WHERE id_subject = $id_subject");
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('subject'));

	$template['page_title'] = 'Delete Subject';
	$core['current_template'] = 'subject_delete';
}