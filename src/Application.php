<?php
namespace Nyaan;

/**
 * Nyaan Application
 *
 * @package   Nyaan
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 *
 * @property-read array $server $_SERVER
 * @property-read array $cookie $_COOKIE
 * @property-read array $get    $_GET
 * @property-read array $post   $_POST
 * @proprety-read \Baguette\Session\SessionInterface $session
 */
final class Application extends \Baguette\Application
{
    public function __get($name) { return $this->$name; }

    /** @var \Baguette\Session\SessionInterface */
    private $session;

    /**
     * @param  \Teto\Routing\Action $action
     * @return \Baguette\Response\ResponseInterface
     */
    public function execute(\Teto\Routing\Action $action)
    {
        list($controller_name, $method) = $action->value;
        $controller = "\\Nyaan\\Controller\\$controller_name";
        try {
            $response = (new $controller($this))->$method($action);
        } catch (\Exception $e) {
            $controller = new \Nyaan\Controller\IndexController($this);
            $response = $controller->display500($action);
        }

        return $response;
    }

    public function setSession(\Baguette\Session\SessionInterface $session)
    {
        $this->session = $session;
    }

    public function isLoggedIn(): bool
    {
        return $this->session->get('user_id', ['default' => 0]) > 0;
    }

    public function getLoginUser(): \stdClass
    {
        static $user;
        if (!$user) { $user = new \stdClass; }

        $user->id   = $this->session->get('user_id',   ['default' => 0]);
        $user->slug = $this->session->get('user_slug', ['default' => 0]);
        $user->name = $this->session->get('user_name', ['default' => 0]);

        return $user;
    }

    public static function getRoutingMap(): array
    {
        $routing_map = [
        ];

        return $routing_map;
    }
}
