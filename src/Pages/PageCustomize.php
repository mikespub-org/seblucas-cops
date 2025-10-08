<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\VirtualLibrary;
use SebLucas\Cops\Input\Config;

class PageCustomize extends Page
{
    /** @var array<string, mixed> */
    public array $custom = [];
    /** @var array<string, mixed> */
    public array $default = [];

    /**
     * Summary of isChecked
     * @param string $key
     * @param mixed $testedValue
     * @return string
     */
    protected function isChecked($key, $testedValue = 1)
    {
        // @todo use $this->custom
        $value = $this->request->option($key);
        if (is_array($value)) {
            if (in_array($testedValue, $value)) {
                return "checked='checked'";
            }
        } else {
            if ($value == $testedValue) {
                return "checked='checked'";
            }
        }
        return "";
    }

    /**
     * Summary of isSelected
     * @param string $key
     * @param mixed $value
     * @return string
     */
    protected function isSelected($key, $value)
    {
        // @todo use $this->custom
        if ($this->request->option($key) == $value) {
            return "selected='selected'";
        }
        return "";
    }

    /**
     * Summary of getTemplateList
     * @return array<string>
     */
    protected function getTemplateList()
    {
        $result = [];
        foreach (glob("templates/*", GLOB_ONLYDIR) as $filename) {
            if (preg_match('/templates\/(.*)/', $filename, $m)) {
                if ($m[1] === 'admin') {
                    continue;
                }
                array_push($result, $m [1]);
            }
        }
        return $result;
    }

    /**
     * Summary of getStyleList
     * @param string|null $template
     * @return array<string>
     */
    protected function getStyleList($template = null)
    {
        $template ??= $this->request->template();
        $result = [];
        foreach (glob("templates/" . $template . "/styles/style-*.css") as $filename) {
            if (preg_match('/styles\/style-(.*?)\.css/', $filename, $m)) {
                array_push($result, $m [1]);
            }
        }
        return $result;
    }

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        // @todo use $this->default here too?
        $this->default = Config::get('customize', []);
        // @todo use $this->custom
        $session = $this->request->getSession();
        if ($session) {
            if ($this->request->method() == 'POST') {
                $session->set('custom', $this->validateValues());
            }
            $this->custom = $session->get('custom') ?? [];
        }
        $this->getEntries();
        $this->title = localize("customize.title");
    }

    /**
     * Summary of validateValues
     * @return array<string, mixed>
     */
    protected function validateValues()
    {
        $custom = [];
        $template = $this->request->post('template');
        if (isset($template) && in_array($template, $this->getTemplateList())) {
            $custom['template'] = $template;
        } else {
            $template = null;
        }
        $style = $this->request->post('style');
        if (isset($style) && in_array($style, $this->getStyleList($template))) {
            $custom['style'] = $style;
        }
        $fancybox = $this->request->post('use_fancyapps');
        if (isset($fancybox)) {
            $custom['use_fancyapps'] = $fancybox ? true : false;
        }
        $paging = $this->request->post('max_item_per_page');
        if (isset($paging) && filter_var($paging, FILTER_VALIDATE_INT, ['min_range' => -1, 'max_range' => 1200])) {
            $custom['max_item_per_page'] = (int) $paging;
        }
        $email = $this->request->post('email');
        if (isset($email) && (empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE))) {
            $custom['email'] = $email;
        }
        $filter = $this->request->post('html_tag_filter');
        if (isset($filter)) {
            $custom['html_tag_filter'] = $filter ? true : false;
        }
        $ignored = $this->request->post('ignored_categories');
        if (isset($ignored) && is_array($ignored)) {
            $allowed = array_map(function ($enum) {
                return $enum->value;
            }, $this->getIgnoredCategoryList());
            $custom['ignored_categories'] = array_intersect($ignored, $allowed);
        }
        // do not customize virtual libraries for multiple databases
        if (Database::isMultipleDatabaseEnabled()) {
            return $custom;
        }
        $library = $this->request->post('virtual_library');
        if (isset($library) && !empty($library)) {
            $database = $this->getDatabaseId();
            $libraries = VirtualLibrary::getLibraries($database);
            $allowed = [];
            $id = 1;
            foreach ($libraries as $name => $value) {
                $value = VirtualLibrary::formatParameter($id, $name);
                $allowed[] = $value;
                $id += 1;
            }
            if (in_array($library, $allowed)) {
                $custom['virtual_library'] = $library;
            }
        }
        return $custom;
    }

    /**
     * Summary of getIgnoredCategoryList
     * @return array<PageQueryScope>
     */
    protected function getIgnoredCategoryList()
    {
        return [
            PageQueryScope::AUTHOR,
            PageQueryScope::SERIES,
            PageQueryScope::PUBLISHER,
            PageQueryScope::TAG,
            PageQueryScope::RATING,
            PageQueryScope::LANGUAGE,
            PageQueryScope::FORMAT,
            PageQueryScope::IDENTIFIER,
            PageQueryScope::BOOK,
        ];
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $this->entryArray = [];

        $database = $this->getDatabaseId();

        $title = localize("customize.template");
        $content = "";
        if ($this->useSelectTag()) {
            $content .= "<select id='template' name='template' onchange='updateCookie (this); window.location=window.location;'>";

            foreach ($this-> getTemplateList() as $filename) {
                $content .= "<option value='{$filename}' " . $this->isSelected("template", $filename) . ">{$filename}</option>";
            }
            $content .= '</select>';
        } else {
            foreach ($this-> getTemplateList() as $filename) {
                $content .= "<input type='radio' onchange='updateCookieFromCheckbox (this); window.location=window.location;' id='template-{$filename}' name='template' value='{$filename}' " . $this->isChecked("template", $filename) . " /><label for='template-{$filename}'> {$filename} </label><br>";
            }
        }
        $this->addHeaderEntry($title, $content);

        $title = localize("customize.style");
        $content = "";
        if ($this->useSelectTag()) {
            $content .= '<select id="style" name="style" onchange="updateCookie (this); window.location=window.location;">';
            foreach ($this-> getStyleList() as $filename) {
                $content .= "<option value='{$filename}' " . $this->isSelected("style", $filename) . ">{$filename}</option>";
            }
            $content .= '</select>';
        } else {
            foreach ($this-> getStyleList() as $filename) {
                $content .= "<input type='radio' onchange='updateCookieFromCheckbox (this);window.location=window.location;' id='style-{$filename}' name='style' value='{$filename}' " . $this->isChecked("style", $filename) . " /><label for='style-{$filename}'> {$filename} </label><br>";
            }
        }
        $this->addHeaderEntry($title, $content);

        // Enable the Lightboxes (for popups) in 'default' template with client side rendering
        if (!$this->request->render() && $this->request->template() === 'default') {
            $title = localize("customize.fancybox");
            $content = '<input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="use_fancyapps" name="use_fancyapps" ' . $this->isChecked("use_fancyapps") . ' />';
            $this->addHeaderEntry($title, $content);
        }

        $title = localize("customize.paging");
        $content = '<input type="number" onchange="updateCookie (this);" id="max_item_per_page" name="max_item_per_page" value="' . strval($this->getNumberPerPage()) . '" min="-1" max="1200" pattern="^[-+]?[0-9]+$" />';
        $this->addHeaderEntry($title, $content);

        $title = localize("customize.email");
        $content = '<input type="text" onchange="updateCookie (this);" id="email" name="email" value="' . $this->request->option("email") . '" />';
        $this->addHeaderEntry($title, $content);

        $title = localize("customize.filter");
        $content = '<input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="html_tag_filter" name="html_tag_filter" ' . $this->isChecked("html_tag_filter") . ' />';
        $this->addHeaderEntry($title, $content);

        $title = localize("customize.ignored");
        $content = "";
        foreach ($this->getIgnoredCategoryList() as $scope) {
            $value = $scope->value;
            $label = $scope->title();
            $content .=  '<input type="checkbox" name="ignored_categories[]" value="' . $value . '" onchange="updateCookieFromCheckboxGroup (this);" id="ignored_categories_' . $value . '" ' . $this->isChecked("ignored_categories", $value) . ' > ' . $label . '</input><br>';
        }
        $this->addHeaderEntry($title, $content);

        // do not customize virtual libraries for multiple databases
        if (!Database::isMultipleDatabaseEnabled()) {
            $this->addVirtualLibraries($database);
        }
    }

    /**
     * Summary of useSelectTag
     * @return bool|int
     */
    public function useSelectTag()
    {
        return !preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $this->request->agent());
    }

    /**
     * Summary of addVirtualLibraries
     * @param int|null $database
     * @return void
     */
    public function addVirtualLibraries($database = null)
    {
        $libraries = VirtualLibrary::getLibraries($database);
        if (empty($libraries)) {
            return;
        }
        $title = localize("library.title");
        $content = "";
        $id = 1;
        if ($this->useSelectTag()) {
            $content .= "<select id='virtual_library' name='virtual_library' onchange='updateCookie (this); window.location=window.location;'>";
            $content .= "<option value='' " . $this->isSelected("virtual_library", "") . ">" . localize("libraries.none") . "</option>";
            foreach ($libraries as $name => $value) {
                $value = VirtualLibrary::formatParameter($id, $name);
                $content .= "<option value='$value' " . $this->isSelected("virtual_library", $value) . ">{$id}. {$name}</option>";
                $id += 1;
            }
            $content .= '</select>';
        } else {
            $content .= "<input type='radio' onchange='updateCookieFromCheckbox (this); window.location=window.location;' id='virtual_library-0' name='virtual_library' value='' " . $this->isChecked("virtual_library", "") . " /><label for='virtual_library-0'> " . localize("libraries.none") . " </label><br>";
            foreach ($libraries as $name => $value) {
                $value = VirtualLibrary::formatParameter($id, $name);
                $content .= "<input type='radio' onchange='updateCookieFromCheckbox (this); window.location=window.location;' id='virtual_library-{$id}' name='virtual_library' value='{$value}' " . $this->isChecked("virtual_library", $value) . " /><label for='virtual_library-{$id}'> {$id}. {$name} </label><br>";
                $id += 1;
            }
        }
        $this->addHeaderEntry($title, $content);
    }
}
