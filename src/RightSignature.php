<?php
/**
 * Simple RightSignature token-based API
 *
 * @author Vlad Gramuzov <vlad.gramuzov@gmail.com>
 * @url https://github.com/pandomic/rs-api
 * @license MIT
 */

namespace RightSignature;

use RightSignature\Element\Template,
    RightSignature\Element\Document;

/**
 * Class RightSignature
 * @package RightSignature
 */
class RightSignature
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * RightSignature constructor.
     * @param $token
     */
    public function __construct($token)
    {
        $this->connection = new Connection(['token' => $token]);
    }

    /**
     * Construct document
     * @param null|string $guid document guid
     * @return Document
     */
    public function document($guid = null)
    {
        $document = new Document($this->connection);
        if ($guid) {
            $document->load($guid);
        }
        return $document;
    }

    /**
     * Construct Template
     * @param null|string $guid template guid
     * @return Template
     */
    public function template($guid = null)
    {
        $template = new Template($this->connection);
        if ($guid) {
            $template->load($guid);
        }
        return $template;
    }
}