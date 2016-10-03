<?php
/**
 * Simple RightSignature token-based API
 *
 * @author Vlad Gramuzov <vlad.gramuzov@gmail.com>
 * @url https://github.com/pandomic/rs-api
 * @license MIT
 */

namespace RightSignature\Pattern;

/**
 * Interface ElementInterface
 * @package RightSignature\Pattern
 */
interface ElementInterface
{
    /**
     * ElementInterface constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection);
}