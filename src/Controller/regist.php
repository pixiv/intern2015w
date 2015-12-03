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

        // validate daburi
        if ( isset($app->get['slug']) ){
            $is_daburi = self::isTyouhuku($app->get['slug']);
        } else { $is_daburi = 0; }

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

    private static function isTyouhuku(string $slug): bool
    {
        // systemは特殊なユーザーなので登録できない
        if (empty($slug) || $slug === 'system') {
            return false;
        }

        $user = trim($slug);
        $pass = $_REQUEST['password'];
        $query = "SELECT * FROM `users` WHERE `slug` = \"${user}\" ";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function regist($slug, $name, $password): array
    {
        $query = "INSERT INTO `users`(`slug`, `name`) VALUES( \"{$slug}\", \"{$name}\" ); ";
        $stmt = db()->prepare($query);
        $stmt->execute();

        $query = "SELECT `id` FROM `users` WHERE `slug` = \"{$slug}\"; ";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC); // uniq
        $id = $user['id'];

        $hashed_password =  password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO `user_passwords` VALUES( {$id}, \"{$hashed_password}\" ); ";
        $stmt = db()->prepare($query);
        $stmt->execute();

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
