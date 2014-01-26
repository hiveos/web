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

$core = array();

$core['title_short'] = 'HIVE';
$core['title_long'] = 'HIVE';
$core['version'] = '1.0';
$core['cookie'] = 'hive1997';
$core['time_offset'] = 0;
$core['clean_url'] = false;

$core['site_url'] = '';
$core['site_dir'] = dirname(__FILE__);

$core['root_dir'] = $core['site_dir'] . '/library';
$core['includes_dir'] = $core['root_dir'] . '/includes';
$core['modules_dir'] = $core['root_dir'] . '/modules';
$core['storage_dir'] = $core['site_dir'] . '/storage';

$db = array();

$db['server'] = '';
$db['name'] = '';
$db['user'] = '';
$db['password'] = '';