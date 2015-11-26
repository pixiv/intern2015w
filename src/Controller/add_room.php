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
        $is_daburi = self::isTyouhuku(isset($_REQUEST['slug']) ?? '');

        if (!$is_daburi && isset($_REQUEST['slug'], $_REQUEST['name'])
            && self::register($_REQUEST['slug'], $_REQUEST['name'], $app->getLoginUser())
        ) {
            return new Response\RedirectResponse('/rooms/' . $_REQUEST['slug']);
        }

        return new Response\RedirectResponse('/');
    }

    private static function isTyouhuku(string $slug): bool
    {
        $query = "SELECT * FROM `rooms` WHERE `slug` = ? ";
        $stmt = db()->prepare($query);
        $stmt->bindParam(1, $slug, \PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function register($slug, $name, $user): bool
    {
        $query = "INSERT INTO `rooms`(`slug`, `name`) VALUES( ?, ? ); ";
        $stmt = db()->prepare($query);
        $stmt->bindParam(1, $slug, \PDO::PARAM_STR);
        $stmt->bindParam(2, $name, \PDO::PARAM_STR);
        $stmt->execute();
        $id = db()->lastInsertId();

        $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
        $user_name = $user->name;
        $message = str_replace('"', '\\"', "**{$user_name}さん**が部屋を作りました！");
        $query = "INSERT INTO `posts` VALUES( ?, 0, ?, ? )";
        $stmt = db()->prepare($query);
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->bindParam(2, $now, \PDO::PARAM_STR);
        $stmt->bindParam(3, $message, \PDO::PARAM_STR);
        $stmt->execute();

        return true;
    }
}
