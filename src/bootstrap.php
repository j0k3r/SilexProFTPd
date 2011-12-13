<?php

require_once __DIR__.'/../vendor/silex.phar';

$app = new Silex\Application();
$app['debug'] = true;

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\MonologServiceProvider;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Register Symfony Validator component extension
$app->register(new ValidatorServiceProvider(), array(
  'validator.class_path'   => __DIR__ . '/../vendor/Symfony/src'
));

// Register Twig extension
$app->register(new TwigServiceProvider(), array(
  'twig.class_path'    => __DIR__ . '/../vendor/Twig/lib',
  'twig.path'          => __DIR__ . '/../views',
  'twig.options'       => array('strict_variables' => false)
));

require_once __DIR__.'/customTwigFilter.php';

// Register Monolog extension
$app->register(new MonologServiceProvider(), array(
  'monolog.class_path'    => __DIR__ . '/../vendor/monolog/src',
  'monolog.logfile'       => __DIR__ . '/../log/silexproftpd.log',
  'monolog.name'          => 'silexproftpd',
  'monolog.level'         => 300
));

// Register UrlGenerator
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Register Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'db.options'  => array(
    'driver'  => 'pdo_mysql',
    'host'    => 'localhost',
    'dbname'  => 'ftp',
    'user'    => 'root',
    'password'=> 'root'
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
