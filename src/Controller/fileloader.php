<?php
namespace Nyaan\Controller;
use Nyaan\Response;
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
        $filename = isset($_REQUEST['name']) ? $_REQUEST['name'] : $_SERVER['PHP_SELF'];
        $filename = basename($filename);
        $ext  = pathinfo($filename, PATHINFO_EXTENSION);
        $path = dirname(dirname(__DIR__)) . "/htdocs/{$filename}";

        if (!file_exists($path)) {
            return new Response\TemplateResponse('404.tpl.html', [], 404);
        }

        $mime_types = [
            'css' => ContentType::Text_CSS,
            'jpg' => ContentType::Image_JPEG,
            'txt' => ContentType::Text_Plain,
        ];
        header('Content-Type: '.$mime_types[$ext]);
        header('Expires: '. date(\DateTime::RFC1123, time() + 3600));
        header('Cache-Control: max-age=3600');
        readfile($path);
        exit;
    }
}
