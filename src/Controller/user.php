<?php
namespace Nyaan\Controller;
use Nyaan\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class user
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        $name = ltrim($action->param['user'], '@');
        $query = "SELECT user_id FROM `users` WHERE `slug` = ?";
        $stmt = db()->prepare($query, array('text'));
        $stmt->execute(array($name));
        $id = $stmt->fetch(\PDO::FETCH_ASSOC);
        $id = $id['user_id'];

        $query =
               "SELECT * FROM users " .
               "LEFT JOIN user_profile ".
               "ON user_profile.user_id = users.user_id " .
               "WHERE users.user_id = ?";
        $stmt = db()->prepare($query);
        $stmt->execute(array(intval($id)));
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user) {
            return new Response\TemplateResponse('user.tpl.html', [
                'user' => $user,
            ]);
        } else {
            $message = "@".$name."は存在しません";
            return new Response\TemplateResponse('404.tpl.html', [
                'message' => $message
            ], 404);
        }
    }
}
