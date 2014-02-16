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
	global $core, $user, $template;

	echo '
		<div class="page-header">
			<h2>Welcome!</h2>
		</div>';

if ($user['logged'])
echo '
		<p class="well">You can access all your books, notebooks and drawings here. Thank you for learning with HIVE!</p>';
else
echo '
		<p class="well">You can access all your books, notebooks and drawings here. Please login to access your data.</p>';
}