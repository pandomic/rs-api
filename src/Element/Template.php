<?php
/**
 * Simple RightSignature token-based API
 *
 * @author Vlad Gramuzov <vlad.gramuzov@gmail.com>
 * @url https://github.com/pandomic/rs-api
 * @license MIT
 */

namespace RightSignature\Element;

use RightSignature\Pattern\ConnectionInterface,
    RightSignature\Pattern\ElementInterface,
    \SimpleXMLElement;

/**
 * Class Template
 * @package RightSignature\Element
 */
class Template implements ElementInterface
{
    /**
     * @var ConnectionInterface connection object
     */
    protected $connection;

    /**
     * @var bool|\StdClass loaded template
     */
    protected $template = false;

    /**
     * @var string template guid set by self::guid()
     * @see guid()
     */
    protected $guid;

    /**
     * Template constructor.
     * @param ConnectionInterface $connection
     */
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
     * Swap Template underlying document
     *
     * Allowed options:
     * - action
     * - subject
     * - description
     * - expires_in
     * - roles
     * - merge_fields
     * - tags
     * - callback_location
     *
     * @see https://rightsignature.com/apidocs/api_documentation_default#/swap_underlying_pdf
     * @param string $documentPath local path to the document to load
     * @param array $options swap options
     * @param null|string $guid prepackaged template guid
     * @return Document
     */
    public function swapTemplate($documentPath, $options, $guid = null)
    {
        preg_match('(([a-z0-9_\-.\?%=&]+)$)i', $documentPath, $matches);
        $fileName = $matches[1];

        $document = file_get_contents($documentPath);
        $document = base64_encode($document);

        $guid = $this->resolveGuid($guid);
        $xml = new SimpleXMLElement('<template/>');
        $action = !empty($options['action']) ? $options['action'] : 'prefill';

        $documentData = $xml->addChild('document_data');
        $documentData->addChild('type', 'base64');
        $documentData->addChild('filename', $fileName);
        $documentData->addChild('value', $document);

        $xml->addChild('guid', $guid);
        $xml->addChild('action', $action);
        $xml = $this->_prefill($xml, $options);

        $response = $this->connection->connect(
            'templates', [], [], 'POST', $xml->asXML()
        )->document;

        $document = new Document($this->connection);
        $document->guid($response->guid);

        return $document;
    }

    /**
     * Load single template
     * @param null|string $guid template guid
     * @return self
     */
    public function load($guid = null)
    {
        $guid = $this->resolveGuid($guid);
        $template = $this->connection->connect('templates/' . $guid)->template;
        if (!empty($template)) {
            $this->template = $template;
        }
        return $this;
    }

    /**
     * Prepackage template(s)
     *
     * Clone template or merge templates
     *
     * @param array $guids one or more template guids
     * @param string $callback callback URL
     * @return self
     */
    public function prepackage($guids = [], $callback = null)
    {
        $arguments = [];
        if (!is_array($guids)) {
            $guids = [$this->resolveGuid($guids)];
        } else if (empty($guids)) {
            $guids = [$this->resolveGuid(null)];
        }
        if ($callback !== null) {
            $arguments['callback_location'] = $callback;
        }
        $guids = implode(',', $guids);
        $template = $this->connection->connect('templates/' . $guids . '/prepackage', $arguments)->template;
        $this->template = $template;
        return $this;
    }

    /**
     * Fill template with data (update template data)
     *
     * Allowed options:
     * - subject
     * - roles
     * - description
     * - expires_in
     * - tags
     * - callback_location
     * - merge_fields
     * @see https://rightsignature.com/apidocs/api_documentation_default#/prefill_template
     * @param array $options
     * @param bool|string $guid template guid
     * @return self
     */
    public function prefill($options, $guid = null)
    {
        $guid = $this->resolveGuid($guid);

        $xml = new SimpleXMLElement('<template/>');
        $action = !empty($options['action']) ? $options['action'] : 'fill';

        $xml->addChild('guid', $guid);
        $xml->addChild('action', $action);
        $xml = $this->_prefill($xml, $options);

        $this->template = $this->connection->connect(
            'templates', [], [], 'POST', $xml->asXML()
        )->template;

        return $this;
    }

    /**
     * Prefill template and send it as document
     *
     * @see prefill() for allowed options
     * @see https://rightsignature.com/apidocs/api_documentation_default#/prefill_template
     *
     * @param array $options
     * @param null|string $guid prepackaged template guid
     * @return Document non-preloaded Document instance
     */
    public function prefillAndSend($options, $guid = null)
    {
        $guid = $this->resolveGuid($guid);
        $xml = new SimpleXMLElement('<template/>');
        $action = !empty($options['action']) ? $options['action'] : 'send';

        $xml->addChild('guid', $guid);
        $xml->addChild('action', $action);
        $xml = $this->_prefill($xml, $options);

        $response = $this->connection->connect(
            'templates', [], [], 'POST', $xml->asXML()
        )->document;

        $document = new Document($this->connection);
        $document->guid($response->guid);

        return $document;
    }

    /**
     * Load list of templates
     * @param int $page page number to load
     * @param int $perPage items per page to load
     * @param null|string $search search string
     * @return array of templates
     */
    public function loadList($page = 1, $perPage = 10, $search = null)
    {
        $arguments = [
            'page'     => $page,
            'per_page' => $perPage
        ];
        if ($search) {
            $arguments['search'] = $search;
        }
        $templates = $this->connection->connect('templates', $arguments);
        if (!empty($templates->page->templates)) {
            if ($templates->page->total_templates == 1) {
                $templates = [$templates->page->templates->template];
            } else {
                $templates = $templates->page->templates;
            }

        }
        return $templates;
    }

    /**
     * Get templates count info
     * @return array(
     *     'total_templates' => int total templates count,
     *     'total_pages'     => int total pages count (for default 10 items per page settings)
     * )
     */
    public function getCount()
    {
        $templates = $this->connection->connect('templates')->page;
        return [
            'total_templates' => $templates->total_templates,
            'total_pages'     => $templates->total_pages,
        ];
    }

    /**
     * Get loaded template
     * @param null|string $name param name to get
     * @return \StdClass template
     */
    public function get($name = null)
    {
        if ($name) {
            return $this->template->{$name};
        }
        return $this->template;
    }

    /**
     * Get template guid by priority
     * @param null|string $guid template guid
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

    /**
     * Bind xml data according to the given options
     * @param SimpleXMLElement $element
     * @param array $options
     * @return SimpleXMLElement
     */
    private function _prefill(SimpleXMLElement $element, $options)
    {
        if (!empty($options['subject'])) {
            $element->addChild('subject', $options['subject']);
        }

        if (!empty($options['description'])) {
            $element->addChild('description', $options['description']);
        }

        if (!empty($options['expires_in'])) {
            $element->addChild('expires_in', $options['expires_in']);
        }

        if (!empty($options['callback_location'])) {
            $element->addChild('callback_location', $options['callback_location']);
        }

        if (!empty($options['roles'])) {
            $roles = $element->addChild('roles');
            foreach ($options['roles'] as $role) {
                $roleNode = $roles->addChild('role');
                if (!empty($role['role_name'])) {
                    $roleNode->addAttribute('role_name', $role['role_name']);
                }
                if (!empty($role['role_id'])) {
                    $roleNode->addAttribute('role_id', $role['role_id']);
                }
                if (!empty($role['name'])) {
                    $roleNode->addChild('name', $role['name']);
                }
                if (!empty($role['email'])) {
                    $roleNode->addChild('email', $role['email']);
                }
            }
        }

        if (!empty($options['tags'])) {
            $tags = $element->addChild('tags');
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

        if (!empty($options['merge_fields'])) {
            $tags = $element->addChild('merge_fields');
            foreach ($options['merge_fields'] as $field) {
                $fieldNode = $tags->addChild('merge_field');
                if (!empty($field['merge_field_id'])) {
                    $fieldNode->addAttribute('merge_field_id', $field['merge_field_id']);
                }
                if (!empty($field['merge_field_name'])) {
                    $fieldNode->addAttribute('merge_field_name', $field['merge_field_name']);
                }
                if (!empty($field['value'])) {
                    $fieldNode->addChild('value', $field['value']);
                }
                if (!empty($field['locked'])) {
                    $fieldNode->addChild('locked', $field['locked']);
                }
            }
        }

        return $element;
    }
}