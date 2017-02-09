<?php
namespace Nyaan\Controller;
use Nyaan\Response\TemplateResponse;
use Baguette\Response\RedirectResponse;

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
        $is_valid = self::isValid($_REQUEST['slug'] ?? '');
        $is_duplicated = self::isDuplicated($_REQUEST['slug'] ?? '');

        if ($is_valid && !$is_duplicated && isset($_REQUEST['slug'], $_REQUEST['name'])
            && self::register($_REQUEST['slug'], $_REQUEST['name'], $app->getLoginUser())
            && $app->validateToken($_REQUEST['csrf_token'] ?? '')
        ) {
            return new RedirectResponse('/rooms/' . $_REQUEST['slug']);
        }

        return new RedirectResponse('/');
    }

    private static function isValid(string $slug): bool
    {

        if (!preg_match('/^[a-zA-Z0-9]+$/', $slug)) {
            return false;
        }

        return true;
    }

    private static function isDuplicated(string $slug): bool
    {
        $query = "SELECT * FROM `rooms` WHERE `slug` = ? ";
        $stmt = db()->prepare($query);
        $stmt->execute([$slug]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function register($slug, $name, $user): bool
    {
        $query = "INSERT INTO `rooms`(`slug`, `name`) VALUES( ?, ? ); ";
        $stmt = db()->prepare($query);
        $stmt->execute([$slug, $name]);
        $id = db()->lastInsertId();

        $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
        $user_name = $user->name;
        $message = "**{$user_name}さん**が部屋を作りました！";
        $query = "INSERT INTO `posts` VALUES( ?, 0, ?, ? )";
        $stmt = db()->prepare($query);
        $stmt->execute([$id, $now, $message]);

        return true;
    }
}
