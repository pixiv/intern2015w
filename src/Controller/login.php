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

        // systemは特殊なユーザーなのでログインできない
        if (isset($_POST['user'], $_POST['password'], $_POST['xsrf_token']) && $_POST['user'] != 'system') {
            $user = trim($_POST['user']);
            $pass = $_POST['password'];
            $query
                = 'SELECT `users`.`user_id`, `users`.`slug`, `users`.`name`, `user_hash_passwords`.`hash_password` '
                . 'FROM `users` '
                . 'INNER JOIN `user_hash_passwords` '
                . '   ON `users`.`user_id` = `user_hash_passwords`.`user_id` '
                . 'WHERE `users`.`slug` = ?';
            $stmt = db()->prepare($query, array('text'));
            $data = array($user);
            $stmt->execute($data);
            $login = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($login) {
                if (password_verify($pass, $login['hash_password'])) {
                    $csrf_token = $app->csrf_session->getCsrfToken();
                    $csrf_value = $_POST['xsrf_token'];
                    if ($csrf_token->isValid($csrf_value)) {
                        $app->session->set('user_id', $login['user_id']);
                        $app->session->set('user_slug', $login['slug']);
                        $app->session->set('user_name', $login['name']);
                        return new Response\RedirectResponse('/');
                    } else {
                        // CSRF failure
                    }
                }
            }
        } else if (!isset($_POST['user'], $_POST['password'])){
            $login = true;
        } else {
            $login = false;
        }
        return new Response\TwigResponse('login.tpl.html', [
            'user' => isset($_POST['user']) ? $_POST['user'] : null,
            'isLoginSuccess' => $login,
            'xsrf_token' => $app->getCsrfTokenValue(),
        ]);
    }
}
