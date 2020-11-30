<?php namespace Tatter\Http\Config;

use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services as BaseServices;
use Tatter\Http\ServerRequest;

/**
 * Services Class
 *
 * Defines our version of the HTTP services to override
 * the framework defaults.
 */
class Services extends BaseServices
{
	/**
	 * @param App|null $config
	 * @param boolean  $getShared
	 *
	 * @return ServerRequest
	 */
	public static function request(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('request', $config);
		}

		ServerRequest::framework($config ?? config('App'), service('uri'), 'php://input', new UserAgent());

		return new ServerRequest();
	}
}
