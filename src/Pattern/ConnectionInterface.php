<?php
namespace RightSignature\Pattern;

interface ConnectionInterface
{
    public function __construct($params);
    public function connect($apiMethod, $arguments = [], $headers= [], $method = false, $body = false);
}