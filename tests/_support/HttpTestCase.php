<?php namespace Tests\Support;

use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tatter\Http\Response;
use Tatter\Http\ServerRequest;

class HttpTestCase extends CIUnitTestCase
{
	/**
	 * Initializes the framework HTTP references.
	 */
	public static function setUpBeforeClass(): void
	{
		ServerRequest::framework(config('App'), service('uri'), 'php://input', new UserAgent());
	}

	public function setUp(): void
	{
		parent::setUp();

		// Mock the Services to simulate having configured them in App
		Services::injectMock('request', new ServerRequest());

		// Mock the Services to simulate having configured them in App
		Services::injectMock('response', new Response());
	}
}
