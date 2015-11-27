<?php
namespace Nyaan\Controller;
use PDO;
use Baguette\Response;
use Nyaan\Response\TemplateResponse;

class register
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        if ($app->isLoggedIn())
            return new Response\RedirectResponse('/');

        $is_unique = true;

        if (isset($app->post['slug'], $app->post['user'], $app->post['password'])
            && $app->verifyAuthenticityToken()
            && $is_unique = self::isUnique($app->post['slug'])
            && self::createUser($app->post['slug'], $app->post['user'], $app->post['password'])
        ) {
            $app->setLoginUser($app->post['slug']);
            return new Response\RedirectResponse('/');
        }

        return new TemplateResponse('register.tpl.html', [
            'user' => isset($app->post['user']) ? $app->post['user'] : null,
            'is_daburi' => !$is_unique,
        ]);
    }

    private static function createUser($slug, $name, $pass): bool
    {
        if (!self::isUnique($slug))
            return false;

        if (preg_match('/\A[a-zA-Z0-9]+\z/', $slug) !== 1)
            return false;

        $query = array(
            'create_user' => 'INSERT INTO `users`(`slug`, `name`) VALUES(:slug, :name);',
            'create_pass' => 'INSERT INTO `user_passwords` VALUES(:uid, :pass);'
        );

        try {
            db()->beginTransaction();

            $stmt = db()->prepare($query['create_user']);
            $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->execute();

            $uid = db()->lastInsertId();

            $stmt = db()->prepare($query['create_pass']);
            $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
            $stmt->bindValue(':pass', password_hash($pass, PASSWORD_DEFAULT), PDO::PARAM_STR);
            $stmt->execute();
            db()->commit();
        } catch (PDOException $e) {
            db()->rollback();
            return false;
        }

        return true;
    }

    private static function isUnique(string $slug): bool
    {
        $slug = trim($slug);
        $query = 'SELECT * FROM `users` WHERE `slug` = :slug;';
        $stmt = db()->prepare($query);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return empty($data);
    }
}
