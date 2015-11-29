<?php
namespace Nyaan\Controller;
use Baguette\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class add_room
{
    function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        $slug = isset($_POST['slug']) ? $_POST['slug'] : '';
        $is_daburi = self::isTyouhuku($slug);
        $is_valid = preg_match('/^[-a-zA-Z]+$/', $slug) == 1;
        if (!$is_daburi && $is_valid && isset($_POST['slug'], $_POST['name'])
            && self::register($_POST['slug'], $_POST['name'], $app->getLoginUser())
        ) {
            $csrf_token = $app->csrf_session->getCsrfToken();
            $csrf_value = $_POST['xsrf_token'];
            if ($csrf_token->isValid($csrf_value)) {
                return new Response\RedirectResponse('/rooms/' . $_POST['slug']);
            }
        }

        return new Response\RedirectResponse('/');
    }

    private static function isTyouhuku(string $slug): bool
    {
        $query = "SELECT * FROM `rooms` WHERE `slug` = ?";
        $stmt = db()->prepare($query, array('text'));
        $stmt->execute(array($slug));
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function register($slug, $name, $user): bool
    {
        $query = "INSERT INTO `rooms`(`slug`, `name`) VALUES( ?, ? )";
        $stmt = db()->prepare($query, array('text'));
        $stmt->execute(array($slug, $name));
        $id = db()->lastInsertId();

        $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
        $user_name = $user->name;
        $message = str_replace('"', '\\"', "**{$user_name}さん**が部屋を作りました！");
        $query = "INSERT INTO `posts` VALUES( ?, 0, ?, ? )";
        $stmt = db()->prepare($query, array('text'));
        $stmt->execute(array($id, $now, $message));

        return true;
    }
}
