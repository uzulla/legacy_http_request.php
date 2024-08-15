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
        $this->request->setMethod('GET');
        $this->request->addHeader('User-Agent', 'TestAgent');

        $result = $this->request->sendRequest();

        $this->assertTrue($result);
        $this->assertEquals(200, $this->request->getResponseCode());
        $this->assertStringContainsString('<title>phpinfo()</title>', $this->request->getResponseBody());
    }
}
