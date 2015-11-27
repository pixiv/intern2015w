<?php
namespace Nyaan\Controller;
use Nyaan\Response\TemplateResponse;
use Baguette\HTTP\ContentType;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class fileloader
{
    function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        $basedir = dirname(dirname(__DIR__));

        switch ($app->server['PHP_SELF'] ?? '') {
        case '/wintern.css':
            $path = $basedir . '/htdocs/wintern.css';
            $mime = ContentType::Text_CSS;
            break;
        case '/LICENSE.txt':
            $path = $basedir . '/htdocs/LICENSE.txt';
            $mime = ContentType::Text_Plain;
            break;
        case '/wall.jpg':
            $path = $basedir . '/htdocs/wall.jpg';
            $mime = ContentType::Image_JPEG;
            break;
        default:
            return new TemplateResponse('404.tpl.html', [], 404);
        }

         header('Content-Type: '.$mime);
         header('Expires: '. date(\DateTime::RFC1123, time() + 3600));
         header('Cache-Control: max-age=3600');
         readfile($path);
         exit;
    }
}
