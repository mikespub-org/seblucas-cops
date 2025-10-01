<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org//licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

/**
 * Use Twig template engine
 * @see https://twig.symfony.com/
 */
class TwigTemplate extends BaseRenderer
{
    /** @var string */
    protected $theme = 'twigged';
    /** @var bool */
    protected $serverSide = false;
    /** @var \Twig\Environment */
    protected $twig;

    /**
     * Summary of setRequest
     * @param Request $request
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
        $this->theme = $request->template();
        $this->serverSide = $request->render() ? true : false;
        // support other Twig template directories too
        $this->twig = $this->getTwigEnvironment('templates/' . $this->theme);
    }

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
        $assets = $this->getPath(Config::get('assets'));
        $function = new \Twig\TwigFunction('asset', function ($file) use ($assets) {
            return $assets . '/' . $file . '?v=' . Config::VERSION;
        });
        $twig->addFunction($function);

        return $twig;
    }

    /**
     * Summary of serverSide - not used here
     * @param ?array<mixed> $data
     * @param string $name = page.html
     * @return bool|string|null
     */
    public function serverSide($data, $name = 'page.html')
    {
        if (empty($data)) {
            return null;
        }
        return $this->twig->render($name, ['it' => $data]);
    }

    /**
     * Summary of renderBlock - @todo test for htmx
     * @param array<string, mixed> $data
     * @param string $name
     * @param string $block = main
     * @return string
     */
    public function renderBlock($data, $name = 'mainlist.html', $block = 'main')
    {
        // Get the page data
        //$json = new JsonRenderer();
        //$data = $json->getJson($this->request, true);
        // @todo Load the (right?) template - see page.html
        //$name = $this->getTemplateName($data);
        $template = $this->twig->load($name);
        // @todo Render block from template - internal use only
        return $template->renderBlock($block, ['it' => $data]);
    }

    /**
     * Summary of getTemplateName - @todo see page.html (hard-coded)
     * @param array<string, mixed> $data
     * @return string
     */
    public function getTemplateName($data)
    {
        if ($data['page'] == "book") {
            return 'bookdetail.html';
        } elseif ($data['page'] == "about") {
            return 'about.html';
        } elseif ($data['page'] == "customize") {
            return 'customize.html';
        } elseif ($data['isFilterPage']) {
            return 'filters.html';
        } elseif ($data['containsBook'] == 0) {
            return 'navlist.html';
        } elseif ($data['page'] == "recent") {
            return 'recent.html';
        } else {
            return 'booklist.html';
        }
    }

    /**
     * Summary of renderPage
     * @param array<string, mixed> $data
     * @param string $name = index.html
     * @return string
     */
    public function renderPage($data, $name = 'index.html')
    {
        if ($this->serverSide) {
            // Get the page data
            $json = new JsonRenderer();
            $data['page_it'] = $json->getJson($this->request, true);
            if ($data['title'] != $data['page_it']['title']) {
                $data['title'] .= ' - ' . $data['page_it']['title'];
            }
            // twig template will automatically include 'page.html' if needed
        }
        return $this->twig->render($name, ['it' => $data]);
    }
}
