<?php

require_once __DIR__ . '/bootstrap.php';

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormError;


// New user
$app->match('/user/new', function () use ($app) {
  $constraint = new Assert\Collection(array(
    'fields' => array(
      'username'  => new Assert\NotBlank(),
      'passwd'    => new Assert\NotBlank(),
      'homedir'   => new Assert\NotBlank(),
      'email'     => new Assert\Email()
    ),
    'allowExtraFields' => true
  ));

  $builder = $app['form.factory']->createBuilder('form', array(), array('validation_constraint' => $constraint));

  $form = $builder
    ->add('fullname', 'text', array('label' => 'Fullname', 'required' => false))
    ->add('username', 'text', array('label' => 'Username'))
    ->add('passwd', 'password', array('label' => 'Password'))
    ->add('homedir', 'text', array('label' => 'Home dir'))
    ->add('email', 'email', array('label' => 'Email', 'required' => false))
    ->getForm()
  ;

  if ('POST' === $app['request']->getMethod())
  {
    $form->bindRequest($app['request']);

    if ($form->isValid())
    {
      // unique validator
      $sql = 'SELECT id FROM `users` WHERE username = ?';
      $uniq = $app['db']->fetchArray($sql, array($form->get('username')->getData()));

      if($uniq)
      {
        $form->addError(new FormError('This username (`'.$form->get('username')->getData().'`) is already in use.'));
      }
      else
      {
        $sql  = "INSERT INTO `users` (`username`, `passwd`, `fullname`, `homedir`, `email`, `valid`) VALUES (?, ENCRYPT(?), ?, ?, ?, 1)";
        $app['db']->executeQuery(
          $sql,
          array(
            $form->get('username')->getData(),
            $form->get('passwd')->getData(),
            $form->get('fullname')->getData(),
            $form->get('homedir')->getData(),
            $form->get('email')->getData()
          )
        );

        $app['session']->setFlash('notice', 'User added !');
        return $app->redirect($app['url_generator']->generate('user_edit', array('id' => $app['db']->lastInsertId())));
      }
    }
  }

  return $app['twig']->render('user_new.twig', array('form' => $form->createView(), 'active' => 'user_list'));
})->bind('user_new');


// Edit an user
$app->match('/user/{id}/edit', function ($id) use ($app) {
  $sql    = "SELECT * FROM `users` WHERE id = ?";
  $user   = $app['db']->fetchAssoc($sql, array((int) $id));

  $constraint = new Assert\Collection(array(
    'fields' => array(
      'username'  => new Assert\NotBlank(),
      'homedir'   => new Assert\NotBlank(),
      'email'     => new Assert\Email()
    ),
    'allowExtraFields' => true
  ));

  // parse data to give right type to the builder (ie: boolean for `valid` instead of string)
  $datas = array(
    'fullname'  => $user['fullname'],
    'username'  => $user['username'],
    'passwd'    => $user['passwd'],
    'homedir'   => $user['homedir'],
    'email'     => $user['email'],
    'valid'     => (bool) $user['valid'],
  );

  $builder = $app['form.factory']->createBuilder('form', $datas, array('validation_constraint' => $constraint));

  $form = $builder
    ->add('fullname', 'text', array('label' => 'Fullname'))
    ->add('username', 'text', array('label' => 'Username'))
    ->add('passwd', 'password', array('label' => 'Password', 'required' => false))
    ->add('homedir', 'text', array('label' => 'Home dir'))
    ->add('email', 'email', array('label' => 'Email', 'required' => false))
    ->add('valid', 'checkbox', array('label' => 'Account valid ?', 'required' => false))
    ->getForm()
  ;

  if ('POST' === $app['request']->getMethod())
  {
    $form->bindRequest($app['request']);

    if ($form->isValid())
    {
      // unique validator
      $sql = 'SELECT id FROM `users` WHERE username = ? AND id != ?';
      $uniq = $app['db']->fetchArray($sql, array($form->get('username')->getData(), (int) $id));

      if($uniq)
      {
        $form->addError(new FormError('This username (`'.$form->get('username')->getData().'`) is already in use.'));
      }
      else
      {
        $params = array(
          $form->get('fullname')->getData(),
          $form->get('email')->getData(),
          $form->get('valid')->getData(),
          $form->get('homedir')->getData(),
          $form->get('username')->getData(),
          (int) $id
        );

        $sql = "UPDATE `users` SET ";
        if(null !== $form->get('passwd')->getData())
        {
          $sql = "passwd = ENCRYPT(?), ";
          $params = array_merge(array($form->get('passwd')->getData()), $params);
        }
        $sql .= "fullname = ?, email = ?, valid = ?, homedir = ?, username = ? WHERE id = ?";

        $app['db']->executeQuery(
          $sql,
          $params
        );

        $app['session']->setFlash('notice', 'User saved !');
        return $app->redirect($app['url_generator']->generate('user_edit', array('id' => $id)));
      }
    }
  }

  $data = array(
    'uploaded' => $app['db']->fetchAssoc("SELECT SUM(transfersize) AS nb FROM `history` WHERE username = ? AND transfertype = 'STOR'", array($user['username'])),
    'download' => $app['db']->fetchAssoc("SELECT SUM(transfersize) AS nb FROM `history` WHERE username = ? AND transfertype = 'RETR'", array($user['username'])),
    'transfer' => $app['db']->fetchAssoc("SELECT COUNT(h.id) as nb, SUM(h.transfersize) as size, SUM(h.transfertime) as time FROM `history` h LEFT JOIN `users` u ON u.username = h.username WHERE u.id = ?", array((int) $id))
  );

  return $app['twig']->render('user_edit.twig', array('form' => $form->createView(), 'user' => $user, 'data' => $data, 'active' => 'user_list'));
})->assert('id', '\d+')
  ->bind('user_edit');


// User activate / deactivate
$app->get('/user/{id}/active', function ($id) use ($app) {
  $app['db']->executeQuery("UPDATE `users` SET valid = !valid WHERE id = ?", array((int) $id));

  return $app->redirect($app['url_generator']->generate('user_list'));
})->assert('id', '\d+')
  ->bind('user_active');


// Delete an user
$app->get('/user/{id}/delete', function ($id) use ($app) {
  $app['db']->delete('users', array('id' => (int) $id));

  $app['session']->setFlash('notice', 'User deleted !');

  return $app->redirect($app['url_generator']->generate('user_list'));
})->assert('id', '\d+')
  ->bind('user_delete');


// User listing (edit & delete)
$app->get('/users', function() use ($app) {
  $sql = "SELECT u.id, u.username, u.lastlogin, u.valid, u.count, u.fullname, SUM(h.transfersize) AS historySizeCount ";
  $sql .= "FROM users u LEFT OUTER JOIN history h ON h.username = u.username ";
  $sql .= "GROUP BY u.username, u.lastlogin, u.count, u.fullname, u.valid ";
  $sql .= "ORDER BY u.lastlogin DESC";
  $users  = $app['db']->fetchAll($sql);

  return $app['twig']->render('user_list.twig', array('users' => $users, 'active' => 'user_list'));
})->bind('user_list');


// History
$app->get('/transfers/{traffic}', function($traffic) use ($app) {
  $sql       = 'SELECT h.*, h.id as history_id, u.id as user_id FROM `history` h LEFT JOIN `users` u ON u.username = h.username';

  $params = array();
  if('STOR' == $traffic || 'RETR' == $traffic)
  {
    $sql .= ' WHERE h.transfertype = ?';
    $params = array($traffic);
  }

  $sql .= ' ORDER BY h.id DESC LIMIT 100';
  $histories = $app['db']->fetchAll($sql, $params);

  return $app['twig']->render('history.twig', array('histories' => $histories, 'active' => 'transfer'));
})->value('traffic', 'ALL')
  ->bind('history');


// User history
$app->get('/user/{id}/transfer/{traffic}', function($id, $traffic) use ($app) {
  $sql    = "SELECT * FROM `users` WHERE id = ?";
  $user   = $app['db']->fetchAssoc($sql, array((int) $id));

  $sql    = "SELECT *, id as history_id FROM `history` h WHERE username = ?";

  $params = array($user['username']);
  if('STOR' == $traffic || 'RETR' == $traffic)
  {
    $sql .= ' AND h.transfertype = ?';
    $params = array_merge($params, array($traffic));
  }

  $sql .= ' ORDER BY h.id DESC LIMIT 100';

  $histories  = $app['db']->fetchAll($sql, $params);

  return $app['twig']->render('history.twig', array('histories' => $histories, 'user' => $user, 'active' => 'transfer'));
})->assert('id', '\d+')
  ->value('traffic', 'ALL')
  ->bind('user_history');


// History view
$app->get('/transfer/{id}/view', function($id) use ($app) {
  $sql      = "SELECT h.*, u.id as user_id FROM `history` h LEFT JOIN `users` u ON u.username = h.username WHERE h.id = ?";
  $history  = $app['db']->fetchAssoc($sql, array((int) $id));

  // count how many times the itam has been downloaded
  $sql      = "SELECT COUNT(id) as nb FROM `history` WHERE filename = ?";
  $countDL  = $app['db']->fetchAssoc($sql, array($history['filename']));

  return $app['twig']->render('history_view.twig', array('history' => $history, 'countDL' => $countDL, 'active' => 'transfer'));
})->assert('id', '\d+')
  ->bind('history_view');


// File view
$app->get('/transfer/{id}/{filename}', function($filename, $id) use ($app) {
  $sql            = "SELECT u.id AS user_id, u.fullname, u.username, h.transferdate, h.transferhost, h.id AS history_id, h.transfertype FROM history h, users u WHERE u.username = h.username AND h.filename = ?";
  $file_histories = $app['db']->fetchAll($sql, array((string) $filename));

  return $app['twig']->render('file_transfer.twig', array('file_histories' => $file_histories, 'filename' => $filename, 'id' => $id, 'active' => 'transfer'));
})->assert('filename', '.*')
  ->bind('file_transfer');

// Error log
$app->get('/error', function() use ($app) {
  $sql          = "SELECT ue.*, ue.id as user_event_id, u.fullname, u.id as user_id FROM users u, userevents ue WHERE u.username = ue.username ORDER BY ue.id DESC LIMIT 100";
  $user_events  = $app['db']->fetchAll($sql);

  return $app['twig']->render('error.twig', array('user_events' => $user_events, 'active' => 'error'));
})->bind('error');


// homepage + statistics
$app->get('/', function() use ($app) {
  $data = array(
    'transfer'      => $app['db']->fetchAssoc("SELECT COUNT(id) as nb, SUM(transfersize) as size, SUM(transfertime) as time FROM `history`"),
    'user_active'   => $app['db']->fetchAssoc("SELECT count(id) as count FROM `users` WHERE valid = 1"),
    'user_inactive' => $app['db']->fetchAssoc("SELECT count(id) as count FROM `users` WHERE valid != 1"),
    'nb_connexions' => $app['db']->fetchAssoc("SELECT SUM(count) as sum FROM `users`"),
    'uploaded'      => $app['db']->fetchAssoc("SELECT SUM(transfersize) AS nb FROM `history` WHERE transfertype = 'STOR'"),
    'download'      => $app['db']->fetchAssoc("SELECT SUM(transfersize) AS nb FROM `history` WHERE transfertype = 'RETR'"),
  );

  $activities = $app['db']->fetchAll('SELECT h.id, h.username, h.transfertype, h.transferdate, h.filename, u.id as user_id FROM `history` h LEFT JOIN `users` u ON u.username = h.username ORDER BY id DESC LIMIT 0, 10');

  return $app['twig']->render('index.twig', array('data' => $data, 'activities' => $activities, 'active' => 'home'));
})->bind('homepage');

return $app;
