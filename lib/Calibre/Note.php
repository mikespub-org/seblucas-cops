<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

class Note
{
    public int $id;
    public int $item;
    public string $colname;
    public string $doc;
    public float $mtime;
    public ?int $databaseId = null;

    /**
     * Summary of __construct
     * @param object $post
     * @param ?int $database
     */
    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->item = $post->item;
        $this->colname = $post->colname;
        $this->doc = $post->doc;
        $this->mtime = $post->mtime;
        $this->databaseId = $database;
    }

    /**
     * Summary of getResources
     * @return array<mixed>
     */
    public function getResources()
    {
        $notesDb = Database::getNotesDb($this->databaseId);
        if (is_null($notesDb)) {
            return [];
        }
        $resources = [];
        $query = 'select hash, name from resources, notes_resources_link where resources.hash = resource and note = ?';
        $params = [$this->id];
        $result = $notesDb->prepare($query);
        $result->execute($params);
        while ($post = $result->fetchObject()) {
            $resources[$post->hash] = new Resource($post, $this->databaseId);
        }
        return $resources;
    }
}
