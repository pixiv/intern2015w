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
        $message = "";
        $slug = isset($_POST['slug']) ? $_POST['slug'] : '';
        $is_duplicated = self::isDuplicated($slug);
        $is_valid = preg_match('/^[-a-zA-Z0-9]+$/', $slug) == 1;
        if (!$is_duplicated && $is_valid && isset($_POST['slug'], $_POST['password'], $_POST['re_password'])) {
            if ($_POST['password'] === $_POST['re_password']) {
                $csrf_token = $app->csrf_session->getCsrfToken();
                $csrf_value = $_POST['xsrf_token'];
                if ($csrf_token->isValid($csrf_value)) {
                    $login = self::register($_POST['slug'], $_POST['user'], $_POST['password']);
                    $app->session->set('user_id', $login['user_id']);
                    $app->session->set('user_slug', $login['slug']);
                    $app->session->set('user_name', $login['name']);
                    return new Response\RedirectResponse('/');
                }
            } else {
                $message = "正しくパスワードを入力してください";
            }
        } else if ($is_duplicated && !empty($_POST['slug'])) {
            $message = "既にそのユーザーは登録されています";
        }
        return new Response\TwigResponse('register.tpl.html', [
            'user' => isset($_POST['user']) ? $_POST['user'] : null,
            'message' => $message,
            'xsrf_token' => $app->getCsrfTokenValue(),
        ]);
    }

    private static function isDuplicated(string $user_name): bool
    {
        // systemは特殊なユーザーなので登録できない
        if (empty($user_name) || $user_name === 'system') {
            return true;
        }

        $user = trim($user_name);
        $query = "SELECT * FROM `users` WHERE `slug` = ?";
        $stmt = db()->prepare($query, array('text'));
        $stmt->execute(array($user));
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
