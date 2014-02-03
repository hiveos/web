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

function template_mynotebook_list()
{
	global $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn btn-warning" href="', build_url(array('mynotebook', 'edit')), '">Add Notebook</a>
			</div>
			<h2>Notebook List</h2>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Name</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($template['notebooks']))
	{
		echo '
				<tr>
					<td class="align_center" colspan="2">There are not any notebooks added yet!</td>
				</tr>';
	}

	foreach ($template['notebooks'] as $notebook)
	{
		echo '
				<tr>
					<td>', $notebook['name'], '</td>
					<td class="span3 align_center">
						<a class="btn btn-primary" href="', build_url(array('mynotebook', 'edit', $notebook['id'])), '">Edit</a>
						<a class="btn btn-danger" href="', build_url(array('mynotebook', 'delete', $notebook['id'])), '">Delete</a>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}

function template_mynotebook_edit()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('mynotebook', 'edit')), '" method="post">
			<fieldset>
				<legend>', (!$template['notebook']['is_new'] ? 'Edit' : 'Add'), ' Notebook</legend>
				<div class="control-group">
					<label class="control-label" for="name">Name:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="name" name="name" value="', $template['notebook']['name'], '" />
					</div>
				</div>
				<div class="form-actions">
					<input type="submit" class="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="mynotebook" value="', $template['notebook']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_mynotebook_delete()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('mynotebook', 'delete')), '" method="post">
			<fieldset>
				<legend>Delete Notebook</legend>
				Are you sure you want to delete the notebook &quot;', $template['notebook']['name'], '&quot;?
				<div class="form-actions">
					<input type="submit" class="btn btn-danger" name="delete" value="Delete" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="mynotebook" value="', $template['notebook']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}