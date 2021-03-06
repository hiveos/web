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

function template_profile_main()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url('profile'), '" method="post">
			<fieldset>
				<legend>Edit Profile</legend>
				<div class="control-group">
					<label class="control-label" for="ssid">ID:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="ssid">', $user['ssid'], '</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="unique">Unique ID:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="unique">', $user['unique'], '</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="name">Name:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="name">', $user['name'], '</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="email_address">Email address:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="email_address" name="email_address" value="', $template['profile']['email_address'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="class">Class:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="class">', $template['profile']['class'], '</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="choose_password">Choose password:</label>
					<div class="controls">
						<input type="password" class="input-xlarge" id="choose_password" name="choose_password" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="verify_password">Verify password:</label>
					<div class="controls">
						<input type="password" class="input-xlarge" id="verify_password" name="verify_password" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="current_password">Current password:</label>
					<div class="controls">
						<input type="password" class="input-xlarge" id="current_password" name="current_password" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="login_count">Login count:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="login_count">', $template['profile']['login_count'], '</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="last_login">Last login:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="last_login">', $template['profile']['last_login'], '</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="last_password_change">Last password change:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="last_password_change">', $template['profile']['last_password_change'], '</span>
					</div>
				</div>
				<div class="form-actions">
					<input type="submit" class="btn btn-primary" name="save" value="Save changes" />
				</div>
			</fieldset>
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}