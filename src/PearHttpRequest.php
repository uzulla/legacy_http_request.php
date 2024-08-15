<?php

declare(strict_types=1);

namespace Uzulla\ForLegacy\HttpRequest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use LogicException;

class PearHttpRequest implements PearHttpRequestInterface
{
    const HTTP_REQUEST_METHOD_GET = 'GET';
    const HTTP_REQUEST_METHOD_HEAD = 'HEAD';
    const HTTP_REQUEST_METHOD_POST = 'POST';
    const HTTP_REQUEST_METHOD_PUT = 'PUT';
    const HTTP_REQUEST_METHOD_DELETE = 'DELETE';
    const HTTP_REQUEST_METHOD_OPTIONS = 'OPTIONS';
    const HTTP_REQUEST_METHOD_TRACE = 'TRACE';

    /** @var Client */
    private $client;
    private $response;
    private $url;
    private $method = 'GET';
    private $headers = [];
    private $queryParams = [];
    private $postData = [];
    /** @var array */
    private $options = [];
    /** @var string */
    private $basicAuthUsername;
    /** @var string */
    private $basicAuthPassword;
    private $body;

    public function __construct($url = '', $params = [])
    {
        if (strlen($url) > 0) {
            $this->setURL($url);
        }
        if (count($params) > 0) {
            throw new LogicException('Not implemented yet');
        }
    }

    public function setURL($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function removeHeader($name)
    {
        unset($this->headers[$name]);
    }

    public function addQueryString($name, $value, $preencoded = false)
    {
        $this->queryParams[$name] = $preencoded ? $value : urlencode($value);
    }

    public function addPostData($name, $value, $preencoded = false)
    {
        $this->postData[$name] = $preencoded ? $value : urlencode($value);
    }

    public function addRawPostData($postdata, $preencoded = true)
    {
        $this->body = $preencoded ? $postdata : urlencode($postdata);
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function sendRequest($saveBody = true)
    {
        $this->options = array_merge([
            'headers' => $this->headers,
            'query' => $this->queryParams,
        ], $this->options);

        if ($this->method === 'POST' || $this->method === 'PUT') {
            if (!empty($this->postData)) {
                $this->options['form_params'] = $this->postData;
            } elseif (isset($this->body)) {
                if (!isset($this->headers['Content-Type'])) {
                    $this->options['headers'] = array_merge(
                    // POSTでContent-Type未指定の場合はapplication/x-www-form-urlencodedにFallbackする
                        ['Content-Type' => 'application/x-www-form-urlencoded'],
                        $this->options['headers']
                    );
                }
                $this->options['body'] = $this->body;
            }
        }

        if (isset($this->basicAuthUsername)) {
            $this->options = array_merge([
                'auth' => [
                    $this->basicAuthUsername,
                    $this->basicAuthPassword,
                    'basic'
                ]
            ], $this->options);
        }

        try {
            $this->client = new Client();
            $this->response = $this->client->request($this->method, $this->url, $this->options);
            return true;
        } catch (RequestException $e) {
            $this->response = $e->getResponse();
            return false;
        }
    }

    public function getResponseCode()
    {
        return $this->response ? $this->response->getStatusCode() : false;
    }

    public function getResponseHeader($headername = null)
    {
        if (!$this->response) {
            return false;
        }

        if ($headername === null) {
            return $this->response->getHeaders();
        }

        return $this->response->getHeader($headername);
    }

    public function getResponseBody()
    {
        return $this->response ? (string)$this->response->getBody() : false;
    }

    public function setProxy($host, $port = 8080, $user = null, $pass = null)
    {
        throw new LogicException('Not implemented yet');
    }

    public function setBasicAuth($user, $pass)
    {
        $this->basicAuthUsername = $user;
        $this->basicAuthPassword = $pass;
    }

    public function setHttpVer($http)
    {
        throw new LogicException('Not implemented yet');
    }

    public function addRawQueryString($querystring, $preencoded = true)
    {
        throw new LogicException('Not implemented yet');
    }

    public function addFile($inputName, $fileName, $contentType = 'application/octet-stream')
    {
        throw new LogicException('Not implemented yet');
    }

    public function addCookie($name, $value)
    {
        throw new LogicException('Not implemented yet');
    }

    public function getResponseReason()
    {
        $response = $this->client->getResponse();
        return $response->getReasonPhrase();
    }

    public function getResponseCookies()
    {
        $response = $this->client->getResponse();
        $cookieJar = $response->getCookies();
        $cookies = [];

        foreach ($cookieJar as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }

        return $cookies;
    }

    public function disconnect()
    {
        throw new LogicException('Not implemented yet');
    }

    public function attach(&$listener)
    {
        throw new LogicException('Not implemented yet');
    }

    public function detach(&$listener)
    {
        throw new LogicException('Not implemented yet');
    }
}
