<?php

require_once __DIR__ . '/bootstrap.php';

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormError;

// Add an user
$app->match('/user/add', function () use ($app) {
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
        $sql  = "INSERT INTO `users` (`username`, `passwd`, `fullname`, `homedir`, `email`, `valid`) VALUES (?, ?, ?, ?, ?, 1)";
        $app['db']->insert(
          'users',
          array(
            'username'  => $form->get('username')->getData(),
            'passwd'    => crypt($form->get('passwd')->getData()),
            'fullname'  => $form->get('fullname')->getData(),
            'homedir'   => $form->get('homedir')->getData(),
            'email'     => $form->get('email')->getData()
          )
        );

        $app['session']->setFlash('notice', 'User added !');
        return $app->redirect($app['url_generator']->generate('user_edit', array('id' => $app['db']->lastInsertId())));
      }
    }
  }

  return $app['twig']->render('user_add.twig', array('form' => $form->createView(), 'active' => 'user_add'));
})->bind('user_add');

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
    'username'  => $user['username'],
    'passwd'    => $user['passwd'],
    'homedir'   => $user['homedir'],
    'email'     => $user['email'],
    'valid'     => (bool) $user['valid'],
  );

  $builder = $app['form.factory']->createBuilder('form', $datas, array('validation_constraint' => $constraint));

  $form = $builder
    ->add('username', 'text', array('label' => 'Username'))
    ->add('passwd', 'password', array('label' => 'Password', 'required' => false))
    ->add('homedir', 'text', array('label' => 'Home dir'))
    ->add('email', 'email', array('label' => 'Email', 'required' => false))
    ->add('valid', 'checkbox', array('label' => 'Is valid ?', 'required' => false))
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
        $app['db']->update(
          'users',
          array(
            'username'  => $form->get('username')->getData(),
            'passwd'    => $form->get('passwd')->getData() === null ? $user['passwd'] : $form->get('passwd')->getData(),
            'homedir'   => $form->get('homedir')->getData(),
            'email'     => $form->get('email')->getData(),
            'valid'     => $form->get('valid')->getData(),
          ),
          array('id' => (int) $id)
        );

        $app['session']->setFlash('notice', 'User saved !');
        return $app->redirect($app['url_generator']->generate('user_edit', array('id' => $id)));
      }
    }
  }


  $transfer = $app['db']->fetchAssoc("SELECT COUNT(h.id) as nb, SUM(h.transfersize) as size, SUM(h.transfertime) as time FROM `history` h LEFT JOIN `users` u ON u.username = h.username WHERE u.id = ?", array((int) $id));

  return $app['twig']->render('user_edit.twig', array('form' => $form->createView(), 'user' => $user, 'transfer' => $transfer, 'active' => 'user_list'));
})->assert('id', '\d+')
  ->bind('user_edit');

// Delete an user
$app->get('/user/{id}/delete', function ($id) use ($app) {
  $app['db']->delete('users', array('id' => (int) $id));

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
    'transfer'       => $app['db']->fetchAssoc("SELECT COUNT(id) as nb, SUM(transfersize) as size, SUM(transfertime) as time FROM `history`"),
    'user_active'    => $app['db']->fetchAssoc("SELECT count(id) as count FROM `users` WHERE valid = 1"),
    'user_inactive'  => $app['db']->fetchAssoc("SELECT count(id) as count FROM `users` WHERE valid != 1"),
    'nb_connexions'  => $app['db']->fetchAssoc("SELECT SUM(count) as sum FROM `users`")
  );

  $activities = $app['db']->fetchAll('SELECT id, username, transfertype, transferdate FROM `history` ORDER BY id DESC LIMIT 0, 10');

  return $app['twig']->render('index.twig', array('data' => $data, 'activities' => $activities, 'active' => 'home'));
})->bind('homepage');

return $app;
