<?php namespace Tatter\Http;

use CodeIgniter\HTTP\Request as FrameworkRequest;
use CodeIgniter\HTTP\RequestInterface;
use Config\App;
use Laminas\Diactoros\Request as BaseRequest;

class Request extends BaseRequest implements RequestInterface
{
	/**
	 * Stored framework Request.
	 *
	 * @var FrameworkRequest|null
	 */
	private static $framework;

	/**
	 * Creates the static FrameworkRequest using the App config
	 *
	 * @param App $config
	 */
	public static function framework(App $config): void
	{
		self::$framework = new FrameworkRequest($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the user's IP address.
	 *
	 * @return string IP address
	 *
	 * @todo Deprecate in framework and remove here
	 */
	public function getIPAddress(): string
	{
		return self::$framework->ci()->getIPAddress();
	}

	/**
	 * Validates an IP address.
	 * Copied from the framework.
	 *
	 * @param string $ip    IP Address
	 * @param string $which IP protocol: 'ipv4' or 'ipv6'
	 *
	 * @return boolean
	 *
	 * @todo Deprecate in framework and remove here
	 */
	public function isValidIP(string $ip = null, string $which = null): bool
	{
		return self::$framework->isValidIP($ip, $which);
	}

	//--------------------------------------------------------------------

	/**
	 * Get the request method.
	 *
	 * @param boolean $upper Whether to return in upper or lower case.
	 *
	 * @return string
	 */
	public function getMethod($upper = false): string
	{
		$method = parent::getMethod();

		return $upper ? strtoupper($method) : $method;
	}

	/**
	 * Fetch an item from the $_SERVER array.
	 *
	 * @param string|array|null $index  Index for item to be fetched from $_SERVER
	 * @param integer|null      $filter A filter name to be applied
	 * @param null              $flags
	 *
	 * @return mixed
	 */
	public function getServer($index = null, $filter = null, $flags = null)
	{
		return self::$framework->getServer($index, $filter, $flags);
	}
}
