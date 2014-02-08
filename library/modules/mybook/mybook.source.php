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
		SELECT id_book, name
		FROM mybook
		WHERE id_user = $user[id]
		ORDER BY name");
	$template['books'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['books'][] = array(
			'id' => $row['id_book'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Book List';
	$core['current_template'] = 'mybook_list';
}

function mybook_add()
{
	global $core, $template, $user;

	$request = db_query("
		SELECT id_book, name
		FROM book
		WHERE FIND_IN_SET($user[id_class], class)
		ORDER BY name");
	$template['books'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['books'][$row['id_book']] = array(
			'id' => $row['id_book'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (empty($template['books']))
		fatal_error('There are no books available to add!');

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
		elseif (!isset($template['books'][$values['id_book']]))
			fatal_error('The book selected is not valid!');
		else
		{
			$id_parent = $values['id_book'];
			unset($values['id_book']);
		}

		$insert = array(
			'name' => "'" . $template['books'][$id_parent]['name'] . "'",
			'id_user' => $user['id'],
		);
		foreach ($values as $field => $value)
			$insert[$field] = "'" . $value . "'";

		db_query("
			INSERT INTO mybook
				(" . implode(', ', array_keys($insert)) . ")
			VALUES
				(" . implode(', ', $insert) . ")");

		$id_book = db_insert_id();

		$book_dir = $core['storage_dir'] . '/' . $user['ssid'] . '/b' . $id_book;
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

			closedir($current_dir);
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('mybook'));

	$template['page_title'] = 'Add Book';
	$core['current_template'] = 'mybook_add';
}

function mybook_delete()
{
	global $core, $template, $user;

	$id_book = !empty($_REQUEST['mybook']) ? (int) $_REQUEST['mybook'] : 0;

	$request = db_query("
		SELECT id_book, name
		FROM mybook
		WHERE id_book = $id_book
			AND id_user = $user[id]
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