<?php
namespace Nyaan\View;

/**
 * @package   Nyaan\View
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class Markdown
{
    /**
     * @param  string $input
     * @return string HTML
     */
    public static function render($input)
    {
        return preg_replace(
            '@</p>$@', '',
            preg_replace('@^<p>@', '', (new \Parsedown)->text(
                htmlspecialcharacters($input, ENT_QUOTES, 'UTF-8')))
        );
    }
}
