<?php

require_once __DIR__.'/../vendor/silex.phar';

$app = new Silex\Application();

require __DIR__ . '/config.php';

use Silex\Provider\SymfonyBridgesServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TranslationServiceProvider;

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new TranslationServiceProvider(), array(
  'translation.class_path'  => __DIR__.'/../vendor/Symfony/src',
  'translator.messages'     => array()
));

$app->register(new Silex\Provider\SymfonyBridgesServiceProvider(), array(
  'symfony_bridges.class_path'  => __DIR__.'/../vendor/Symfony/src',
));

$app->register(new FormServiceProvider(), array(
  'form.class_path'   => __DIR__ . '/../vendor/Symfony/src'
));

// Register Symfony Validator component extension
$app->register(new ValidatorServiceProvider(), array(
  'validator.class_path'  => __DIR__ . '/../vendor/Symfony/src'
));

// Register Twig extension
$app->register(new TwigServiceProvider(), array(
  'twig.class_path'    => __DIR__ . '/../vendor/twig/lib',
  'twig.path'          => __DIR__ . '/../views',
  'twig.options'       => array('strict_variables' => false)
));

require_once __DIR__.'/customTwigFilter.php';

// Register Monolog extension
$app->register(new MonologServiceProvider(), array(
  'monolog.class_path'    => __DIR__ . '/../vendor/monolog/src',
  'monolog.logfile'       => __DIR__ . '/../log/'.$app['monolog.filename'].'.log',
  'monolog.name'          => $app['monolog.filename'],
  'monolog.level'         => $app['monolog.loglevel']
));

// Register UrlGenerator
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Register Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'db.options'  => array(
    'driver'  => $app['db.config.driver'],
    'host'    => $app['db.config.host'],
    'dbname'  => $app['db.config.dbname'],
    'user'    => $app['db.config.user'],
    'password'=> $app['db.config.password']
  ),
  'db.dbal.class_path'    => __DIR__.'/../vendor/Doctrine/dbal/lib',
  'db.common.class_path'  => __DIR__.'/../vendor/Doctrine/common/lib',
));

// Application error handling
$app->error(function(\Exception $e) use ($app)
{
  if ($e instanceof NotFoundHttpException)
  {
    $content = sprintf('<h1>%d - %s (%s)</h1>',
      $e->getStatusCode(),
      Response::$statusTexts[$e->getStatusCode()],
      $app['request']->getRequestUri()
    );
    return new Response($content, $e->getStatusCode());
  }

  if ($e instanceof HttpException)
  {
    return new Response('<h1>Oops!</h1><h2>Something went wrong...</h2><p>You should go eat some cookies while we\'re fixing this feature!</p>', $e->getStatusCode());
  }
});
