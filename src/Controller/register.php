<?php
namespace Nyaan\Controller;
use Baguette\Response;

class register
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        if ($app->session->get('user_id', ['default' => false])) {
            return new Response\RedirectResponse('/');
        }

        $is_daburi = self::isTyouhuku(isset($_REQUEST['user']) ?? '');

        if (!$is_daburi && isset($_REQUEST['slug'], $_REQUEST['password'])) {
            $login = self::register($_REQUEST['slug'], $_REQUEST['user'], $_REQUEST['password']);
            $app->session->set('user_id', $login['user_id']);
            $app->session->set('user_slug', $login['slug']);
            $app->session->set('user_name', $login['name']);

            return new Response\RedirectResponse('/');
        }

        return new Response\TwigResponse('register.tpl.html', [
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
        $query = "SELECT * FROM `users` WHERE `slug` = \"${user}\" ";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function register($slug, $name, $password): array
    {
        $query = "INSERT INTO `users`(`slug`, `name`) VALUES( ?, ? )";
        $stmt = db()->prepare($query, array('text'));
        $stmt->execute(array($slug, $name));

        $user_id = db()->lastInsertId();
        $query = "INSERT INTO `user_hash_passwords` VALUES( ?, ? )";
        $hash_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = db()->prepare($query, array('text'));
        $stmt->execute(array($user_id, $hash_password));

        return [
            'user_id' => $user_id,
            'name'    => $name,
            'slug'    => $slug,
        ];
    }
}
