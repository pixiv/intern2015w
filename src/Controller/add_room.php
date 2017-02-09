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
            && self::regist($_REQUEST['slug'], $_REQUEST['name'], $app->getLoginUser())
        ) {
            return new Response\RedirectResponse('/rooms/' . $_REQUEST['slug']);
        }

        return new Response\RedirectResponse('/');
    }

    private static function isTyouhuku(string $slug): bool
    {
        $query = "SELECT * FROM `rooms` WHERE `slug` = :slug ";
        $stmt = db()->prepare($query);
        $stmt->bindValue(':slug', $slug, \PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function regist($slug, $name, $user): bool
    {
        $query = "INSERT INTO `rooms`(`slug`, `name`) VALUES( :slug, :name ); ";
        $stmt = db()->prepare($query);
        $stmt->bindValue(':slug', $slug, \PDO::PARAM_STR);
        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();
        $id = db()->lastInsertId();

        $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
        $user_name = $user->name;
        $message = str_replace('"', '\\"', "**{$user_name}さん**が部屋を作りました！");
        $query = "INSERT INTO `posts` VALUES( {$id}, 0, \"{$now}\", :message )";
        $stmt = db()->prepare($query);
        $stmt->bindValue(':message', $message, \PDO::PARAM_STR);
        $stmt->execute();

        return true;
    }
}
