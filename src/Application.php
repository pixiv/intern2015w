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

final class InvalidAuthenticityToken extends \Exception {}

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

    public function getAuthenticityToken(): string
    {
        return $this->session->get('authenticity_token', ['default' => 'null']);
    }

    public function setAuthenticityToken(): string
    {
        $token = base64_encode(random_bytes(64));
        $this->session->set('authenticity_token', $token);
        return $token;
    }

    // XXX: accessing $_POST is bad idea?
    public function verifyAuthenticityToken(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET'
            || $_SERVER['REQUEST_METHOD'] === 'HEAD'
        )
            return true;

        if (isset($_POST['authenticity_token'])
            && $_POST['authenticity_token'] === self::getAuthenticityToken()
        ) {
            self::setAuthenticityToken();
            return true;
        } else {
            throw new InvalidAuthenticityToken();
        }
    }

    public static function getRoutingMap(): array
    {
        $routing_map = [
        ];

        return $routing_map;
    }
}
