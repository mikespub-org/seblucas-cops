<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\VirtualLibrary;
use SebLucas\Cops\Model\Entry;

class PageCustomize extends Page
{
    /**
     * Summary of isChecked
     * @param string $key
     * @param mixed $testedValue
     * @return string
     */
    protected function isChecked($key, $testedValue = 1)
    {
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
                array_push($result, $m [1]);
            }
        }
        return $result;
    }

    /**
     * Summary of getStyleList
     * @return array<string>
     */
    protected function getStyleList()
    {
        $result = [];
        foreach (glob("templates/" . $this->request->template() . "/styles/style-*.css") as $filename) {
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
        $this->getEntries();
        $this->title = localize("customize.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $this->entryArray = [];

        $ignoredBaseArray = [PageQueryResult::SCOPE_AUTHOR,
            PageQueryResult::SCOPE_TAG,
            PageQueryResult::SCOPE_SERIES,
            PageQueryResult::SCOPE_PUBLISHER,
            PageQueryResult::SCOPE_RATING,
            "language"];

        $database = $this->getDatabaseId();

        $title = localize("customize.template");
        $content = "";
        if ($this->useSelectTag()) {
            $content .= "<select id='template' onchange='updateCookie (this); window.location=window.location;'>";

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

        if (!$this->request->render()) {
            $title = localize("customize.fancybox");
            $content = '<input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="use_fancyapps" ' . $this->isChecked("use_fancyapps") . ' />';
            $this->addHeaderEntry($title, $content);
        }

        $title = localize("customize.paging");
        $content = '<input type="number" onchange="updateCookie (this);" id="max_item_per_page" value="' . strval($this->getNumberPerPage()) . '" min="-1" max="1200" pattern="^[-+]?[0-9]+$" />';
        $this->addHeaderEntry($title, $content);

        $title = localize("customize.email");
        $content = '<input type="text" onchange="updateCookie (this);" id="email" value="' . $this->request->option("email") . '" />';
        $this->addHeaderEntry($title, $content);

        $title = localize("customize.filter");
        $content = '<input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="html_tag_filter" ' . $this->isChecked("html_tag_filter") . ' />';
        $this->addHeaderEntry($title, $content);

        $title = localize("customize.ignored");
        $content = "";
        foreach ($ignoredBaseArray as $key) {
            $keyPlural = preg_replace('/(ss)$/', 's', $key . "s");
            $content .=  '<input type="checkbox" name="ignored_categories[]" onchange="updateCookieFromCheckboxGroup (this);" id="ignored_categories_' . $key . '" ' . $this->isChecked("ignored_categories", $key) . ' > ' . localize("{$keyPlural}.title") . '</input><br>';
        }
        $this->addHeaderEntry($title, $content);

        $this->addVirtualLibraries($database);
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
            $content .= "<select id='virtual_library' onchange='updateCookie (this); window.location=window.location;'>";
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
