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
        if (isset($app->post['user'], $app->post['password']) && $app->post['user'] != 'system') {
            $user = trim($app->post['user']);
            $pass = $app->post['password'];
            $query
                = 'SELECT `users`.`id`, `users`.`slug`, `users`.`name`, `user_passwords`.`password` '
                . 'FROM `users` '
                . 'INNER JOIN `user_passwords` '
                . '   ON `users`.`id` = `user_passwords`.`user_id` '
                . "WHERE `users`.`slug` = \"${user}\" ";
            // . "  AND `user_passwords`.`password` = \"${pass}\" ";
            $stmt = db()->prepare($query);
            $stmt->execute();

            if ($login = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (password_verify($pass, $login['password'])) {
                    $app->session->set('user_id', $login['id']);
                    $app->session->set('user_slug', $login['slug']);
                    $app->session->set('user_name', $login['name']);
                    return new Response\RedirectResponse('/');
                }
            }
        }

        return new Response\TwigResponse('login.tpl.html', [
            'user' => isset($app->post['user']) ? $app->post['user'] : null,
            ]);
    }
}
