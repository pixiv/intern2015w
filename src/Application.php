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

    public function setLoginUser(string $user_slug): bool
    {
        $query = 'SELECT `users`.`id`, `users`.`slug`, `users`.`name` '
                 . 'FROM `users` WHERE `users`.`slug` = ?';
        $stmt = db()->prepare($query);
        $stmt->execute([$user_slug]);

        if ($login = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->session->set('user_id', $login['id']);
            $this->session->set('user_slug', $login['slug']);
            $this->session->set('user_name', $login['name']);
            return true;
        }

        return false;
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

    public function verifyAuthenticityToken(): bool
    {
        if ($this->server['REQUEST_METHOD'] === 'GET'
            || $this->server['REQUEST_METHOD'] === 'HEAD'
        )
            return true;

        if (isset($this->post['authenticity_token'])
            && $this->post['authenticity_token'] === self::getAuthenticityToken()
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
