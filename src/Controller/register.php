<?php
namespace Nyaan\Controller;
use Nyaan\Response\TemplateResponse;
use Baguette\Response\RedirectResponse;

class register
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        if ($app->session->get('user_id', ['default' => false])) {
            return new RedirectResponse('/');
        }

        $is_valid = self::isValid($_REQUEST['slug'] ?? '');
        $is_duplicated = self::isDuplicated($_REQUEST['slug'] ?? '');

        if ($is_valid && !$is_duplicated && isset($_REQUEST['slug'], $_REQUEST['password'])  && $app->validateToken($_REQUEST['csrf_token'] ?? '')) {
            $login = self::register($_REQUEST['slug'], $_REQUEST['user'], $_REQUEST['password']);
            $app->session->set('user_id', $login['id']);
            $app->session->set('user_slug', $login['slug']);
            $app->session->set('user_name', $login['name']);

            return new RedirectResponse('/');
        }

        return new TemplateResponse('register.tpl.html', [
            'user' => $_REQUEST['user'] ?? null,
            'slug' => $_REQUEST['slug'] ?? null,
            'is_valid' => $is_valid,
            'is_duplicated' => $is_duplicated,
        ]);
    }

    private static function isValid(string $user_name): bool
    {
        // systemは特殊なユーザーなので登録できない
        if ($user_name === 'system') {
            return false;
        }

        return true;
    }

    private static function isDuplicated(string $user_name): bool
    {
        $query = "SELECT * FROM `users` WHERE `slug` = ? ";
        $stmt = db()->prepare($query);
        $stmt->execute([$user_name]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function register($slug, $name, $password): array
    {
        $query = "INSERT INTO `users`(`slug`, `name`) VALUES( ?, ? ); ";
        $stmt = db()->prepare($query);
        $stmt->execute([$slug, $name]);

        $id = db()->lastInsertId();
        $query = "INSERT INTO `user_passwords` VALUES( ?, ? ); ";
        $stmt = db()->prepare($query);
        $stmt->execute([$id, $password]);

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
