<?php
namespace Nyaan\Controller;
use Baguette\Response;

// FIXME: そんな英語はない
class regist
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        if ($app->session->get('user_id', ['default' => false])) {
            return new Response\RedirectResponse('/');
        }

        $is_daburi = self::isTyouhuku(isset($_REQUEST['user']) ?? '');

        if (!$is_daburi && isset($_REQUEST['slug'], $_REQUEST['password'])) {
            $login = self::regist($_REQUEST['slug'], $_REQUEST['user'], $_REQUEST['password']);
            $app->session->set('user_id', $login['id']);
            $app->session->set('user_slug', $login['slug']);
            $app->session->set('user_name', $login['name']);

            return new Response\RedirectResponse('/');
        }

        return new Response\TwigResponse('regist.tpl.html', [
            'user' => isset($_REQUEST['user']) ? $_REQUEST['user'] : null,
            'is_daburi' => $is_daburi,
        ]);
    }

    private static function isTyouhuku(string $user_name): bool
    {
        // systemは特殊なユーザーなので登録できない
        if (empty($user_name) || $user_name === 'system') {
            return false;
        }

        $user = trim($user_name);
        $pass = $_REQUEST['password'];
        $query = 'SELECT * FROM `users` WHERE `slug` = ?;';
        $stmt = db()->prepare($query);
        $stmt->execute([$user]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function regist($slug, $name, $password): array
    {
        $query = 'INSERT INTO `users`(`slug`, `name`) VALUES(?, ?);';
        $stmt = db()->prepare($query);
        $stmt->execute([$slug, $name]);

        $id = db()->lastInsertId();
        $query = "INSERT INTO `user_passwords` VALUES(?, ?); ";
        $stmt = db()->prepare($query);
        $stmt->execute([$id, $password]);

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
