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

    public $logger;

    /** csrf */
    private $csrf_manager;
    private $tokenId = '__csrf_token__';

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

    public function setLogger($logger)
    {
        $this->logger = $logger;
        $this->logger->info('start logger');
    }

    public function setSession(\Baguette\Session\SessionInterface $session)
    {
        $this->session = $session;
    }

    public function setCsrfManager(\Symfony\Component\Security\Csrf\CsrfTokenManagerInterface $manager)
    {
        $this->csrf_manager = $manager;
    }

    public function generateToken()
    {
        $csrfToken = $this->csrf_manager->getToken($this->tokenId)->getValue();
        return $csrfToken;
    }

    public function validateToken($string)
    {
        $csrfToken = new \Symfony\Component\Security\Csrf\CsrfToken($this->tokenId, $string);

        $valid = !empty($string) && $this->csrf_manager->isTokenValid($csrfToken);
        if($valid) {
            $this->csrf_manager->refreshToken($this->tokenId);
            return true;
        } else {
            return false;
        }
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
