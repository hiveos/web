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

function template_book_list()
{
	global $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn btn-warning" href="', build_url(array('book', 'edit')), '">Add Book</a>
			</div>
			<h2>Book List</h2>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Name</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($template['books']))
	{
		echo '
				<tr>
					<td class="align_center" colspan="2">There are not any books added yet!</td>
				</tr>';
	}

	foreach ($template['books'] as $book)
	{
		echo '
				<tr>
					<td>', $book['name'], '</td>
					<td class="span3 align_center">
						<a class="btn btn-primary" href="', build_url(array('book', 'edit', $book['id'])), '">Edit</a>
						<a class="btn btn-danger" href="', build_url(array('book', 'delete', $book['id'])), '">Delete</a>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}

function template_book_edit()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('book', 'edit')), '" method="post">
			<fieldset>
				<legend>', (!$template['book']['is_new'] ? 'Edit' : 'Add'), ' Book</legend>
				<div class="control-group">
					<label class="control-label" for="name">Name:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="name" name="name" value="', $template['book']['name'], '" />
					</div>
				</div>
				<div class="form-actions">
					<input type="submit" class="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="book" value="', $template['book']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_book_delete()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('book', 'delete')), '" method="post">
			<fieldset>
				<legend>Delete Book</legend>
				Are you sure you want to delete the book &quot;', $template['book']['name'], '&quot;?
				<div class="form-actions">
					<input type="submit" class="btn btn-danger" name="delete" value="Delete" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="book" value="', $template['book']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}