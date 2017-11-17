<?php
/**
 * Created by PhpStorm.
 * User: kyslik
 * Date: 04/11/17
 * Time: 9:02 PM
 */

namespace Kyslik\LaravelFilterable\Exceptions;

use Exception;
use Throwable;

class MissingBuilderInstance extends Exception
{

    /**
     * MissingBuilderInstance constructor.
     *
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = 'Builder instance must be set before applying filters.';
        parent::__construct($message, $code, $previous);
    }

}