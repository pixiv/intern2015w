<?php
namespace Nyaan\Controller;
use Baguette\Response;
use Nyaan\Response\TemplateResponse;

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
        // systemは特殊なユーザーなのでログインできない
        if (!$app->isLoggedIn()
            && isset($app->post['user'], $app->post['password'])
            && $app->verifyAuthenticityToken()
            && $app->post['user'] !== 'system'
        ) {
            $slug = trim($app->post['user']);
            $pass = $app->post['password'];

            if (self::verifyPassword($slug, $pass)) {
                $app->setLoginUser($slug);
            } else {
                return new TemplateResponse('login.tpl.html', [
                    'user' => $app->post['user'] ?? null,
                ]);
            }
        }

        return new Response\RedirectResponse('/');
    }

    private static function verifyPassword(string $slug, string $pass)
    {
        $query = 'SELECT `users`.`id` FROM `users` WHERE `users`.`slug` = :slug;';
        $stmt = db()->prepare($query);
        $stmt->bindValue(':slug', $slug, \PDO::PARAM_STR);
        $stmt->execute();

        if ($input = $stmt->fetch(\PDO::FETCH_ASSOC))
            $uid = $input['id'];
        else
            $uid = 0;

        $query = 'SELECT `password` FROM `user_passwords` WHERE `user_id` = :uid;';
        $stmt = db()->prepare($query);
        $stmt->bindValue(':uid', $uid, \PDO::PARAM_INT);
        $stmt->execute();

        if ($input = $stmt->fetch(\PDO::FETCH_ASSOC))
            $pass_db = $input['password'];
        else
            $pass_db = null;

        return password_verify($pass, $pass_db);
    }
}
