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

        $viewerUrl = Uri::root(true) . '/plugins/content/pdf_embed/assets/viewer/pdfjs/web/viewer.html';

        $article->text = preg_replace_callback(
            '/\{pdf=([^\|\}]+)(?:\|([^\|\}]*))?(?:\|([^\|\}]*))?(?:\|[^\}]*)?\}/i',
            function (array $m) use ($viewerUrl): string {
                $pdfUrl = trim($m[1]);
                $width  = !empty(trim($m[2] ?? '')) ? trim($m[2]) : '100%';
                $height = !empty(trim($m[3] ?? '')) ? (int) $m[3] : 720;

                // Rewrite live-site URLs to current-site-relative so PDF.js same-origin check passes
                $siteRoot = rtrim(Uri::root(), '/');
                $liveHost = 'https://www.aidff.com';
                if (stripos($pdfUrl, $liveHost) === 0) {
                    $pdfUrl = $siteRoot . substr($pdfUrl, strlen($liveHost));
                } elseif (!preg_match('~^https?://~i', $pdfUrl)) {
                    $pdfUrl = $siteRoot . '/' . ltrim($pdfUrl, '/');
                }

                $src = $viewerUrl . '?file=' . rawurlencode($pdfUrl);

                return '<div class="pdfview-embed" style="width:' . htmlspecialchars($width, ENT_QUOTES) . ';max-width:100%;margin:1.5rem 0">'
                    . '<' . 'iframe src="' . htmlspecialchars($src, ENT_QUOTES) . '" '
                    . 'width="100%" height="' . $height . '" '
                    . 'style="border:none;display:block;border-radius:4px" '
                    . 'allowfullscreen loading="lazy"></' . 'iframe>'
                    . '</div>';
            },
            $article->text
        );
    }
}
