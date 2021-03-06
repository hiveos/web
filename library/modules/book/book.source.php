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

function book_main()
{
	global $core;

	$actions = array('list', 'edit', 'delete');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function book_list()
{
	global $core, $template;

	$request = db_query("
		SELECT b.id_book, b.name, s.name AS subject
		FROM book AS b
			LEFT JOIN subject AS s ON (s.id_subject = b.id_subject)
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
	$core['current_template'] = 'book_list';
}

function book_edit()
{
	global $core, $template;

	$id_book = !empty($_REQUEST['book']) ? (int) $_REQUEST['book'] : 0;
	$is_new = empty($id_book);

	if (!empty($_POST['save']))
	{
		check_session('book');

		$values = array();
		$fields = array(
			'name' => 'string',
			'id_subject' => 'int',
			'class' => 'array_int',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'string')
				$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';
			elseif ($type === 'int')
				$values[$field] = !empty($_POST[$field]) ? (int) $_POST[$field] : 0;
			elseif ($type === 'array_int')
			{
				$values[$field] = array();
				if (!empty($_POST[$field]) && is_array($_POST[$field]))
				{
					foreach ($_POST[$field] as $value)
						$values[$field][] = (int) $value;
				}
				$values[$field] = implode(',', $values[$field]);
			}
		}

		if ($values['name'] === '')
			fatal_error('Book name field cannot be empty!');
		elseif ($values['id_subject'] === 0)
			fatal_error('Book subject field cannot be empty!');
		elseif ($values['class'] === '')
			fatal_error('Book classes field cannot be empty!');

		if ($is_new)
		{
			$insert = array();
			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO book
					(" . implode(', ', array_keys($insert)) . ")
				VALUES
					(" . implode(', ', $insert) . ")");

			$id_book = db_insert_id();

			mkdir($core['storage_dir'] . '/shared/' . $id_book);
		}
		else
		{
			$update = array();
			foreach ($values as $field => $value)
				$update[] = $field . " = '" . $value . "'";

			db_query("
				UPDATE book
				SET " . implode(', ', $update) . "
				WHERE id_book = $id_book
				LIMIT 1");
		}

		if (!empty($_FILES['file']) && !empty($_FILES['file']['name']))
		{
			$file_size = (int) $_FILES['file']['size'];
			$file_extension = htmlspecialchars(strtolower(substr(strrchr($_FILES['file']['name'], '.'), 1)), ENT_QUOTES);
			$file_dir = $core['storage_dir'] . '/shared/temp_s_' . $id_book . '.' . $file_extension;

			if (!is_uploaded_file($_FILES['file']['tmp_name']) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['file']['tmp_name'])))
				fatal_error('File could not be uploaded!');

			if ($file_size > 10 * 1024 * 1024)
				fatal_error('Files cannot be larger than 10 MB!');

			if (!in_array($file_extension, array('pdf')))
				fatal_error('Only files with the following extensions can be uploaded: pdf');

			if (file_exists($file_dir))
				unlink($file_dir);

			if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_dir))
				fatal_error('File could not be uploaded!');

			$target_dir = $core['storage_dir'] . '/shared/' . $id_book . '/page.png';

			$fp = fopen($file_dir, 'rb');
			$img = new imagick();
			$img->setResolution(150,150);
			$img->readImageFile($fp);
			$img->setImageFormat('png');
			$img->setImageCompression(imagick::COMPRESSION_JPEG);
			$img->setImageCompressionQuality(90);
			$img->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH);
			$img->writeImages($target_dir, false);
			$img->clear();
			fclose($fp);

			unlink($file_dir);

			$temp = list_dir(dirname($target_dir));

			foreach ($temp as $file)
				rename(dirname($target_dir) . '/' . $file, dirname($target_dir) . '/' . preg_replace('~^page\-(\d+).png$~e', "'page' . ($1 + 1) . '.png'", $file));
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('book'));

	if ($is_new)
	{
		$template['book'] = array(
			'is_new' => true,
			'id' => 0,
			'name' => '',
			'subject' => 0,
			'class' => array(),
		);
	}
	else
	{
		$request = db_query("
			SELECT id_book, name, id_subject, class
			FROM book
			WHERE id_book = $id_book
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['book'] = array(
				'is_new' => false,
				'id' => $row['id_book'],
				'name' => $row['name'],
				'subject' => $row['id_subject'],
				'class' => !empty($row['class']) ? explode(',', $row['class']) : array(),
			);
		}
		db_free_result($request);

		if (!isset($template['book']))
			fatal_error('The book requested does not exist!');
	}

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

	if (empty($template['subjects']))
		fatal_error('There are no subjects added yet! You cannot add books without subjects!');

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

	if (empty($template['classes']))
		fatal_error('There are no classes added yet! You cannot add books without classes!');

	$template['page_title'] = (!$is_new ? 'Edit' : 'Add') . ' Book';
	$core['current_template'] = 'book_edit';
}

function book_delete()
{
	global $core, $template;

	$id_book = !empty($_REQUEST['book']) ? (int) $_REQUEST['book'] : 0;

	$request = db_query("
		SELECT id_book, name
		FROM book
		WHERE id_book = $id_book
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
		check_session('book');

		db_query("
			DELETE FROM book
			WHERE id_book = $id_book
			LIMIT 1");
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('book'));

	$template['page_title'] = 'Delete Book';
	$core['current_template'] = 'book_delete';
}