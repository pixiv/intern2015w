<?php
namespace Nyaan\Controller;
use Nyaan\Response\TemplateResponse;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class room
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        $room = $action->param['slug'];
        $data = self::getRoomInfo($room);

        if (!empty($app->post['message']) && $app->isLoggedIn())
            self::putMessage(
                $data['id'],
                $app->session->get('user_id', ['default' => 0]),
                $app->post['message']
            );

        $talk = self::getRoomTalks($data['id']);
        $users = [];
        foreach ($talk as $s) {
            $user_id = $s['user_id'];
            if (empty($users[$user_id])) {
                $query = 'SELECT * FROM `users` WHERE `id` = ?';
                $stmt = db()->prepare($query);
                $stmt->execute([$user_id]);
                $users[$user_id] = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        }

        return new TemplateResponse('room.tpl.html', [
            'slug' => $room,
            'room' => $data,
            'talk' => $talk,
            'users' => $users,
        ]);
    }

    private static function getRoomInfo(string $room_slug): array
    {
        $query = 'SELECT * FROM `rooms` WHERE `slug` = :slug';
        $stmt = db()->prepare($query);
        $stmt->bindValue(':slug', $room_slug, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private static function getRoomTalks(string $room_id): array
    {
        $query = 'SELECT * FROM `posts` WHERE `room_id` = :room_id ORDER BY datetime(`posted_at`) DESC LIMIT 100';
        $stmt = db()->prepare($query);
        $stmt->bindValue(':room_id', $room_id, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }

    private static function putMessage(int $room_id, int $uid, string $message): bool
    {
        try {
            db()->beginTransaction();

            $query = 'INSERT INTO `posts` VALUES(:room_id, :uid, :date, :message);';
            $stmt = db()->prepare($query);
            $stmt->bindValue(':room_id', $room_id, \PDO::PARAM_INT);
            $stmt->bindValue(':uid', $uid, \PDO::PARAM_INT);
            $stmt->bindValue(':date', date('Y-m-d H:i:s', strtotime('+9 hours')), \PDO::PARAM_STR);
            $stmt->bindValue(':message', $message, \PDO::PARAM_STR);
            $stmt->execute();

            db()->commit();
        } catch (PDOException $e) {
            db()->rollback();
            return false;
        }

        return true;
    }
}
