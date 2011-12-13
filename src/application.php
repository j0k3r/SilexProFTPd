<?php

require_once __DIR__ . '/bootstrap.php';


$app->get('/user/{id}/edit', function ($id) use ($app) {
  $sql    = "SELECT * FROM users WHERE id = ?";
  $user   = $app['db']->fetchAssoc($sql, array((int) $id));

  return $app['twig']->render('user_edit.twig', array('user' => $user, 'active' => 'user_list'));
})->assert('id', '\d+')
  ->bind('user_edit');


$app->get('/user/{id}/delete', function ($id) use ($app) {
  $sql = "DELETE FROM users WHERE id = ?";
  $app['db']->fetchAssoc($sql, array((int) $id));

  return new RedirectResponse('/homepage');
})->assert('id', '\d+')
  ->bind('user_delete');


$app->get('/users', function() use ($app) {
  $sql    = "SELECT * FROM users";
  $users  = $app['db']->fetchAll($sql);

  return $app['twig']->render('user_list.twig', array('users' => $users, 'active' => 'user_list'));
})->bind('user_list');


$app->get('/', function() use ($app) {
  $data = array(
    'transfer'       => $app['db']->fetchAssoc("SELECT SUM(transfersize) as size, SUM(transfertime) as time FROM history"),
    'user_active'    => $app['db']->fetchAssoc("SELECT count(id) as count FROM users WHERE valid = 1"),
    'user_inactive'  => $app['db']->fetchAssoc("SELECT count(id) as count FROM users WHERE valid != 1"),
    'nb_connexions'  => $app['db']->fetchAssoc("SELECT SUM(count) as sum FROM users")
  );

  $activities = $app['db']->fetchAll('SELECT * FROM history ORDER BY id DESC LIMIT 0, 10');

  return $app['twig']->render('index.twig', array('data' => $data, 'activities' => $activities, 'active' => 'home'));
})->bind('homepage');

return $app;
