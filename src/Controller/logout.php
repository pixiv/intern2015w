<?php
namespace Nyaan\Controller;
use Nyaan\Response\TemplateResponse;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class logout
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        $app->session->destroy();

        return new TemplateResponse('logout.tpl.html');
    }
}
