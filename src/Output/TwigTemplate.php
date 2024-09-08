<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;

/**
 * Use Twig template engine
 * @see https://twig.symfony.com/
 */
class TwigTemplate extends BaseRenderer
{
    /**
     * Summary of getTwigEnvironment
     * @param string|string[] $templateDir
     * @return \Twig\Environment
     */
    public function getTwigEnvironment($templateDir = 'templates/twigged')
    {
        $loader = new \Twig\Loader\FilesystemLoader($templateDir);
        $twig = new \Twig\Environment($loader);
        // add Twig functions for COPS templates
        $function = new \Twig\TwigFunction('str_format', function ($format, ...$args) {
            //return str_format($format, ...$args);
            return Format::str_format($format, ...$args);
        });
        $twig->addFunction($function);
        $assets = Route::path(Config::get('assets'));
        $function = new \Twig\TwigFunction('asset', function ($file) use ($assets) {
            return $assets . '/' . $file . '?v=' . Config::VERSION;
        });
        $twig->addFunction($function);

        return $twig;
    }

    /**
     * Summary of serverSide - not used here
     * @param \Twig\Environment $twig
     * @param ?array<mixed> $data
     * @param string $theme
     * @return bool|string|null
     */
    public function serverSide($twig, $data, $theme = 'twigged')
    {
        if (empty($twig)) {
            return false;
        }
        if (empty($data)) {
            return null;
        }
        return $twig->render('page.html', ['it' => $data]);
    }

    /**
     * Summary of renderPage
     * @param array<string, mixed> $data
     * @param string $theme
     * @param bool|int $serverSide
     * @return string
     */
    public function renderPage($data, $theme = 'twigged', $serverSide = false)
    {
        // support other Twig template directories too
        $twig = $this->getTwigEnvironment('templates/' . $theme);
        if ($serverSide) {
            // Get the page data
            $json = new JsonRenderer();
            $data['page_it'] = $json->getJson($this->request, true);
            if ($data['title'] != $data['page_it']['title']) {
                $data['title'] .= ' - ' . $data['page_it']['title'];
            }
            // twig template will automatically include 'page.html' if needed
        }
        return $twig->render('index.html', ['it' => $data]);
    }
}
