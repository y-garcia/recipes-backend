<?php
/**
 * Author: Yeray García Quintana
 * Date: 10.11.2018
 */

namespace Recipes\db\entity;

use JsonSerializable;
use Recipes\db\UuidHelper;

abstract class Entity implements JsonSerializable
{
    /** @var string */
    protected $table;
    /** @var \Ramsey\Uuid\UuidInterface */
    protected $id;

    /**
     * Entity constructor.
     * @param string $table
     * @param \Ramsey\Uuid\UuidInterface $id
     */
    public function __construct($table, $id = null)
    {
        $this->table = $table;
        $this->id = $id ?: UuidHelper::newUUID();
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Ramsey\Uuid\UuidInterface $id
     */
    public function setId($id)
    {
        $this->id = $id ?: UuidHelper::newUUID();
    }

    public function fromArray(array $row)
    {
        foreach ($row as $key => $value) {
            $method = "set" . $this->camelize($key, "_", true);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    private function camelize($string, $separator = "_", $capitalizeFirstCharacter = false)
    {
        $str = str_replace($separator, '', ucwords($string, $separator));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
