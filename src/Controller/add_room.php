<?php
namespace Nyaan\Controller;
use Nyaan\Response;

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
        $slug = NULL;
        if (isset($_REQUEST['slug'])) {
            preg_match('/[-a-zA-Z0-9]+/', $_REQUEST['slug'], $matches);
            if (count($matches) > 0) {
                $slug = $matches[0];
            }
        }
        $is_daburi = $slug === NULL || self::isTyouhuku($slug);

        $token = $app->session->get('token', ['default' => false]);
        if (!$is_daburi
            && isset($_REQUEST['slug'], $_REQUEST['name'], $_REQUEST['token'])
            && $_REQUEST['token'] === $token
            && self::register($_REQUEST['slug'], $_REQUEST['name'], $app->getLoginUser())
        ) {
            $app->session->set('token', NULL);
            return new Response\RedirectResponse('/rooms/' . $_REQUEST['slug']);
        }

        return new Response\RedirectResponse('/');
    }

    private static function isTyouhuku(string $slug): bool
    {
        $query = 'SELECT * FROM `rooms` WHERE `slug` = ?';
        $stmt = db()->prepare($query);
        $stmt->execute([$slug]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function register($slug, $name, $user): bool
    {
        $query = 'INSERT INTO `rooms` (`slug`, `name`) VALUES(?, ?)';
        $stmt = db()->prepare($query);
        $stmt->execute([$slug, $name]);
        $id = db()->lastInsertId();

        $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
        $user_name = $user->name;
        $message = str_replace('"', '\\"', "**{$user_name}さん**が部屋を作りました！");
        $query = 'INSERT INTO `posts` VALUES(?, 0, ?, ?)';
        $stmt = db()->prepare($query);
        $stmt->execute([$id, $now, $message]);

        return true;
    }
}
