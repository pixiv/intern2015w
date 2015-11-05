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
        $query = "SELECT * FROM `users` WHERE `slug` = \"{$name}\"";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new Response\TemplateResponse('user.tpl.html', [
            'user' => $user,
        ]);
    }
}
