<?php
namespace RightSignature\Element;

use RightSignature\Pattern\ConnectionInterface,
    RightSignature\Pattern\ElementInterface;
use RightSignature\Request;

;

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
     * @return self
     */
    public function prepackage($guids = [], $callback = null)
    {
        $arguments = [];
        if (!is_array($guids)) {
            $guids = [$this->resolveGuid($guids)];
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
        $options['guid'] = $guid;

        $xml = new \SimpleXMLElement('<template/>');

        $xml->addChild('guid', $guid);
        $xml->addChild('action', 'fill');

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

        if (!empty($options['roles'])) {
            $roles = $xml->addChild('roles');
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

        if (!empty($options['merge_fields'])) {
            $tags = $xml->addChild('merge_fields');
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

        $this->template = $this->connection->connect(
            'templates', [], [], 'POST', $xml->asXML()
        )->template;

        return $this;
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
}