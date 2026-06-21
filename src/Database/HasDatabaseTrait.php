<?php

namespace SebLucas\Cops\Database;

/**
 * Trait for classes that use DatabaseContext instance methods
 * instead of static Database methods
 */
trait HasDatabaseTrait
{
    protected ?int $databaseId = null;
    protected ?DatabaseContext $dbContext = null;

    /**
     * Summary of getDatabaseId
     * @return ?int
     */
    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    /**
     * Summary of setDatabaseId
     * @param ?int $databaseId
     * @return void
     */
    public function setDatabaseId($databaseId)
    {
        if ($this->databaseId !== $databaseId) {
            $this->dbContext = null;
        }
        $this->databaseId = $databaseId;
    }

    /**
     * Summary of getDbContext
     * @return DatabaseContext
     */
    public function getDbContext()
    {
        return $this->dbContext ??= new DatabaseContext($this->databaseId, $this->getConfig());
    }

    /**
     * Summary of setDbContext
     * @param ?DatabaseContext $dbContext
     * @return void
     */
    public function setDbContext($dbContext)
    {
        $this->dbContext = $dbContext;
    }
}
