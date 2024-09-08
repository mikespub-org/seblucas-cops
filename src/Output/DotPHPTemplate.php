<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Template\doT;

/**
 * Use doT-php template engine
 * @see resources/dot-php
 */
class DotPHPTemplate extends BaseRenderer
{
    /**
     * Summary of getDotTemplate
     * @param string $templateFile
     * @return \Closure
     */
    public function getDotTemplate($templateFile)
    {
        // production mode was required here for issue seblucas/cops#392
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        $headcontent = file_get_contents($templateFile);
        $template = new doT();
        $dot = $template->template($headcontent, null);

        return $dot;
    }

    /**
     * Summary of serverSide
     * @param ?array<mixed> $data
     * @param string $theme
     * @return bool|string|null
     */
    public function serverSide($data, $theme = 'default')
    {
        // Get the templates
        $header = file_get_contents('templates/' . $theme . '/header.html');
        $footer = file_get_contents('templates/' . $theme . '/footer.html');
        $main = file_get_contents('templates/' . $theme . '/main.html');
        $bookdetail = file_get_contents('templates/' . $theme . '/bookdetail.html');
        $page = file_get_contents('templates/' . $theme . '/page.html');

        // Generate the function for the template
        $template = new doT();
        $dot = $template->template($page, ['bookdetail' => $bookdetail,
            'header' => $header,
            'footer' => $footer,
            'main' => $main]);
        // If there is a syntax error in the function created
        // $dot will be equal to FALSE
        if (!$dot) {
            return false;
        }
        // Execute the template
        if (!empty($data)) {
            return $dot($data);
        }

        return null;
    }

    /**
     * Summary of renderPage
     * @param array<string, mixed> $data
     * @param string $theme
     * @param bool|int $serverSide
     * @return string
     */
    public function renderPage($data, $theme = 'default', $serverSide = false)
    {
        $dot = $this->getDotTemplate('templates/' . $theme . '/file.html');
        if ($serverSide) {
            // Get the page data
            $json = new JsonRenderer();
            $page_it = $json->getJson($this->request, true);
            if ($data['title'] != $page_it['title']) {
                $data['title'] .= ' - ' . $page_it['title'];
            }
            // insert 'page.html' template with data here
            $output = $dot($data);
            $output .= "<body>\n";
            $output .= $this->serverSide($page_it, $theme);
            $output .= "</body>\n</html>\n";
            return $output;
        }
        $output = $dot($data);
        $output .= "<body>\n</body>\n</html>\n";
        return $output;
    }
}
