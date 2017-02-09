<?php
namespace Nyaan;
use Nyaan\Application;

require __DIR__ . '/../vendor/autoload.php';

error_reporting(-1);

try {
call_user_func(function(){
    mb_internal_encoding("UTF-8");
    $dotenv = new \Dotenv\Dotenv(dirname(__DIR__));
    $dotenv->overload();
    $dotenv->required('DB_DSN')->notEmpty();

    $routing_map = [
        'logout'   => ['GET',  '/logout',      'logout'],
        'login'    => ['GET',  '/login',       'login'],
                      ['POST',  '/login',       'login'],
        'register' => ['GET',  '/register',    'register'],
                      ['POST', '/register',    'register'],
        'room'     => ['GET',  '/rooms/:slug', 'room', ['slug' => '/[-a-zA-Z]+/']],
                      ['POST', '/rooms/:slug', 'room', ['slug' => '/[-a-zA-Z]+/']],
        'add_romm' => ['POST', '/add_room',    'add_room'],
        'user'     => ['GET',  '/:user',       'user', ['user' => '/@[-a-zA-Z]+/']],
        'index'    => ['GET',  '/',            'top'],
        '#404'     =>                          'fileloader',
    ];

    $now = new \DateTimeImmutable;
    $app = new Application($_SERVER, $_COOKIE, $_GET, $_POST, $now);

    $basedir = dirname(__DIR__);
    $twig_option = [
        'cache' => $basedir . '/cache/twig',
        'debug' => true,
    ];
    $loader = new \Twig_Loader_Filesystem($basedir . '/src/View/twig');
    \Nyaan\Response\TemplateResponse::setTwigEnvironment(new \Twig_Environment($loader, $twig_option));

    ini_set('session.save_path', $basedir . '/cache/session');
    $session = new \Baguette\Session\PhpSession;
    $app->setSession($session);
    $session->start();

    $path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['PHP_SELF'];
    $path = ($path === '/index.php') ? '/' : $path;
    $router = new \Teto\Routing\Router($routing_map);
    $action = $router->match($_SERVER['REQUEST_METHOD'], $path);

    $controller = '\\Nyaan\\Controller\\' . $action->value;
    $response = (new $controller)->action($app, $action);

    echo $app->renderResponse($response);
});
} catch (\Exception $e) {
    var_dump($e);
}
