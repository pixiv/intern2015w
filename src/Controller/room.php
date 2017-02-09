<?php
namespace Nyaan\Controller;
use Nyaan\Response;

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
        $room  = $action->param['slug'];

        $query = "SELECT * FROM `rooms` WHERE `slug` = :slug";
        $stmt = db()->prepare($query);
        $stmt->bindParam(':slug', $room);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!empty($_REQUEST['message'])) {
            $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
            $message = htmlspecialchars(str_replace('"', '\\"', $_REQUEST['message']));
            $user_id = $_SESSION['user_id'];
            $query = "INSERT INTO `posts` VALUES( {$data['id']}, :user_id, \"{$now}\", :message )";
            $stmt = db()->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':message', $message);
            $stmt->execute();
        }

        $query = "SELECT * FROM `posts` WHERE `room_id` = {$data['id']} ORDER BY datetime(`posted_at`) DESC LIMIT 100";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $talk = $stmt->fetchALL(\PDO::FETCH_ASSOC);

        $users = [];
        foreach ($talk as $s) {
            $user_id = $s['user_id'];
            if (empty($users[$user_id])) {
                $query = "SELECT * FROM `users` WHERE `id` = {$user_id}";
                $stmt = db()->prepare($query);
                $stmt->execute();
                $users[$user_id] = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        }

        return new Response\TemplateResponse('room.tpl.html', [
            'slug' => $room,
            'room' => $data,
            'talk' => $talk,
            'users' => $users,
        ]);
    }
}
