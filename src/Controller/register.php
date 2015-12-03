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

        $is_daburi = self::isTyouhuku($_REQUEST['slug'] ?? '');

        if (!isset($_REQUEST['user'], $_REQUEST['slug'], $_REQUEST['password'], $_REQUEST['password_confirmation'])) {
            return new Response\TwigResponse('register.tpl.html', [
              'user' => isset($_REQUEST['user']) ? $_REQUEST['user'] : null,
              'is_daburi' => $is_daburi,
            ]);
        }

        if ($_REQUEST['password'] != $_REQUEST['password_confirmation']) {
            return new Response\TwigResponse('register.tpl.html', [
              'user' => isset($_REQUEST['user']) ? $_REQUEST['user'] : null,
              'is_daburi' => $is_daburi,
            ]);
        }

        if (!$is_daburi) {
            $password_hash = password_hash($_REQUEST['password'], PASSWORD_DEFAULT);
            $login = self::register($_REQUEST['slug'], $_REQUEST['user'], $password_hash);
            $app->session->set('user_id', $login['id']);
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
            return true;
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
        $query = "INSERT INTO `users`(`slug`, `name`) VALUES( \"{$slug}\", \"{$name}\" ); ";
        $stmt = db()->prepare($query);
        $stmt->execute();

        $id = db()->lastInsertId();
        $query = "INSERT INTO `user_passwords` VALUES( {$id}, \"{$password}\" ); ";
        $stmt = db()->prepare($query);
        $stmt->execute();

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
