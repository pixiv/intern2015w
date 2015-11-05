<?php
namespace Nyaan\Response;

/**
 * HTML Template Response
 *
 * @package   Nyaan\Response
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class TemplateResponse extends \Baguette\Response\TwigResponse
{
    /**
     * @param  Nyaan\Application $app
     * @return string
     */
    public function render(\Baguette\Application $app)
    {
        $params = $this->params + [
            'server'  => $app->server,
            'cookie'  => $app->cookie,
            'get'     => $app->get,
            'post'    => $app->post,
            'now'     => $app->now,
            'isLoggedIn' => $app->isLoggedIn(),
            'loginUser'  => $app->getLoginUser(),
        ];

        return static::$twig->render($this->tpl_name, $params);
    }

    /**
     * @param  \Baguette\Application $_ is not used.
      */
    public function getHttpStatusCode(\Baguette\Application $_): int
    {
        return $this->status_code;
    }

    /**
     * @param  \Twig_Environment
     */
    public static function setTwigEnvironment(\Twig_Environment $twig)
    {
        $twig->addExtension(new \Twig_Extensions_Extension_Text);

        $twig->addFunction(new \Twig_SimpleFunction('markdown', '\Nyaan\View\Markdown::render', ['is_safe' => ['html']]));
        //$twig->addFilter(new \Twig_SimpleFilter('caption_convert', '\MobileNovel\Service\NovelService::convertCaption'), ['is_safe' => ['html']]);

        parent::setTwigEnvironment($twig);
    }

    public function getTplPath(\Baguette\Application $app): string
    {
        $is_ketai = ($app->getViewMode() == $app::VIEW_MODE_KETAI);
        $prefix = $app->getViewMode() . '/';
        $suffix = $is_ketai ? '.tpl.xhtml' : '.tpl.html';

        return $prefix . $this->tpl_name . $suffix;
    }

    public function isTplExists(\Baguette\Application $app): bool
    {
        try {
            self::$twig->loadTemplate($this->getTplPath($app));
        } catch (\Twig_Error_Loader $e) {
            return false;
        }

        return true;
    }
}
