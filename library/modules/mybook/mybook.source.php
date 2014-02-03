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

function mybook_main()
{
	global $core;

	$actions = array('list', 'add', 'delete');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function mybook_list()
{
	global $core, $template, $user;

	$request = db_query("
		SELECT m.id_book, b.name, s.name AS subject
		FROM mybook AS m
			INNER JOIN book AS b ON (b.id_book = m.id_book)
			INNER JOIN subject AS s ON (s.id_subject = b.id_subject)
		WHERE m.id_user = $user[id]
		ORDER BY b.name");
	$template['books'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['books'][] = array(
			'id' => $row['id_book'],
			'name' => $row['name'],
			'subject' => $row['subject'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Book List';
	$core['current_template'] = 'mybook_list';
}

function mybook_add()
{
	global $core, $template, $user;

	if (!empty($_POST['save']))
	{
		check_session('mybook');

		$values = array();
		$fields = array(
			'id_book' => 'int',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'int')
				$values[$field] = !empty($_POST[$field]) ? (int) $_POST[$field] : 0;
		}

		if ($values['id_book'] === 0)
			fatal_error('Book field cannot be empty!');

		$insert = array(
			'id_user' => $user['id'],
		);
		foreach ($values as $field => $value)
			$insert[$field] = "'" . $value . "'";

		db_query("
			INSERT INTO mybook
				(" . implode(', ', array_keys($insert)) . ")
			VALUES
				(" . implode(', ', $insert) . ")");
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('mybook'));

	$request = db_query("
		SELECT b.id_book, b.name
		FROM book AS b
			LEFT JOIN mybook AS m ON (m.id_book = b.id_book AND m.id_user = $user[id])
		WHERE IFNULL(m.id_link, 0) = 0
		ORDER BY b.name");
	$template['books'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['books'][] = array(
			'id' => $row['id_book'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (empty($template['books']))
		fatal_error('There are no books available to add!');

	$template['page_title'] = 'Add Book';
	$core['current_template'] = 'mybook_add';
}

function mybook_delete()
{
	global $core, $template, $user;

	$id_book = !empty($_REQUEST['mybook']) ? (int) $_REQUEST['mybook'] : 0;

	$request = db_query("
		SELECT m.id_book, b.name
		FROM mybook AS m
			INNER JOIN book AS b ON (b.id_book = m.id_book)
		WHERE m.id_book = $id_book
			AND m.id_user = $user[id]
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['mybook'] = array(
			'id' => $row['id_book'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['mybook']))
		fatal_error('The book requested does not exist!');

	if (!empty($_POST['delete']))
	{
		check_session('mybook');

		db_query("
			DELETE FROM mybook
			WHERE id_book = $id_book
				AND id_user = $user[id]
			LIMIT 1");
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('mybook'));

	$template['page_title'] = 'Delete Book';
	$core['current_template'] = 'mybook_delete';
}