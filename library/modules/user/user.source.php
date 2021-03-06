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

function user_main()
{
	global $core;

	$actions = array('list', 'edit', 'reset', 'delete');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function user_list()
{
	global $core, $template;

	$request = db_query("
		SELECT
			u.id_user, u.id_unique, u.ssid, u.name, u.email_address,
			u.registered, u.admin, c.name AS class
		FROM user AS u
			LEFT JOIN class AS c ON (c.id_class = u.id_class)
		ORDER BY u.name");
	$template['users'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['users'][] = array(
			'id' => $row['id_user'],
			'unique' => $row['id_unique'],
			'ssid' => $row['ssid'],
			'name' => $row['name'],
			'email_address' => $row['email_address'],
			'class' => $row['class'],
			'registered' => format_time($row['registered']),
			'admin' => $row['admin'] ? 'Yes' : 'No',
		);
	}
	db_free_result($request);

	$template['page_title'] = 'User List';
	$core['current_template'] = 'user_list';
}

function user_edit()
{
	global $core, $template;

	$id_user = !empty($_REQUEST['user']) ? (int) $_REQUEST['user'] : 0;
	$is_new = empty($id_user);

	if ($is_new)
	{
		$template['user'] = array(
			'is_new' => true,
			'id' => 0,
			'ssid' => '',
			'name' => '',
			'email_address' => '',
			'class' => 0,
			'admin' => 0,
		);
	}
	else
	{
		$request = db_query("
			SELECT
				id_user, id_unique, ssid, name, email_address, admin,
				login_count, last_login, last_password_change, id_class
			FROM user
			WHERE id_user = $id_user
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['user'] = array(
				'is_new' => false,
				'id' => $row['id_user'],
				'unique' => $row['id_unique'],
				'ssid' => $row['ssid'],
				'name' => $row['name'],
				'email_address' => $row['email_address'],
				'class' => $row['id_class'],
				'admin' => $row['admin'],
				'login_count' => $row['login_count'],
				'last_login' => empty($row['last_login']) ? 'Never' : format_time($row['last_login'], 'long'),
				'last_password_change' => empty($row['last_password_change']) ? 'Never' : format_time($row['last_password_change'], 'long'),
			);
		}
		db_free_result($request);

		if (!isset($template['user']))
			fatal_error('The user requested does not exist!');
	}

	if (!empty($_POST['save']))
	{
		check_session('user');

		$values = array();
		$fields = array(
			'ssid' => 'ssid',
			'name' => 'string',
			'email_address' => 'email',
			'id_class' => 'int',
			'password' => 'password',
			'verify_password' => 'password',
			'admin' => 'int',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'password')
				$values[$field] = !empty($_POST[$field]) ? sha1($_POST[$field]) : '';
			elseif ($type === 'ssid')
				$values[$field] = !empty($_POST[$field]) && !preg_match('~[^A-Z0-9]~', $_POST[$field]) ? $_POST[$field] : '';
			elseif ($type === 'email')
				$values[$field] = !empty($_POST[$field]) && preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $_POST[$field]) ? $_POST[$field] : '';
			elseif ($type === 'int')
				$values[$field] = !empty($_POST[$field]) ? (int) $_POST[$field] : 0;
			elseif ($type === 'string')
				$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';
		}

		if ($values['name'] === '')
			fatal_error('You did not enter a valid name!');
		elseif ($values['ssid'] === '')
			fatal_error('You did not enter a valid ID!');

		$request = db_query("
			SELECT id_user
			FROM user
			WHERE ssid = '$values[ssid]'
				AND id_user != $id_user
			LIMIT 1");
		list ($duplicate_id) = db_fetch_row($request);
		db_free_result($request);

		if (!empty($duplicate_id))
			fatal_error('The ID entered is already in use!');

		if ($values['email_address'] === '')
			fatal_error('You did not enter a valid email address!');

		$request = db_query("
			SELECT id_user
			FROM user
			WHERE email_address = '$values[email_address]'
				AND id_user != $id_user
			LIMIT 1");
		list ($duplicate_id) = db_fetch_row($request);
		db_free_result($request);

		if (!empty($duplicate_id))
			fatal_error('The email address entered is already in use!');

		if ($values['password'] === '' && $is_new)
			fatal_error('You did not enter a valid password!');
		elseif ($values['password'] === '')
			unset($values['password'], $values['verify_password']);
		elseif ($values['password'] !== $values['verify_password'])
			fatal_error('The passwords entered do not match!');
		else
			unset($values['verify_password']);

		if ($is_new)
		{
			$unique = substr(md5(session_id() . mt_rand() . (string) microtime()), 0, 20);

			$request = db_query("
				SELECT id_user
				FROM user
				WHERE id_unique = '$unique'
				LIMIT 1");
			list ($duplicate_id) = db_fetch_row($request);
			db_free_result($request);

			if (!empty($duplicate_id))
				$unique = substr(md5(session_id() . mt_rand() . (string) microtime()), 0, 20);

			$insert = array(
				'id_unique' => "'" . $unique . "'",
				'registered' => time(),
			);
			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO user
					(" . implode(', ', array_keys($insert)) . ")
				VALUES
					(" . implode(', ', $insert) . ")");

			mkdir($core['storage_dir'] . '/' . $values['ssid']);
		}
		else
		{
			$update = array();
			foreach ($values as $field => $value)
				$update[] = $field . " = '" . $value . "'";

			db_query("
				UPDATE user
				SET " . implode(', ', $update) . "
				WHERE id_user = $id_user
				LIMIT 1");

			if ($values['ssid'] != $template['user']['ssid'])
				rename($core['storage_dir'] . '/' . $template['user']['ssid'], $core['storage_dir'] . '/' . $values['ssid']);
		}

		if (!empty($_FILES['photo']) && !empty($_FILES['photo']['name']))
		{
			$photo_size = (int) $_FILES['photo']['size'];
			$photo_extension = htmlspecialchars(strtolower(substr(strrchr($_FILES['photo']['name'], '.'), 1)), ENT_QUOTES);
			$photo_dir = $core['site_dir'] . '/interface/img/photo_' . ($is_new ? $unique : $template['user']['unique']) . '.' . $photo_extension;

			if (!is_uploaded_file($_FILES['photo']['tmp_name']) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['photo']['tmp_name'])))
				fatal_error('Photo could not be uploaded!');

			if ($photo_size > 1 * 1024 * 1024)
				fatal_error('Photo cannot be larger than 1 MB!');

			if (!in_array($photo_extension, array('png')))
				fatal_error('Only photos with the following extensions can be uploaded: png');

			@unlink($photo_dir);

			if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_dir))
				fatal_error('Photo could not be uploaded!');
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('user'));

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

	$template['page_title'] = (!$is_new ? 'Edit' : 'Add') . ' User';
	$core['current_template'] = 'user_edit';
}

function user_reset()
{
	global $core, $template;

	$id_user = !empty($_REQUEST['user']) ? (int) $_REQUEST['user'] : 0;

	$request = db_query("
		SELECT id_user, id_unique, name
		FROM user
		WHERE id_user = $id_user
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$unique = $row['id_unique'];

		$template['user'] = array(
			'id' => $row['id_user'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['user']))
		fatal_error('The user requested does not exist!');

	if (!empty($_POST['reset']))
	{
		check_session('user');

		$new_unique = substr(md5(session_id() . mt_rand() . (string) microtime()), 0, 20);

		$request = db_query("
			SELECT id_user
			FROM user
			WHERE id_unique = '$new_unique'
			LIMIT 1");
		list ($duplicate_id) = db_fetch_row($request);
		db_free_result($request);

		if (!empty($duplicate_id))
			$new_unique = substr(md5(session_id() . mt_rand() . (string) microtime()), 0, 20);

		db_query("
			UPDATE user
			SET id_unique = '$new_unique'
			WHERE id_user = $id_user
			LIMIT 1");

		$base_photo_dir = $core['site_dir'] . '/interface/img/photo_%s.png';

		if (file_exists(sprintf($base_photo_dir, $unique)))
			rename(sprintf($base_photo_dir, $unique), sprintf($base_photo_dir, $new_unique));
	}

	if (!empty($_POST['reset']) || !empty($_POST['cancel']))
		redirect(build_url('user'));

	$template['page_title'] = 'Reset User Unique ID';
	$core['current_template'] = 'user_reset';
}

function user_delete()
{
	global $core, $template;

	$id_user = !empty($_REQUEST['user']) ? (int) $_REQUEST['user'] : 0;

	$request = db_query("
		SELECT id_user, name
		FROM user
		WHERE id_user = $id_user
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['user'] = array(
			'id' => $row['id_user'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['user']))
		fatal_error('The user requested does not exist!');

	if (!empty($_POST['delete']))
	{
		check_session('user');

		db_query("
			DELETE FROM user
			WHERE id_user = $id_user
			LIMIT 1");
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('user'));

	$template['page_title'] = 'Delete User';
	$core['current_template'] = 'user_delete';
}