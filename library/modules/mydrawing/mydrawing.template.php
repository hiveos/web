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

function template_mydrawing_list()
{
	global $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn btn-warning" href="', build_url(array('mydrawing', 'edit')), '">Add Drawing</a>
			</div>
			<h2>Drawing List</h2>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Name</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($template['drawings']))
	{
		echo '
				<tr>
					<td class="align_center" colspan="2">There are not any drawings added yet!</td>
				</tr>';
	}

	foreach ($template['drawings'] as $drawing)
	{
		echo '
				<tr>
					<td>', $drawing['name'], '</td>
					<td class="span3 align_center">
						<a class="btn btn-info" href="', build_url(array('mydrawing', 'view', $drawing['id'])), '">View</a>
						<a class="btn btn-primary" href="', build_url(array('mydrawing', 'edit', $drawing['id'])), '">Edit</a>
						<a class="btn btn-danger" href="', build_url(array('mydrawing', 'delete', $drawing['id'])), '">Delete</a>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}

function template_mydrawing_view()
{
	global $template;

	echo '
		<div class="page-header">
			<h2>View Drawing - ', $template['drawing']['name'], '</h2>
		</div>
		<div class="content_page">
			<img src="', build_url(array('output', 'drawing', $template['drawing']['page'], $template['drawing']['id'])), '" alt="" class="img-polaroid" />
		</div>';
}

function template_mydrawing_edit()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('mydrawing', 'edit')), '" method="post">
			<fieldset>
				<legend>', (!$template['drawing']['is_new'] ? 'Edit' : 'Add'), ' Drawing</legend>
				<div class="control-group">
					<label class="control-label" for="name">Name:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="name" name="name" value="', $template['drawing']['name'], '" />
					</div>
				</div>
				<div class="form-actions">
					<input type="submit" class="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="mydrawing" value="', $template['drawing']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_mydrawing_delete()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('mydrawing', 'delete')), '" method="post">
			<fieldset>
				<legend>Delete Drawing</legend>
				Are you sure you want to delete the drawing &quot;', $template['drawing']['name'], '&quot;?
				<div class="form-actions">
					<input type="submit" class="btn btn-danger" name="delete" value="Delete" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="mydrawing" value="', $template['drawing']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}