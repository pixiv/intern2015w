<?php
namespace Nyaan\Controller;
use Baguette\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */

 // XSS対策のため特殊文字をエスケープする関数
  function h($str)
 {
     return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
 }

final class add_room
{
    function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        $is_daburi = self::isTyouhuku(isset($_REQUEST['slug']) ?? '');
        $is_daburi = h($is_daburi);

        if (!$is_daburi && isset($_REQUEST['slug'], $_REQUEST['name'])
            && self::regist($_REQUEST['slug'], $_REQUEST['name'], $app->getLoginUser())
        ) {
            return new Response\RedirectResponse('/rooms/' . $_REQUEST['slug']);
        }

        return new Response\RedirectResponse('/');
    }

    private static function isTyouhuku(string $slug): bool
    {
        $slug = h($slug);
        $query = "SELECT * FROM `rooms` WHERE `slug` = \"${slug}\" ";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return !empty($data);
    }

    private static function regist($slug, $name, $user): bool
    {
        $name = h($name);
        $slug = h($slug);
        $query = "INSERT INTO `rooms`(`slug`, `name`) VALUES( \"{$slug}\", \"{$name}\" ); ";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $id = db()->lastInsertId();

        $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
        $user_name = $user->name;
        $message = str_replace('"', '\\"', "**{$user_name}さん**が部屋を作りました！");
        $query = "INSERT INTO `posts` VALUES( {$id}, 0, \"{$now}\", \"{$message}\" )";
        $stmt = db()->prepare($query);
        $stmt->execute();

        return true;
    }
}
