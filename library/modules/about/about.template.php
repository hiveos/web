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

function template_about_main()
{
	echo '
		<div class="page-header">
			<h2>About</h2>
		</div>
		<p class="content">
			HIVE-web is the web site base for the HIVE project.
		</p>';
}