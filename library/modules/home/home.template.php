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

function template_home_main()
{
	global $core, $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				x users &bull; x something
			</div>
			<h2>', $core['title_long'], '</h2>
		</div>
		<p>Nothing to see here...</p>';
}