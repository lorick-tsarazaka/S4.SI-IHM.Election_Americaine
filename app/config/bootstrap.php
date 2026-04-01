<?php

	$ds = DIRECTORY_SEPARATOR;
	require(__DIR__ . $ds . '..' . $ds . '..' . $ds . 'vendor' . $ds . 'autoload.php');
	if(file_exists(__DIR__. $ds . 'config.php') === false) {
		Flight::halt(500, 'Config file not found. Please create a config.php file in the app/config directory to get started.');
	}

	$app = Flight::app();
	$config = require('config.php');

	require('services.php');

	$router = $app->router();
	require('routes.php');

	$app->start();
