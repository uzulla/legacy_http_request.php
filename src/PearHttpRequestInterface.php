<?php

declare(strict_types=1);

namespace Uzulla\ForLegacy\HttpRequest;

/**
 * The interface is similar as PEAR::HTTP_Request
 */
interface PearHttpRequestInterface
{
    public function setURL($url);
    public function getUrl();
    public function setProxy($host, $port = 8080, $user = null, $pass = null);
    public function setBasicAuth($user, $pass);
    public function setMethod($method);
    public function setHttpVer($http);
    public function addHeader($name, $value);
    public function removeHeader($name);
    public function addQueryString($name, $value, $preencoded = false);
    public function addRawQueryString($querystring, $preencoded = true);
    public function addPostData($name, $value, $preencoded = false);
    public function addRawPostData($postdata, $preencoded = true);
    public function addFile($inputName, $fileName, $contentType = 'application/octet-stream');
    public function addCookie($name, $value);
    public function sendRequest($saveBody = true);
    public function getResponseCode();
    public function getResponseReason();
    public function getResponseHeader($headername = null);
    public function getResponseBody();
    public function getResponseCookies();
    public function disconnect();
    public function attach(&$listener);
    public function detach(&$listener);
}
