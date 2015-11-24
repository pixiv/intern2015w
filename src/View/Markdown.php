<?php
namespace Nyaan\View;

/**
 * @package   Nyaan\View
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class Markdown extends \Parsedown
{
    function __construct()
    {
        $this->setMarkupEscaped(true);
    }

    // XSS対策
    protected function inlineLink($Excerpt)
    {
        $res = parent::inlineLink($Excerpt);
        $href = $res['element']['attributes']['href'];
        if (isset($href)) {
            if (preg_match('/^javascript\:/i', $href)) {
                $res['element']['attributes']['href'] = NULL;
            }
        }
        return $res;
    }

    /**
     * @param  string $input
     * @return string HTML
     */
    public static function render($input)
    {
        $md = new Markdown();
        return preg_replace('/^<p>|<\/p>$/', '', $md->text($input));
    }
}
