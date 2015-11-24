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

        $query = 'SELECT * FROM `rooms` WHERE `slug` = ?';
        $stmt = db()->prepare($query);
        $stmt->execute([$room]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        $token = $app->session->get('token', ['default' => false]);
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['token']) && $_POST['token'] === $token) {
            $app->session->set('token', NULL);
            return $this->post($room, $app->session->get('user_id'), $_POST['message'] ?? '');
        }

        $query = 'SELECT * FROM `posts` WHERE `room_id` = ? ORDER BY datetime(`posted_at`) DESC LIMIT 100';
        $stmt = db()->prepare($query);
        $stmt->execute([$data['id']]);
        $talk = $stmt->fetchALL(\PDO::FETCH_ASSOC);

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

        $token = csrf_token();
        $app->session->set('token', $token);

        return new Response\TemplateResponse('room.tpl.html', [
            'slug' => $room,
            'room' => $data,
            'talk' => $talk,
            'users' => $users,
            'token' => $token
        ]);
    }


    private function post($room, $user, $message) {
        if ($message !== '') {
            $query = 'INSERT INTO `posts` (`room_id`, `user_id`, `message`) VALUES((SELECT `id` FROM `rooms` WHERE `slug` = ?), ?, ?)';
            $stmt = db()->prepare($query);
            $stmt->execute([$room, $user, $message]);
        }
        return new Response\RedirectResponse("/rooms/$room");
    }
}
