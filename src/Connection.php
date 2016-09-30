<?php
namespace RightSignature;

use RightSignature\Pattern\ConnectionInterface;

/**
 * Class Connection
 * @package RightSignature
 */
class Connection implements ConnectionInterface
{
    /**
     * @var string api token
     */
    protected $token;

    /**
     * @var string base api url
     */
    protected $baseUrl = 'https://rightsignature.com/api/';

    /**
     * Connection constructor.
     * @param array $params
     */
    public function __construct($params)
    {
        $this->token = $params['token'];
        if (!empty($params['base_url'])) {
            $this->baseUrl = $params['base_url'];
        }
    }

    /**
     *
     * @param $apiMethod
     * @param array $arguments
     * @param array $headers
     * @param string $method
     * @param bool $body
     * @return Request|Response
     */
    public function connect($apiMethod, $arguments = [], $headers = [], $method = 'GET', $body = false)
    {
        $arguments = empty($arguments)
            ? ''
            : '?' . http_build_query($arguments);
        $request = new Request(
            $this->token,
            $this->baseUrl . $apiMethod . '.json' . $arguments,
            $headers, $method, $body
        );

        $request = $request->process();

        return $request;
    }


}