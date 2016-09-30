<?php
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
     * @var ConnectionIn
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