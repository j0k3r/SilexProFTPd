<?php

require_once __DIR__ . '/bootstrap.php';

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormError;

// Add an user
$app->get('/user/add', function () use ($app) {


  return $app->redirect($app['url_generator']->generate('user_list'));
})->bind('user_add');

// Edit an user
$app->match('/user/{id}/edit', function ($id) use ($app) {
  $sql    = "SELECT username, passwd, homedir, email FROM users WHERE id = ?";
  $user   = $app['db']->fetchAssoc($sql, array((int) $id));

  $constraint = new Assert\Collection(array(
    'fields' => array(
      'username'  => new Assert\NotBlank(),
      'homedir'   => new Assert\NotBlank(),
      'email'     => new Assert\Email()
    ),
    'allowExtraFields' => true
  ));

  $builder = $app['form.factory']->createBuilder('form', $user, array('validation_constraint' => $constraint));

  $form = $builder
    ->add('username', 'text', array('label' => 'Username'))
    ->add('passwd', 'password', array('label' => 'Password', 'required' => false))
    ->add('homedir', 'text', array('label' => 'Home dir'))
    ->add('email', 'email', array('label' => 'Email', 'required' => false))
    ->getForm()
  ;

  if ('POST' === $app['request']->getMethod())
  {
    $form->bindRequest($app['request']);

    if ($form->isValid())
    {
      $sql    = "UPDATE users SET username = ?, passwd = ?, homedir = ?, email = ? WHERE id = ?";
      $user   = $app['db']->executeQuery($sql, array(
        $form->get('username')->getData(),
        $form->get('passwd')->getData() === null ? $user['passwd'] : $form->get('passwd')->getData(),
        $form->get('homedir')->getData(),
        $form->get('email')->getData(),
        (int) $id)
      );

      $app['session']->setFlash('notice', 'User saved !');
      return $app->redirect($app['url_generator']->generate('user_edit', array('id' => $id)));
    }
  }

  return $app['twig']->render('user_edit.twig', array('form' => $form->createView(), 'user' => $user, 'active' => 'user_list'));
})->assert('id', '\d+')
  ->bind('user_edit');

// Delete an user
$app->get('/user/{id}/delete', function ($id) use ($app) {
  $sql = "DELETE FROM users WHERE id = ?";
  $app['db']->executeQuery($sql, array((int) $id));

  return $app->redirect($app['url_generator']->generate('user_list'));
})->assert('id', '\d+')
  ->bind('user_delete');

// User listing (edit & delete)
$app->get('/users', function() use ($app) {
  $sql    = "SELECT * FROM users";
  $users  = $app['db']->fetchAll($sql);

  return $app['twig']->render('user_list.twig', array('users' => $users, 'active' => 'user_list'));
})->bind('user_list');


// homepage + statistics
$app->get('/', function() use ($app) {
  $data = array(
    'transfer'       => $app['db']->fetchAssoc("SELECT SUM(transfersize) as size, SUM(transfertime) as time FROM history"),
    'user_active'    => $app['db']->fetchAssoc("SELECT count(id) as count FROM users WHERE valid = 1"),
    'user_inactive'  => $app['db']->fetchAssoc("SELECT count(id) as count FROM users WHERE valid != 1"),
    'nb_connexions'  => $app['db']->fetchAssoc("SELECT SUM(count) as sum FROM users")
  );

  $activities = $app['db']->fetchAll('SELECT id, username, transfertype, transferdate FROM history ORDER BY id DESC LIMIT 0, 10');

  return $app['twig']->render('index.twig', array('data' => $data, 'activities' => $activities, 'active' => 'home'));
})->bind('homepage');

return $app;
