<?php
namespace Nyaan\Controller;
use Baguette\Response;

// FIXME: そんな英語はない
class register
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        if ($app->session->get('user_id', ['default' => false])) {
            return new Response\RedirectResponse('/');
        }

        $slug = NULL;
        if (isset($_REQUEST['slug'])) {
            preg_match('/[-a-zA-Z0-9]+/', $_REQUEST['slug'], $matches);
            if (count($matches) > 0) {
                $slug = $matches[0];
            }
        }
        $is_daburi = $slug === NULL || self::isTyouhuku($slug);

        if (!$is_daburi && isset($_REQUEST['slug'], $_REQUEST['password'])) {
            $token = $app->session->get('token', ['default' => false]);
            if (isset($_REQUEST['token']) && $_REQUEST['token'] === $token) {
                $app->session->set('token', NULL);
                $login = self::register($_REQUEST['slug'], $_REQUEST['user'], $_REQUEST['password']);
                $app->session->set('user_id', $login['id']);
                $app->session->set('user_slug', $login['slug']);
                $app->session->set('user_name', $login['name']);

                return new Response\RedirectResponse('/');
            }
        }

        $token = csrf_token();
        $app->session->set('token', $token);

        return new Response\TwigResponse('register.tpl.html', [
            'user' => isset($_REQUEST['user']) ? $_REQUEST['user'] : null,
            'is_daburi' => $is_daburi,
            'token' => $token
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
        $query = 'SELECT * FROM `users` WHERE `slug` = ?';
        $stmt = db()->prepare($query);
        $stmt->execute([$user]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function register($slug, $name, $password): array
    {
        $query = 'INSERT INTO `users` (`slug`, `name`) VALUES(?, ?)';
        $stmt = db()->prepare($query);
        $stmt->execute([$slug, $name]);

        $id = db()->lastInsertId();
        $query = 'INSERT INTO `user_passwords` VALUES(?, ?)';
        $stmt = db()->prepare($query);
        $stmt->execute([$id, password($password)]);

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
