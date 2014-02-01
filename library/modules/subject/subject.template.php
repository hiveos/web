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

function template_subject_list()
{
	global $template;

	echo '
		<div subject="page-header">
			<div subject="pull-right">
				<a subject="btn btn-warning" href="', build_url(array('subject', 'edit')), '">Add Subject</a>
			</div>
			<h2>Subject List</h2>
		</div>
		<table subject="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Name</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($template['subjects']))
	{
		echo '
				<tr>
					<td subject="align_center" colspan="2">There are not any subjects added yet!</td>
				</tr>';
	}

	foreach ($template['subjects'] as $subject)
	{
		echo '
				<tr>
					<td>', $subject['name'], '</td>
					<td subject="span3 align_center">
						<a subject="btn btn-primary" href="', build_url(array('subject', 'edit', $subject['id'])), '">Edit</a>
						<a subject="btn btn-danger" href="', build_url(array('subject', 'delete', $subject['id'])), '">Delete</a>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}

function template_subject_edit()
{
	global $user, $template;

	echo '
		<form subject="form-horizontal" action="', build_url(array('subject', 'edit')), '" method="post">
			<fieldset>
				<legend>', (!$template['subject']['is_new'] ? 'Edit' : 'Add'), ' Subject</legend>
				<div subject="control-group">
					<label subject="control-label" for="name">Name:</label>
					<div subject="controls">
						<input type="text" subject="input-xlarge" id="name" name="name" value="', $template['subject']['name'], '" />
					</div>
				</div>
				<div subject="form-actions">
					<input type="submit" subject="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" subject="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="subject" value="', $template['subject']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_subject_delete()
{
	global $user, $template;

	echo '
		<form subject="form-horizontal" action="', build_url(array('subject', 'delete')), '" method="post">
			<fieldset>
				<legend>Delete Subject</legend>
				Are you sure you want to delete the subject &quot;', $template['subject']['name'], '&quot;?
				<div subject="form-actions">
					<input type="submit" subject="btn btn-danger" name="delete" value="Delete" />
					<input type="submit" subject="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="subject" value="', $template['subject']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}