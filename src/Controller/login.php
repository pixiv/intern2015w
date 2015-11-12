<?php
namespace Nyaan\Controller;
use Nyaan\Response\TemplateResponse;
use Baguette\Response\RedirectResponse;

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
            return new RedirectResponse('/');
        }

        // systemは特殊なユーザーなのでログインできない
        if (isset($_REQUEST['user'], $_REQUEST['password']) && $_REQUEST['user'] != 'system' && $app->validateToken($_REQUEST['csrf_token'] ?? '')) {
            $user = trim($_REQUEST['user']);
            $pass = $_REQUEST['password'];
            $query
                = 'SELECT `users`.`id`, `users`.`slug`, `users`.`name` '
                . 'FROM `users` '
                . 'INNER JOIN `user_passwords` '
                . '   ON `users`.`id` = `user_passwords`.`user_id` '
                . "WHERE `users`.`slug` = ? "
                . "  AND `user_passwords`.`password` = ? ";
            $stmt = db()->prepare($query);
            $stmt->execute([$user, $pass]);

            if ($login = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $app->session->set('user_id', $login['id']);
                $app->session->set('user_slug', $login['slug']);
                $app->session->set('user_name', $login['name']);
                return new RedirectResponse('/');
            }
        }

        return new TemplateResponse('login.tpl.html', [
            'user' => isset($_REQUEST['user']) ? $_REQUEST['user'] : null,
        ]);
    }
}
