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
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
        $slug = filter_input(INPUT_POST, 'slug', FILTER_VALIDATE_REGEXP, ['options' =>
            ['regexp' => '/^[a-zA-Z0-9]+$/']
        ]);

        if (!empty($name) && !empty($slug) && !self::isTyouhuku($slug)
         && self::regist($slug, $name, $app->getLoginUser())
        ) {
            return new Response\RedirectResponse('/rooms/' . $slug);
        }

        return new Response\RedirectResponse('/');
    }

    private static function isTyouhuku(string $slug): bool
    {
        $query = 'SELECT * FROM `rooms` WHERE `slug` = :slug';
        $stmt = db()->prepare($query);
        $stmt->execute([':slug' => $slug]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function regist($slug, $name, $user): bool
    {
        $query = 'INSERT INTO `rooms`(`slug`, `name`) VALUES( :slug, :name )';
        $stmt = db()->prepare($query);
        $stmt->execute([':slug' => $slug, ':name' => $name]);
        $id = db()->lastInsertId();

        $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
        $user_name = $user->name;
        $message = "**{$user_name}さん**が部屋を作りました！";
        $query = 'INSERT INTO `posts` VALUES( :id, 0, :now, :message )';
        $stmt = db()->prepare($query);
        $stmt->execute([':id' => $id, ':now' => $now, ':message' => $message]);

        return true;
    }
}
