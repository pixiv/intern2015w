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

        $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_SPECIAL_CHARS);
        $slug = filter_input(INPUT_POST, 'slug', FILTER_VALIDATE_REGEXP, ['options' =>
            ['regexp' => '/^[a-zA-Z0-9]+$/']
        ]);
        $password = filter_input(INPUT_POST, 'password');

        if (!empty($user) && !empty($slug) && !empty($password) && !self::isTyouhuku($user)) {
            $login = self::regist($slug, $user, $password);
            $app->session->set('user_id', $login['id']);
            $app->session->set('user_slug', $login['slug']);
            $app->session->set('user_name', $login['name']);

            return new Response\RedirectResponse('/');
        }

        return new Response\TwigResponse('regist.tpl.html', [
            'user' => $user ?? null,
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
        $query = 'SELECT * FROM `users` WHERE `slug` = :user';
        $stmt = db()->prepare($query);
        $stmt->execute([':user' => $user]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function regist($slug, $name, $password): array
    {
        $query = 'INSERT INTO `users`(`slug`, `name`) VALUES( :slug, :name )';
        $stmt = db()->prepare($query);
        $stmt->execute([':slug' => $slug, ':name' => $name]);

        $id = db()->lastInsertId();
        $query = 'INSERT INTO `user_passwords` VALUES( :id, :password )';
        $stmt = db()->prepare($query);
        $stmt->execute([':id' => $id, ':password' => $password]);

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
