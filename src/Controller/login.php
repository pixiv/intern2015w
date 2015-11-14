<?php
namespace Nyaan\Controller;
use Baguette\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class login
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        if ($app->session->get('user_id', ['default' => false])) {
            return new Response\RedirectResponse('/');
        }

        $token = $app->session->get('token', ['default' => false]);
        if (!isset($_REQUEST['token']) || $_REQUEST['token'] !== $token) {
            return new Response\RedirectResponse('/');
        }
        $app->session->set('token', NULL);

        // systemは特殊なユーザーなのでログインできない
        if (isset($_REQUEST['user'], $_REQUEST['password']) && $_REQUEST['user'] != 'system') {
            $user = trim($_REQUEST['user']);
            $pass = $_REQUEST['password'];
            $query = 'SELECT * FROM `users` WHERE `slug` = ?';
            $stmt = db()->prepare($query);
            $stmt->execute([$user]);

            if ($login = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $query = 'SELECT `password` FROM `user_passwords` WHERE `user_id` = ?';
                $stmt = db()->prepare($query);
                $stmt->execute([$login['id']]);
                $res = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($res && password($pass, $res['password']) === true) {
                    $app->session->set('user_id', $login['id']);
                    $app->session->set('user_slug', $login['slug']);
                    $app->session->set('user_name', $login['name']);
                    return new Response\RedirectResponse('/');
                }
            }
        }

        $token = csrf_token();
        $app->session->set('token', $token);

        return new Response\TwigResponse('login.tpl.html', [
            'user' => isset($_REQUEST['user']) ? $_REQUEST['user'] : null,
            'token' => $token
        ]);
    }
}
