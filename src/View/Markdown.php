<?php
namespace Nyaan\View;
use HTMLPurifier;

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
        $purifier = new HTMLPurifier();
        $sanitized = $purifier->purify((new \Parsedown)->text($input));
        return preg_replace(
            '@</p>$@', '',
            preg_replace('@^<p>@', '', $sanitized)
        );
    }
}
