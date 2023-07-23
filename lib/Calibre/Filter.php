<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Request;

class Filter
{
    protected Request $request;
    protected array $params = [];
    protected string $queryString = "";
    protected mixed $databaseId;

    /**
     * Summary of __construct
     * @param \SebLucas\Cops\Input\Request $request
     * @param array $params
     */
    public function __construct(Request $request, array $params = [], mixed $database = null)
    {
        $this->request = $request;
        $this->params = $params;
        $this->queryString = "";
        $this->databaseId = $database;

        $this->checkForFilters();
    }

    /**
     * Summary of getFilterString
     * @return string filters to append to query string
     */
    public function getFilterString()
    {
        return $this->queryString;
    }

    /**
     * Summary of getQueryParams
     * @return array updated query params including filters
     */
    public function getQueryParams()
    {
        return $this->params;
    }

    /**
     * Summary of checkForFilters
     * @return void
     */
    public function checkForFilters()
    {
        if (empty($this->request->urlParams)) {
            return;
        }

        $tagName = $this->request->get('tag', null);
        if (!empty($tagName)) {
            $this->addTagNameFilter($tagName);
        }

        $authorId = $this->request->get('a', null);
        if (!empty($authorId) && preg_match('/^!?\d+$/', $authorId)) {
            $this->addAuthorIdFilter($authorId);
        }

        $languageId = $this->request->get('l', null);
        if (!empty($languageId) && preg_match('/^!?\d+$/', $languageId)) {
            $this->addLanguageIdFilter($languageId);
        }

        $publisherId = $this->request->get('p', null);
        if (!empty($publisherId) && preg_match('/^!?\d+$/', $publisherId)) {
            $this->addPublisherIdFilter($publisherId);
        }

        $seriesId = $this->request->get('s', null);
        if (!empty($seriesId) && preg_match('/^!?\d+$/', $seriesId)) {
            $this->addSeriesIdFilter($seriesId);
        }

        $tagId = $this->request->get('t', null);
        if (!empty($tagId) && preg_match('/^!?\d+$/', $tagId)) {
            $this->addTagIdFilter($tagId);
        }

        // URL format: ...&c[2]=3&c[3]=other to filter on column 2 = 3 and column 3 = other
        $customIdArray = $this->request->get('c', null);
        if (!empty($customIdArray)) {
            $this->addCustomIdArrayFilters($customIdArray);
        }
    }

    /**
     * Summary of addFilter
     * @param mixed $filter
     * @param mixed $param
     * @return void
     */
    public function addFilter($filter, $param)
    {
        $this->queryString .= 'and (' . $filter . ')';
        array_push($this->params, $param);
    }

    /**
     * Summary of addTagNameFilter
     * @param mixed $tagName
     * @return void
     */
    public function addTagNameFilter($tagName)
    {
        $exists = true;
        if (preg_match("/^!(.*)$/", $tagName, $matches)) {
            $exists = false;
            $tagName = $matches[1];
        }

        $filter = 'exists (select null from books_tags_link, tags where books_tags_link.book = books.id and books_tags_link.tag = tags.id and tags.name = ?)';

        if (!$exists) {
            $filter = 'not ' . $filter;
        }

        $this->addFilter($filter, $tagName);
    }

    /**
     * Summary of addAuthorIdFilter
     * @param mixed $authorId
     * @return void
     */
    public function addAuthorIdFilter($authorId)
    {
        $this->addLinkedIdFilter($authorId, 'books_authors_link', 'author');
    }

    /**
     * Summary of addLanguageIdFilter
     * @param mixed $languageId
     * @return void
     */
    public function addLanguageIdFilter($languageId)
    {
        $this->addLinkedIdFilter($languageId, 'books_languages_link', 'lang_code');
    }

    /**
     * Summary of addPublisherIdFilter
     * @param mixed $publisherId
     * @return void
     */
    public function addPublisherIdFilter($publisherId)
    {
        $this->addLinkedIdFilter($publisherId, 'books_publishers_link', 'publisher');
    }

    /**
     * Summary of addSeriesIdFilter
     * @param mixed $seriesId
     * @return void
     */
    public function addSeriesIdFilter($seriesId)
    {
        $this->addLinkedIdFilter($seriesId, 'books_series_link', 'series');
    }

    /**
     * Summary of addTagIdFilter
     * @param mixed $tagId
     * @return void
     */
    public function addTagIdFilter($tagId)
    {
        $this->addLinkedIdFilter($tagId, 'books_tags_link', 'tag');
    }

    /**
     * Summary of addCustomIdArrayFilters
     * @param array $customIdArray
     * @return void
     */
    public function addCustomIdArrayFilters($customIdArray)
    {
        foreach ($customIdArray as $customId => $valueId) {
            if (!preg_match('/^\d+$/', $customId)) {
                continue;
            }
            $this->addCustomIdFilter($customId, $valueId);
        }
    }

    /**
     * Summary of addCustomIdFilter
     * @param mixed $customId
     * @param mixed $valueId
     * @return void
     */
    public function addCustomIdFilter($customId, $valueId)
    {
        $customType = CustomColumnType::createByCustomID($customId, $this->databaseId);
        //[$query, $params] = $customType->getQuery($valueId);
        //return $this->getEntryArray($query, $params, $n);
        [$filter, $params] = $customType->getFilter($valueId);
        if (!empty($filter)) {
            //var_dump([$filter, $params]);
            $this->queryString .= 'and (' . $filter . ')';
            foreach ($params as $param) {
                array_push($this->params, $param);
            }
        }
    }

    /**
     * Summary of addLinkedIdFilter
     * @param mixed $linkId
     * @param mixed $linkTable
     * @param mixed $linkColumn
     * @return void
     */
    public function addLinkedIdFilter($linkId, $linkTable, $linkColumn)
    {
        $exists = true;
        if (preg_match("/^!(.*)$/", $linkId, $matches)) {
            $exists = false;
            $linkId = $matches[1];
        }

        $filter = "exists (select null from {$linkTable} where {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";

        if (!$exists) {
            $filter = 'not ' . $filter;
        }

        $this->addFilter($filter, $linkId);
    }
}
