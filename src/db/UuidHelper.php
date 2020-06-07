<?php
/**
 * Author: Yeray García Quintana
 * Date: 04.11.2018
 */

namespace Recipes\db;

use Ramsey\Uuid\Uuid;

class UuidHelper
{
    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    static public function newUUID()
    {
        try {
            return Uuid::uuid1();
        } catch (\Exception $e) {
            return null;
        }
    }
}
