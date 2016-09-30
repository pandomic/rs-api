<?php
namespace RightSignature\Element;

use RightSignature\Pattern\ConnectionInterface,
    RightSignature\Pattern\ElementInterface;

/**
 * Class Document
 * @package RightSignature\Element
 */
class Document implements ElementInterface
{
    /**
     * @var ConnectionInterface connection object
     */
    protected $connection;

    /**
     * @var bool|\StdClass loaded document
     */
    protected $document = false;

    /**
     * @var string template guid set by self::guid()
     * @see guid()
     */
    protected $guid;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set template guid
     * @param string $guid
     * @return self
     */
    public function guid($guid)
    {
        $this->guid = $guid;
        return $this;
    }

    /**
     * Load single document
     * @param null|string $guid document guid
     * @return self
     */
    public function load($guid = null)
    {
        $guid = $this->resolveGuid($guid);
        $document = $this->connection->connect('documents/' . $guid)->document;
        if (!empty($document)) {
            $this->document = $document;
        }
        return $this;
    }

    /**
     * Load list of documents
     * @param int $page page number to load
     * @param int $perPage items per page to load
     * @param null|string $state document state
     * @param null|string $search search string
     * @return array of documents
     */
    public function loadList($page = 1, $perPage = 10, $state = null, $search = null)
    {
        $arguments = [
            'page'     => $page,
            'per_page' => $perPage
        ];
        if ($search) {
            $arguments['search'] = $search;
        }
        if ($state) {
            $arguments['state'] = $state;
        }
        $documents = $this->connection->connect('documents', $arguments);
        if (!empty($documents->page->documents)) {
            if ($documents->page->total_templates == 1) {
                $documents = [$documents->page->documents->document];
            } else {
                $documents = $documents->page->documents;
            }

        }
        return $documents;
    }

    /**
     * Get documents count info
     * @return array(
     *     'total_documents' => int total documents count,
     *     'total_pages'     => int total pages count (for default 10 items per page settings)
     * )
     */
    public function getCount()
    {
        $templates = $this->connection->connect('documents')->page;
        return [
            'total_documents' => $templates->total_documents,
            'total_pages'     => $templates->total_pages,
        ];
    }

    /**
     * Get loaded document
     * @param null|string $name param name to get
     * @return \StdClass template
     */
    public function get($name = null)
    {
        if ($name) {
            return $this->document->{$name};
        }
        return $this->document;
    }

    /**
     * Load details for multiple documents (max 20)
     * @param array $guids array of documents guids
     * @return array of documents
     */
    public function batchDetails($guids)
    {
        $documents = [];
        if (!empty($guids)) {
            $guids = implode(',', $guids);
            $documents = $this->connection->connect('documents/' . $guids . '/batch_details');
            if (empty($documents->documents->document) && !empty($documents->documents)) {
                $documents = $documents->documents;
            } else if (!empty($documents->documents->document)) {
                $documents = [$documents->documents->document];
            }
        }
        return $documents;
    }

    /**
     * Delete document
     * @param null|string $guid document guid
     * @return bool status
     */
    public function trash($guid = null)
    {
        $guid = $this->resolveGuid($guid);
        $response = $this->connection->connect('documents/' . $guid . '/trash');
        return strpos($response->document->status, 'has been trashed') !== false;
    }

    /**
     * Extend documents expiration date
     * @param null|string $guid document guid
     * @return bool status
     */
    public function extendExpiration($guid = null)
    {
        $guid = $this->resolveGuid($guid);
        $response = $this->connection->connect('documents/' . $guid . '/extend_expiration');
        return strpos($response->document->status, 'expiration extended') !== false;
    }

    /**
     * Update documents tags
     * @param array $tags
     * @param null|string $guid document guid
     * @return bool status
     */
    public function updateTags($tags, $guid = null)
    {
        $guid = $this->resolveGuid($guid);
        $xml = new \SimpleXMLElement('<tags/>');
        foreach ($tags as $tag) {
            $tagNode = $xml->addChild('tag');
            if (!empty($tag['name'])) {
                $tagNode->addChild('name', $tag['name']);
            }
            if (!empty($tag['value'])) {
                $tagNode->addChild('value', $tag['value']);
            }
        }
        $response = $this->connection->connect(
            'documents/' . $guid . '/update_tags', [], [], 'POST', $xml->asXML()
        );
        return strpos($response->document->status, 'tags updated') !== false;
    }

    /**
     * Send document
     *
     * Allowed options:
     * - recipients
     * - subject
     * - expires_in
     * - description
     * - tags
     * - callback_location
     * - use_text_tags
     * - lock_signers
     * - passcode_question
     * - passcode_answer
     *
     * @see https://rightsignature.com/apidocs/api_documentation_default#/send_document
     * @param string $documentPath document to upload
     * @param array $options options
     * @return bool status
     */
    public function send($documentPath, $options = [])
    {
        $document = file_get_contents($documentPath);
        $document = base64_encode($document);

        preg_match('((?:\/|\\)?([a-z0-9_\-.\?%=&]+)$)i', $documentPath, $matches);
        $fileName = $matches[1];

        $xml = new \SimpleXMLElement('<document/>');

        $docData = $xml->addChild('document_data');
        $docData->addChild('type', 'base64');
        $docData->addChild('filename', $fileName);
        $docData->addChild('value', $document);

        $xml->addChild('action', 'send');

        if (!empty($options['use_text_tags'])) {
            $flag = $options['use_text_tags'] ? 'true' : 'false';
            $xml->addChild('use_text_tags', $flag);
        }

        if (!empty($options['lock_signers'])) {
            $flag = $options['lock_signers'] ? 'true' : 'false';
            $xml->addChild('lock_signers', $flag);
        }

        if (!empty($options['passcode_question'])) {
            $xml->addChild('passcode_question', $options['passcode_question']);
        }

        if (!empty($options['passcode_answer'])) {
            $xml->addChild('passcode_answer', $options['passcode_answer']);
        }

        if (!empty($options['subject'])) {
            $xml->addChild('subject', $options['subject']);
        }

        if (!empty($options['description'])) {
            $xml->addChild('description', $options['description']);
        }

        if (!empty($options['expires_in'])) {
            $xml->addChild('expires_in', $options['expires_in']);
        }

        if (!empty($options['callback_location'])) {
            $xml->addChild('callback_location', $options['callback_location']);
        }

        if (!empty($options['recipients'])) {
            $roles = $xml->addChild('recipients');
            foreach ($options['recipients'] as $role) {
                $roleNode = $roles->addChild('recipient');
                if (!empty($role['name'])) {
                    $roleNode->addChild('name', $role['name']);
                }
                if (!empty($role['email'])) {
                    $roleNode->addChild('email', $role['email']);
                }
                if (!empty($role['role'])) {
                    $roleNode->addChild('role', $role['role']);
                }
                if (!empty($role['locked'])) {
                    $flag = $role['locked'] ? 'true' : 'false';
                    $roleNode->addChild('locked', $flag);
                }
                if (!empty($role['is_sender'])) {
                    $flag = $role['is_sender'] ? 'true' : 'false';
                    $roleNode->addChild('is_sender', $flag);
                }
            }
        }

        if (!empty($options['tags'])) {
            $tags = $xml->addChild('tags');
            foreach ($options['tags'] as $tag) {
                $tagNode = $tags->addChild('tag');
                if (!empty($tag['name'])) {
                    $tagNode->addChild('name', $tag['name']);
                }
                if (!empty($tag['value'])) {
                    $tagNode->addChild('value', $tag['value']);
                }
            }
        }

        $response = $this->connection->connect(
            'documents', [], [], 'POST', $xml->asXML()
        );

        return strpos($response->document->status, 'sent') !== false;
    }

    /**
     * Update document callback
     * @param string $callback url
     * @param null|string $guid document guid
     * @return bool status
     */
    public function updateCallback($callback, $guid = null)
    {
        $guid = $this->resolveGuid($guid);
        $xml = new \SimpleXMLElement("<callback_location>{$callback}</callback_location>");
        $response = $this->connection->connect(
            'documents/' . $guid . '/update_callback', [], [], 'POST', $xml->asXML()
        );
        return strpos($response->document->status, 'location updated') !== false;
    }

    /**
     * Get document signer links
     * @param string $guid
     * @return array
     */
    public function getSignerLinks($guid = null) {
        $guid = $this->resolveGuid($guid);
        return $this->connection->connect('documents/' . $guid . '/signer_links')->document->signer_links;
    }

    /**
     * Get documents guid by priority
     * @param null|string $guid document guid
     * @return string guid
     */
    protected function resolveGuid($guid)
    {
        if ($guid == null) {
            if ($this->template) {
                return $this->template->guid;
            } else {
                return $this->guid;
            }
        }

        return $guid;
    }
}