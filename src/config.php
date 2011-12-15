<?php

// Databases
$app['db.config.driver']    = 'pdo_mysql';
$app['db.config.host']      = 'localhost';
$app['db.config.dbname']    = 'ftp';
$app['db.config.user']      = 'root';
$app['db.config.password']  = 'root';

// Debug
$app['debug'] = true;

// Log
$app['monolog.filename']    = 'silexproftpd';
$app['monolog.loglevel']    = 300;
