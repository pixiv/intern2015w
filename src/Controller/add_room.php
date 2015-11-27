<?php
namespace Nyaan\Controller;
use Baguette\Response\RedirectResponse;
use PDO;

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
        if (!$app->isLoggedIn())
            return new RedirectResponse('login');

        if (isset($app->post['slug'], $app->post['name'])
            && $app->verifyAuthenticityToken()
            && self::createRoom($app->post['slug'], $app->post['name'], $app->getLoginUser()))
            return new RedirectResponse('/rooms/' . $app->post['slug']);

        return new RedirectResponse('/');
    }

    /**
     * create new room
     *
     * @param string room_slug 作成するroomのslug
     * @param string room_name 作成するroomのname
     * @param ? admin_user 作成するroomのadmin
     * @return bool 成功したならtrue, 失敗したなら false
     */
    private static function createRoom(
        string $room_slug,
        string $room_name,
        $user): bool
    {
        if (!self::isUnique($room_slug))
            return false;

        if (preg_match('/\A[a-zA-Z0-9]+\z/', $room_slug) !== 1)
            return false;

        $query = array(
            'create_room' => 'INSERT INTO `rooms`(`slug`, `name`) VALUES(:slug, :name);',
            'system_post' => 'INSERT INTO `posts` VALUES(:id, 0, :date, :message);'
        );

        try {
            db()->beginTransaction();

            $stmt = db()->prepare($query['create_room']);
            $stmt->bindValue(':slug', $room_slug, PDO::PARAM_STR);
            $stmt->bindValue(':name', $room_name, PDO::PARAM_STR);
            $stmt->execute();

            $room_id = db()->lastInsertId();
            $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
            $message = "**{$user->name}さん**が部屋を作りました！";

            $stmt = db()->prepare($query['system_post']);
            $stmt->bindValue(':id', $room_id, PDO::PARAM_INT);
            $stmt->bindValue(':date', $now, PDO::PARAM_STR);
            $stmt->bindValue(':message', $message, PDO::PARAM_STR);
            $stmt->execute();

            db()->commit();
        } catch (PDOException $e) {
            db()->rollback();
            return false;
        }

        return true;
    }

    private static function isUnique(string $slug): bool
    {
        $query = 'SELECT * FROM `rooms` WHERE `slug` = ?;';
        $stmt = db()->prepare($query);
        $stmt->execute([$slug]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return empty($data);
    }
}
