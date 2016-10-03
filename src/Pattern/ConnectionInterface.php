<?php
/**
 * Simple RightSignature token-based API
 *
 * @author Vlad Gramuzov <vlad.gramuzov@gmail.com>
 * @url https://github.com/pandomic/rs-api
 * @license MIT
 */

namespace RightSignature\Pattern;

interface ConnectionInterface
{
    /**
     * ConnectionInterface constructor.
     * @param array $params
     */
    public function __construct($params);

    /**
     * Connect to api
     * @param string $apiMethod
     * @param array $arguments
     * @param array $headers
     * @param bool $method
     * @param bool $body
     * @return mixed
     */
    public function connect($apiMethod, $arguments = [], $headers= [], $method = false, $body = false);
}