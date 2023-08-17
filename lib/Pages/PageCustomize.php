<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Model\Entry;

class PageCustomize extends Page
{
    /**
     * Summary of isChecked
     * @param mixed $key
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
     * @param mixed $key
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
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
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
        $content = "";
        if (!preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $this->request->agent())) {
            $content .= "<select id='template' onchange='updateCookie (this); window.location=window.location;'>";

            foreach ($this-> getTemplateList() as $filename) {
                $content .= "<option value='{$filename}' " . $this->isSelected("template", $filename) . ">{$filename}</option>";
            }
            $content .= '</select>';
        } else {
            foreach ($this-> getTemplateList() as $filename) {
                $content .= "<input type='radio' onchange='updateCookieFromCheckbox (this); window.location=window.location;' id='template' name='template' value='{$filename}' " . $this->isChecked("template", $filename) . " /><label for='template-{$filename}'> {$filename} </label>";
            }
        }
        array_push($this->entryArray, new Entry(
            localize("customize.template"),
            "",
            $content,
            "text",
            [],
            $database
        ));

        $content = "";
        if (!preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $this->request->agent())) {
            $content .= '<select id="style" onchange="updateCookie (this);">';
            foreach ($this-> getStyleList() as $filename) {
                $content .= "<option value='{$filename}' " . $this->isSelected("style", $filename) . ">{$filename}</option>";
            }
            $content .= '</select>';
        } else {
            foreach ($this-> getStyleList() as $filename) {
                $content .= "<input type='radio' onchange='updateCookieFromCheckbox (this);' id='style-{$filename}' name='style' value='{$filename}' " . $this->isChecked("style", $filename) . " /><label for='style-{$filename}'> {$filename} </label>";
            }
        }
        array_push($this->entryArray, new Entry(
            localize("customize.style"),
            "",
            $content,
            "text",
            [],
            $database
        ));
        if (!$this->request->render()) {
            $content = '<input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="use_fancyapps" ' . $this->isChecked("use_fancyapps") . ' />';
            array_push($this->entryArray, new Entry(
                localize("customize.fancybox"),
                "",
                $content,
                "text",
                [],
                $database
            ));
        }
        $content = '<input type="number" onchange="updateCookie (this);" id="max_item_per_page" value="' . $this->getNumberPerPage() . '" min="-1" max="1200" pattern="^[-+]?[0-9]+$" />';
        array_push($this->entryArray, new Entry(
            localize("customize.paging"),
            "",
            $content,
            "text",
            [],
            $database
        ));
        $content = '<input type="text" onchange="updateCookie (this);" id="email" value="' . $this->request->option("email") . '" />';
        array_push($this->entryArray, new Entry(
            localize("customize.email"),
            "",
            $content,
            "text",
            [],
            $database
        ));
        $content = '<input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="html_tag_filter" ' . $this->isChecked("html_tag_filter") . ' />';
        array_push($this->entryArray, new Entry(
            localize("customize.filter"),
            "",
            $content,
            "text",
            [],
            $database
        ));
        $content = "";
        foreach ($ignoredBaseArray as $key) {
            $keyPlural = preg_replace('/(ss)$/', 's', $key . "s");
            $content .=  '<input type="checkbox" name="ignored_categories[]" onchange="updateCookieFromCheckboxGroup (this);" id="ignored_categories_' . $key . '" ' . $this->isChecked("ignored_categories", $key) . ' > ' . localize("{$keyPlural}.title") . '</input> ';
        }

        array_push($this->entryArray, new Entry(
            localize("customize.ignored"),
            "",
            $content,
            "text",
            [],
            $database
        ));
    }
}
