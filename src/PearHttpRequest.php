<?php

declare(strict_types=1);

namespace Uzulla\ForLegacy\HttpRequest;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;

class PearHttpRequest implements PearHttpRequestInterface
{
    private $client;
    private $response;
    private $url;
    private $method = 'GET';
    private $headers = [];
    private $queryParams = [];
    private $postData = [];
    private $body;

    public function __construct()
    {
        $this->client = new Client();
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

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function sendRequest($saveBody = true)
    {
        $options = [
            'headers' => $this->headers,
            'query' => $this->queryParams,
        ];

        if ($this->method === 'POST' || $this->method === 'PUT') {
            if (!empty($this->postData)) {
                $options['form_params'] = $this->postData;
            } elseif ($this->body) {
                $options['body'] = $this->body;
            }
        }

        try {
            $this->response = $this->client->request($this->method, $this->url, $options);
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
        $this->client->setDefaultOption('proxy', [
            'http' => "tcp://{$host}:{$port}",
            'https' => "tcp://{$host}:{$port}"
        ]);

        if ($user !== null && $pass !== null) {
            $this->client->setDefaultOption('auth', [$user, $pass]);
        }
    }

    public function setBasicAuth($user, $pass)
    {
        $this->client->setDefaultOption('auth', [$user, $pass, 'basic']);
    }

    public function setHttpVer($http)
    {
        if ($http === '1.0') {
            $this->client->setDefaultOption('version', '1.0');
        } elseif ($http === '1.1') {
            $this->client->setDefaultOption('version', '1.1');
        } else {
            throw new InvalidArgumentException('Invalid HTTP version. Supported versions are 1.0 and 1.1');
        }
    }

    public function addRawQueryString($querystring, $preencoded = true)
    {
        if (!$preencoded) {
            $querystring = http_build_query($querystring);
        }

        $currentUri = $this->client->getConfig('base_uri');
        $newUri = $currentUri->withQuery($currentUri->getQuery() ? $currentUri->getQuery() . '&' . $querystring : $querystring);

        $this->client->setConfig(['base_uri' => $newUri]);
    }

    public function addFile($inputName, $fileName, $contentType = 'application/octet-stream')
    {
        $this->client->setDefaultOption('multipart', [
            [
                'name' => $inputName,
                'contents' => fopen($fileName, 'r'),
                'filename' => basename($fileName),
                'headers' => [
                    'Content-Type' => $contentType
                ]
            ]
        ]);
    }

    public function addCookie($name, $value)
    {
        $this->client->setDefaultOption('cookies', function ($cookieJar) use ($name, $value) {
            $cookieJar->setCookie(new SetCookie([
                'Name' => $name,
                'Value' => $value,
                'Domain' => $this->client->getConfig('base_uri')->getHost(),
                'Path' => '/',
            ]));
            return $cookieJar;
        });
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
        // TODO: Implement disconnect() method.
    }

    public function attach(&$listener)
    {
        // TODO: Implement attach() method.
    }

    public function detach(&$listener)
    {
        // TODO: Implement detach() method.
    }
}
