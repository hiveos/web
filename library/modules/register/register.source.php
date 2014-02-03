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

function register_main()
{
	global $core, $template;

	if (!empty($_POST['submit']))
	{
		check_session('register');

		$values = array();
		$fields = array(
			'ssid' => 'ssid',
			'name' => 'string',
			'email_address' => 'email',
			'password' => 'password',
			'verify_password' => 'password',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'string')
				$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';
			elseif ($type === 'password')
				$values[$field] = !empty($_POST[$field]) ? sha1($_POST[$field]) : '';
			elseif ($type === 'ssid')
				$values[$field] = !empty($_POST[$field]) && !preg_match('~[^A-Z0-9]~', $_POST[$field]) ? $_POST[$field] : '';
			elseif ($type === 'email')
				$values[$field] = !empty($_POST[$field]) && preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $_POST[$field]) ? $_POST[$field] : '';
		}

		if ($values['name'] === '')
			fatal_error('You did not enter a valid name!');
		elseif ($values['ssid'] === '')
			fatal_error('You did not enter a valid ID!');

		$request = db_query("
			SELECT id_user
			FROM user
			WHERE ssid = '$values[ssid]'
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
			LIMIT 1");
		list ($duplicate_id) = db_fetch_row($request);
		db_free_result($request);

		if (!empty($duplicate_id))
			fatal_error('The email address entered is already in use!');

		if ($values['password'] === '')
			fatal_error('You did not enter a valid password!');

		if ($values['password'] !== $values['verify_password'])
			fatal_error('The passwords entered do not match!');

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

		db_query("
			INSERT INTO user
				(id_unique, ssid, name, password, email_address, registered)
			VALUES
				('$unique', '$values[ssid]', '$values[name]', '$values[password]', '$values[email_address]', " . time() . ")");

		mkdir($core['storage_dir'] . '/' . $values['ssid']);

		redirect(build_url('login'));
	}

	$template['page_title'] = 'Register';
	$core['current_template'] = 'register_main';
}