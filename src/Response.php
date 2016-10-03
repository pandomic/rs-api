<?php
/**
 * Simple RightSignature token-based API
 *
 * @author Vlad Gramuzov <vlad.gramuzov@gmail.com>
 * @url https://github.com/pandomic/rs-api
 * @license MIT
 */

namespace RightSignature;

/**
 * Class Response
 * @package RightSignature
 */
class Response
{
    /**
     * @var mixed response data
     */
    protected $_responseData;

    /**
     * Response constructor.
     * @param mixed $data
     */
    public function __construct($data) {
        $this->_responseData = $data;
    }

    /**
     * Set response data
     * @param mixed $data
     */
    public function setData($data) {
        $this->_responseData = $data;
    }

    /**
     * Response data getter
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_responseData->{$name};
    }
}