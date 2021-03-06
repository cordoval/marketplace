<?php

require_once __DIR__.'/../vendor/silex/autoload.php';
require_once __DIR__.'/../vendor/lightopenid/openid.php';

$app = new Silex\Application();

$app['autoloader']->registerNamespaces(array(
    'Marketplace'      => __DIR__.'/../src',
    'Symfony'          => __DIR__.'/../vendor/',
    'Panda'            => __DIR__.'/../vendor/SilexDiscountServiceProvider/src',
    'Knp'              => __DIR__.'/../vendor/KnpSilexExtensions/'
));

/** Silex Extensions */
use Silex\Provider\SymfonyBridgesServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Panda\DiscountServiceProvider;
use Knp\Provider\RepositoryServiceProvider;

/** Twig Extensions */
use Marketplace\Twig\MarketplaceExtension;

$app->register(new SymfonyBridgesServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new DiscountServiceProvider());
$app->register(new ValidatorServiceProvider());

$app->register(new DoctrineServiceProvider(), array(
    'db.dbal.class_path'    => __DIR__.'/../vendor/silex/vendor/doctrine-dbal/lib',
    'db.common.class_path'  => __DIR__.'/../vendor/silex/vendor/doctrine-common/lib',
));

$app->register(new TranslationServiceProvider(), array(
  'translator.messages' => array()
));

$app->register(new TwigServiceProvider(), array(
    'twig.path' => array(
        __DIR__.'/../src/Resources/views',
        __DIR__.'/../vendor/Symfony/Bridge/Twig/Resources/views/Form',
    ),
    'twig.class_path' => __DIR__.'/../vendor/silex/vendor/twig/lib',
));

$app->register(new RepositoryServiceProvider(), array('repository.repositories' => array(
    'projects'      => 'Marketplace\\Repository\\Project',
    'comments'      => 'Marketplace\\Repository\\Comment',
    'project_votes' => 'Marketplace\\Repository\\ProjectVote',
    'project_links' => 'Marketplace\\Repository\\ProjectLink',
)));

if (!file_exists(__DIR__.'/config.php')) {
    throw new RuntimeException('You must create your own configuration file ("src/config.php"). See "src/config.php.dist" for an example config file.');
}

require_once __DIR__.'/config.php';

/** Marketplace providers */
$app->register(new \Marketplace\Provider\Service\Security());
$app->register(new \Marketplace\Provider\Service\Migration());

$app->before(function() use ($app) {
    $app['twig']->addGlobal('categories', $app['project.categories']);
    $app['twig']->addExtension(new MarketplaceExtension($app));
});

return $app;
