<?php

namespace Recipes\db\entity;

use Ramsey\Uuid\Uuid;
use Recipes\config\Config;

class Deleted extends Entity
{
    /** @var int $table_id */
    private $table_id;
    /** @var Uuid $deleted_id */
    private $deleted_id;
    /** @var string $deleted */
    private $deleted;

    /**
     * Deleted constructor.
     * @param int $table_id
     * @param Uuid $deleted_id
     * @param string $deleted
     * @param Uuid $id
     */
    public function __construct($table_id = null, Uuid $deleted_id = null, $deleted = null, Uuid $id = null)
    {
        parent::__construct(Config::TABLE_DELETED, $id);
        $this->table_id = $table_id;
        $this->deleted_id = $deleted_id;
        $this->deleted = $deleted;
    }

    /**
     * @return int
     */
    public function getTableId()
    {
        return $this->table_id;
    }

    /**
     * @param int $table_id
     * @return Deleted
     */
    public function setTableId($table_id)
    {
        $this->table_id = $table_id;
        return $this;
    }

    /**
     * @return Uuid
     */
    public function getDeletedId()
    {
        return $this->deleted_id;
    }

    /**
     * @param Uuid $deleted_id
     * @return Deleted
     */
    public function setDeletedId($deleted_id)
    {
        $this->deleted_id = $deleted_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param string $deleted
     * @return Deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

}