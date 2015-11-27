<?php
namespace Nyaan\Controller;
use PDO;
use Baguette\Response;
use Nyaan\Response\TemplateResponse;

// FIXME: そんな英語はない
class regist
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        if ($app->isLoggedIn())
            return new Response\RedirectResponse('/');

        $is_unique = true;

        if (isset($_POST['slug'], $_POST['user'], $_POST['password'])
            && $app->verifyAuthenticityToken()
            && $is_unique = self::isUnique($_POST['slug'])
            && self::createUser($_POST['slug'], $_POST['user'], $_POST['password'])
        ) {
            $app->setLoginUser($_POST['slug']);
            return new Response\RedirectResponse('/');
        }

        return new TemplateResponse('regist.tpl.html', [
            'user' => isset($_POST['user']) ? $_POST['user'] : null,
            'is_daburi' => !$is_unique,
        ]);
    }

    private static function createUser($slug, $name, $pass): bool
    {
        if (!self::isUnique($slug))
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

    private static function isUnique(string $user_slug): bool
    {
        $slug = trim($user_slug);
        $query = 'SELECT * FROM `users` WHERE `slug` = ? OR `name` = ?;';
        $stmt = db()->prepare($query);
        $stmt->execute([$slug]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return empty($data);
    }
}
