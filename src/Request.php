<?php
namespace RightSignature;

use RightSignature\Exception\ConnectionException,
    RightSignature\Exception\ParseException,
    RightSignature\Exception\ApiException;

/**
 * Class Request
 * @package RightSignature
 */
class Request
{
    /**
     * @var string request url
     */
    protected $url;

    /**
     * @var array request headers
     */
    protected $headers = [];

    /**
     * @var string request method
     */
    protected $method = 'GET';

    /**
     * @var null|string request body in XML format
     */
    protected $body = null;

    /**
     * @var request api token
     */
    protected $token;

    /**
     * Request constructor.
     * @param bool|string $token
     * @param bool|string $url
     * @param bool|array $headers
     * @param bool|string $method
     * @param bool|string $body
     */
    public function __construct($token = false, $url = false, $headers = false, $method = false, $body = false)
    {
        if ($token) {
            $this->token($token);
        }
        if ($url) {
            $this->url($url);
        }
        if ($headers) {
            $this->headers($headers, false);
        }
        if ($method) {
            $this->method($method);
        }
        if ($body) {
            $this->body($body);
        }
    }

    /**
     * Set api token
     * @param string $token
     * @return self;
     */
    public function token($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Set request url
     * @param string $url
     * @return self
     */
    public function url($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set headers
     * @param array $headers
     * @param bool $append append or replace headers
     * @return self
     */
    public function headers($headers, $append = true)
    {
        if ($append) {
            $this->headers = array_merge_recursive($this->headers, $headers);
        } else {
            $this->headers = $headers;
        }
        return $this;
    }

    /**
     * Add single header
     * @param string $header
     * @return self
     */
    public function header($header)
    {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * Set request method
     * @param string $method
     * @return self
     */
    public function method($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Set request XML body
     * @param string $body
     * @return self
     */
    public function body($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Process request
     * @return Response
     * @throws ApiException
     * @throws ConnectionException
     * @throws ParseException
     */
    public function process()
    {
        $this->body = str_ireplace(
            '<?xml version="1.0"?>',
            '<?xml version="1.0" encoding="UTF-8"?>',
            $this->body
        );

        $this
            ->header("Content-Type: text/xml;charset=utf-8")
            ->header("api-token: $this->token");

        $response = $this->send();

        if ($response === false) {
            throw new ConnectionException('API connection error.');
        }

        $response = json_decode($response);

        if ($response === null || $response === false) {
            throw new ParseException('Response can not be parsed');
        } else if (!empty($response->error)) {
            throw new ApiException($response->error->message);
        }

        return new Response($response);
    }

    public function send()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->body);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}