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

	$actions = array('list', 'view', 'add', 'delete');

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

function mybook_view()
{
	global $core, $template, $user;

	$id_book = !empty($_REQUEST['mybook']) ? (int) $_REQUEST['mybook'] : 0;
	$page = !empty($_REQUEST['view']) ? (int) $_REQUEST['view'] : 0;
	$book_dir = $core['storage_dir'] . '/' . $user['ssid'] . '/b' . $id_book;

	$request = db_query("
		SELECT id_book, name
		FROM mybook
		WHERE id_book = $id_book
			AND id_user = $user[id]
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['book'] = array(
			'id' => $row['id_book'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['book']) || !file_exists($book_dir))
		fatal_error('The book requested does not exist!');

	$pages = array();
	$temp = list_dir($book_dir);

	foreach ($temp as $file)
	{
		if (preg_match('~^page(\d+).png$~', $file, $match))
			$pages[] = $match[1];
	}

	natsort($pages);

	if (empty($pages))
		fatal_error('There are no pages in the requested book!');
	elseif (!in_array($page, $pages))
		$page = current($pages);

	$template['book']['page'] = $page;
	$template['book']['pages'] = count($pages);
	$template['book']['previous'] = $page > 1;
	$template['book']['next'] = $page < count($pages);

	$template['page_title'] = 'View Book';
	$core['current_template'] = 'mybook_view';
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

		copy_dir($parent_dir, $book_dir);
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
		$template['book'] = array(
			'id' => $row['id_book'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['book']))
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