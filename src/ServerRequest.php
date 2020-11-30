<?php namespace Tatter\Http;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Laminas\Diactoros\ServerRequest as BaseServerRequest;

class ServerRequest extends BaseServerRequest
{
	/**
	 * Stored framework IncomingRequest.
	 *
	 * @var IncomingRequest|null
	 */
	private static $framework;

	/**
	 * Creates the static IncomingRequest using the App config
	 *
	 * @param App $config
	 * @param URI $uri
	 * @param string|null $body
	 * @param UserAgent|null $userAgent
	 *
	 * @param App $config
	 */
	public static function framework(App $config, URI $uri, $body = 'php://input', UserAgent $userAgent = null): void
	{
		self::$framework = new IncomingRequest($config, $uri, $body, $userAgent);
	}
}
