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

        $s = isset($_REQUEST['slug']) ?? '';
        if ($s == 1) {
          $s = $_REQUEST['slug'];
        }

        $is_daburi = self::isTyouhuku($s);

        if (!$is_daburi && isset($_REQUEST['slug'], $_REQUEST['password'])) {
            $login = self::register($_REQUEST['slug'], $_REQUEST['user'], $_REQUEST['password']);
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

    private static function isTyouhuku(string $slug_name): bool
    {
        // systemは特殊なユーザーなので登録できない
        if (empty($slug_name) || $slug_name === 'system') {
            return false;
        }

        $user = trim($slug_name);
        $query = "SELECT * FROM `users` WHERE `slug` = ? ";
        $stmt = db()->prepare($query);
        $stmt->bindParam(1, $user, \PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function register($slug, $name, $password): array
    {
        $query = "INSERT INTO `users`(`slug`, `name`) VALUES( ?, ? ); ";
        $stmt = db()->prepare($query);
        $stmt->bindParam(1, $slug, \PDO::PARAM_STR);
        $stmt->bindParam(2, $name, \PDO::PARAM_STR);
        $stmt->execute();

        $id = db()->lastInsertId();
        $query = "INSERT INTO `user_passwords` VALUES( {$id}, ? ); ";
        $stmt = db()->prepare($query);
        $password_h = password_hash($password, PASSWORD_DEFAULT, array('cost' => 10));
        $stmt->bindParam(1, $password_h, \PDO::PARAM_STR);
        $stmt->execute();

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
