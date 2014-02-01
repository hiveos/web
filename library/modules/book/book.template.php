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
		<div book="page-header">
			<div book="pull-right">
				<a book="btn btn-warning" href="', build_url(array('book', 'edit')), '">Add Book</a>
			</div>
			<h2>Book List</h2>
		</div>
		<table book="table table-striped table-bordered">
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
					<td book="align_center" colspan="2">There are not any books added yet!</td>
				</tr>';
	}

	foreach ($template['books'] as $book)
	{
		echo '
				<tr>
					<td>', $book['name'], '</td>
					<td book="span3 align_center">
						<a book="btn btn-primary" href="', build_url(array('book', 'edit', $book['id'])), '">Edit</a>
						<a book="btn btn-danger" href="', build_url(array('book', 'delete', $book['id'])), '">Delete</a>
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
		<form book="form-horizontal" action="', build_url(array('book', 'edit')), '" method="post">
			<fieldset>
				<legend>', (!$template['book']['is_new'] ? 'Edit' : 'Add'), ' Book</legend>
				<div book="control-group">
					<label book="control-label" for="name">Name:</label>
					<div book="controls">
						<input type="text" book="input-xlarge" id="name" name="name" value="', $template['book']['name'], '" />
					</div>
				</div>
				<div book="form-actions">
					<input type="submit" book="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" book="btn" name="cancel" value="Cancel" />
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
		<form book="form-horizontal" action="', build_url(array('book', 'delete')), '" method="post">
			<fieldset>
				<legend>Delete Book</legend>
				Are you sure you want to delete the book &quot;', $template['book']['name'], '&quot;?
				<div book="form-actions">
					<input type="submit" book="btn btn-danger" name="delete" value="Delete" />
					<input type="submit" book="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="book" value="', $template['book']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}