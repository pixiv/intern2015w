<?php
namespace Nyaan;
use Baguette\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class room_list
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        return new Response\TwigResponse('room_list.tpl.html');
    }
}
