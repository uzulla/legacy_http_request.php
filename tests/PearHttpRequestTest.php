<?php

namespace Uzulla\ForLegacy\HttpRequest\Tests;

use PHPUnit\Framework\TestCase;
use Uzulla\ForLegacy\HttpRequest\PearHttpRequest;

class PearHttpRequestTest extends TestCase
{
    /** @var PearHttpRequest */
    protected $request;

    /** @var TestHttpServer */
    protected $testServer;

    const TEST_SERVER_URL = 'http://127.0.0.1:8080/';

    protected function setUp(): void
    {
        $this->testServer = new TestHttpServer();
        $this->testServer->start();
    }

    protected function tearDown(): void
    {
        $this->testServer = null;
    }

    public function testSendRequest()
    {
        $this->request = new PearHttpRequest();

        $this->request->setURL(self::TEST_SERVER_URL);
        $this->request->setMethod(PearHttpRequest::HTTP_REQUEST_METHOD_GET);
        $this->request->addHeader('User-Agent', 'TestAgent');

        $result = $this->request->sendRequest();

        $this->assertTrue($result);
        $this->assertEquals(200, $this->request->getResponseCode());
        $this->assertStringContainsString('<title>phpinfo()</title>', $this->request->getResponseBody());
    }

    public function testSetUrlWithConstructor()
    {
        $this->request = new PearHttpRequest(self::TEST_SERVER_URL);

        $result = $this->request->sendRequest();

        $this->assertTrue($result);
        $this->assertEquals(200, $this->request->getResponseCode());
        $this->assertStringContainsString('<title>phpinfo()</title>', $this->request->getResponseBody());
    }

    public function testAddQueryString()
    {
        $this->request = new PearHttpRequest();

        $this->request->setURL(self::TEST_SERVER_URL);
        $this->request->setMethod(PearHttpRequest::HTTP_REQUEST_METHOD_GET);
        $this->request->addHeader('User-Agent', 'TestAgent');
        $this->request->addQueryString('foo', 'bar');
        $this->request->addQueryString('baz', 'qux', true);

        $this->request->sendRequest();

        // phpinfoをつかってチェックしているので、変なAssertです
        $this->assertStringContainsString('<tr><td class="e">$_SERVER[\'REQUEST_URI\']</td><td class="v">/?foo=bar&amp;baz=qux</td></tr>', $this->request->getResponseBody());
        $this->assertStringContainsString('<tr><td class="e">$_SERVER[\'REQUEST_METHOD\']</td><td class="v">GET</td></tr>', $this->request->getResponseBody());
    }

    public function testAddPostData()
    {
        $this->request = new PearHttpRequest();

        $this->request->setURL(self::TEST_SERVER_URL);
        $this->request->setMethod(PearHttpRequest::HTTP_REQUEST_METHOD_POST);
        $this->request->addHeader('User-Agent', 'TestAgent');
        $this->request->addPostData('foo', 'bar');
        $this->request->addPostData('baz', 'qux', true);

        $this->request->sendRequest();

        $this->assertStringContainsString('<tr><td class="e">$_SERVER[\'REQUEST_METHOD\']</td><td class="v">POST</td></tr>', $this->request->getResponseBody());
        $this->assertStringContainsString('<tr><td class="e">$_POST[\'foo\']</td><td class="v">bar</td></tr>', $this->request->getResponseBody());
        $this->assertStringContainsString('<tr><td class="e">$_POST[\'baz\']</td><td class="v">qux</td></tr>', $this->request->getResponseBody());
    }

    public function testSetBasicAuth()
    {
        $this->request = new PearHttpRequest();

        $this->request->setURL(self::TEST_SERVER_URL);
        $this->request->setMethod(PearHttpRequest::HTTP_REQUEST_METHOD_GET);
        $this->request->addHeader('User-Agent', 'TestAgent');
        $this->request->setBasicAuth('test_basic_auth_user', 'test_basic_auth_pass');

        $this->request->sendRequest();

        $this->assertStringContainsString('<tr><td class="e">$_SERVER[\'PHP_AUTH_USER\']</td><td class="v">test_basic_auth_user</td></tr>', $this->request->getResponseBody());
        $this->assertStringContainsString('<tr><td class="e">$_SERVER[\'PHP_AUTH_PW\']</td><td class="v">test_basic_auth_pass</td></tr>', $this->request->getResponseBody());
    }

    public function testSetBody()
    {
        $this->request = new PearHttpRequest();

        $this->request->setURL(self::TEST_SERVER_URL);
        $this->request->setMethod(PearHttpRequest::HTTP_REQUEST_METHOD_POST);
        $this->request->addHeader('User-Agent', 'TestAgent');
        $this->request->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->request->setBody('this_is=raw_body');

        $this->request->sendRequest();

        $this->assertStringContainsString('<tr><td class="e">$_POST[\'this_is\']</td><td class="v">raw_body</td></tr>', $this->request->getResponseBody());
    }

    public function testAddRawPostData()
    {
        $this->request = new PearHttpRequest();

        $this->request->setURL(self::TEST_SERVER_URL);
        $this->request->setMethod(PearHttpRequest::HTTP_REQUEST_METHOD_POST);
        $this->request->addHeader('User-Agent', 'TestAgent');
        $this->request->addRawPostData('this_is=raw_body');

        $this->request->sendRequest();

        $this->assertStringContainsString('<tr><td class="e">$_POST[\'this_is\']</td><td class="v">raw_body</td></tr>', $this->request->getResponseBody());
    }
}
