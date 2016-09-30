<?php
namespace RightSignature;

class Response
{
    protected $_responseData = [];

    public function __construct($data) {
        $this->_responseData = $data;
    }

    public function setData($data) {
        $this->_responseData = $data;
    }

    public function __get($name)
    {
        return $this->_responseData->{$name};
    }
}