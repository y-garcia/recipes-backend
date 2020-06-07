<?php
/**
 * Author: Yeray García Quintana
 * Date: 23.06.2018
 */

namespace Recipes\model;

class ResultDto
{
    public $status;
    public $code;
    public $message;
    public $result;

    /**
     * ResultDto constructor.
     * @param $status
     * @param $message
     * @param $code
     * @param $result
     */
    public function __construct($status, $code, $message, $result)
    {
        $this->status = $status;
        $this->code = $code;
        $this->message = $message;
        $this->result = $result;
    }

    static public function ok($data)
    {
        return new ResultDto(200, null, null, $data);
    }
}
