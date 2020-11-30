<?php

use Config\Services;
use Tatter\Http\ServerRequest;
use Tests\Support\HttpTestCase;

class ServicesTest extends HttpTestCase
{
	public function testRequestReturnsServerRequest()
	{
		$result = Services::request();

		$this->assertInstanceOf(ServerRequest::class, $result);
	}
}
