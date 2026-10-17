<?php
/**
 * @package     Plg_Content_Pdfview
 * @copyright   (C) 2026 WebtechSolutions
 * @license     GNU General Public License version 2 or later
 */

namespace Nickpsal\Plugin\Content\Pdfview\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

final class Pdfview extends CMSPlugin
{
    public function onContentPrepare($context, $article, $params, $page = 0): void
    {
        if (!isset($article->text) || strpos($article->text, '{pdf=') === false) {
            return;
        }

        $article->text = preg_replace_callback(
            '/\{pdf=([^\|\}]+)(?:\|([^\|\}]*))?(?:\|([^\|\}]*))?(?:\|[^\}]*)?\}/i',
            function (array $m): string {
                // The editor may wrap the URL in a link or add entities; keep only the plain URL
                $pdfUrl = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
                $width  = trim(strip_tags($m[2] ?? '')) ?: '100%';
                $height = (int) strip_tags($m[3] ?? '') ?: 720;

                if ($pdfUrl === '') {
                    return $m[0];
                }

                if (!preg_match('~^https?://~i', $pdfUrl)) {
                    $pdfUrl = rtrim(Uri::root(), '/') . '/' . ltrim($pdfUrl, '/');
                }

                $url = htmlspecialchars($pdfUrl, ENT_QUOTES);

                return '<div class="pdfview-embed" style="width:' . htmlspecialchars($width, ENT_QUOTES) . ';max-width:100%;margin:1.5rem 0">'
                    . '<' . 'iframe src="' . $url . '#view=FitH" '
                    . 'width="100%" height="' . $height . '" '
                    . 'style="border:none;display:block;border-radius:4px" '
                    . 'allowfullscreen loading="lazy"></' . 'iframe>'
                    . '<p style="margin:.5rem 0 0"><a href="' . $url . '" target="_blank" rel="noopener">PDF</a></p>'
                    . '</div>';
            },
            $article->text
        );
    }
}
