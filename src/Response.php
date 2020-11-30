<?php namespace Tatter\Http;

use CodeIgniter\HTTP\ResponseInterface;
use Config\App;
use DateTime;
use DateTimeZone;
use Laminas\Diactoros\Response as BaseResponse;

class Response extends BaseResponse implements ResponseInterface
{
	/**
	 * Return an instance with the specified status code and, optionally, reason phrase.
	 *
	 * If no reason phrase is specified, will default recommended reason phrase for
	 * the response's status code.
	 *
	 * @see http://tools.ietf.org/html/rfc7231#section-6
	 * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 *
	 * @param integer $code   The 3-digit integer result code to set.
	 * @param string  $reason The reason phrase to use with the
	 *                        provided status code; if none is provided, will
	 *                        default to the IANA name.
	 *
	 * @return $this
	 * @throws HTTPException For invalid status code arguments.
	 */
	public function setStatusCode(int $code, string $reason = '')
	{
		// Valid range?
		if ($code < 100 || $code > 599)
		{
			throw HTTPException::forInvalidStatusCode($code);
		}

		// Unknown and no message?
		if (! array_key_exists($code, static::$statusCodes) && empty($reason))
		{
			throw HTTPException::forUnkownStatusCode($code);
		}

		$this->statusCode = $code;

		if (! empty($reason))
		{
			$this->reason = $reason;
		}
		else
		{
			$this->reason = static::$statusCodes[$code];
		}

		return $this;
	}

	/**
	 * Gets the response response phrase associated with the status code.
	 *
	 * @see http://tools.ietf.org/html/rfc7231#section-6
	 * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 *
	 * @return string
	 */
	public function getReason(): string
	{
		return $this->getReasonPhrase();
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Convenience Methods
	//--------------------------------------------------------------------

	/**
	 * Sets the date header
	 *
	 * @param DateTime $date
	 *
	 * @return Response
	 */
	public function setDate(DateTime $date)
	{
		$date->setTimezone(new DateTimeZone('UTC'));

		$this->setHeader('Date', $date->format('D, d M Y H:i:s') . ' GMT');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the Content Type header for this response with the mime type
	 * and, optionally, the charset.
	 *
	 * @param string $mime
	 * @param string $charset
	 *
	 * @return Response
	 */
	public function setContentType(string $mime, string $charset = 'UTF-8')
	{
		// add charset attribute if not already there and provided as parm
		if ((strpos($mime, 'charset=') < 1) && ! empty($charset))
		{
			$mime .= '; charset=' . $charset;
		}

		$this->removeHeader('Content-Type'); // replace existing content type
		$this->setHeader('Content-Type', $mime);

		return $this;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Cache Control Methods
	//
	// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
	//--------------------------------------------------------------------

	/**
	 * Sets the appropriate headers to ensure this response
	 * is not cached by the browsers.
	 */
	public function noCache()
	{
		$this->removeHeader('Cache-control');

		$this->setHeader('Cache-control', ['no-store', 'max-age=0', 'no-cache']);
	}

	//--------------------------------------------------------------------

	/**
	 * A shortcut method that allows the developer to set all of the
	 * cache-control headers in one method call.
	 *
	 * The options array is used to provide the cache-control directives
	 * for the header. It might look something like:
	 *
	 *      $options = [
	 *          'max-age'  => 300,
	 *          's-maxage' => 900
	 *          'etag'     => 'abcde',
	 *      ];
	 *
	 * Typical options are:
	 *  - etag
	 *  - last-modified
	 *  - max-age
	 *  - s-maxage
	 *  - private
	 *  - public
	 *  - must-revalidate
	 *  - proxy-revalidate
	 *  - no-transform
	 *
	 * @param array $options
	 *
	 * @return Response
	 */
	public function setCache(array $options = [])
	{
		if (empty($options))
		{
			return $this;
		}

		$this->removeHeader('Cache-Control');
		$this->removeHeader('ETag');

		// ETag
		if (isset($options['etag']))
		{
			$this->setHeader('ETag', $options['etag']);
			unset($options['etag']);
		}

		// Last Modified
		if (isset($options['last-modified']))
		{
			$this->setLastModified($options['last-modified']);

			unset($options['last-modified']);
		}

		$this->setHeader('Cache-control', $options);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the Last-Modified date header.
	 *
	 * $date can be either a string representation of the date or,
	 * preferably, an instance of DateTime.
	 *
	 * @param DateTime|string $date
	 *
	 * @return Response
	 */
	public function setLastModified($date)
	{
		if ($date instanceof DateTime)
		{
			$date->setTimezone(new DateTimeZone('UTC'));
			$this->setHeader('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');
		}
		elseif (is_string($date))
		{
			$this->setHeader('Last-Modified', $date);
		}

		return $this;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Output Methods
	//--------------------------------------------------------------------

	/**
	 * Sends the output to the browser.
	 *
	 * @return Response
	 */
	public function send()
	{
		// If we're enforcing a Content Security Policy,
		// we need to give it a chance to build out it's headers.
		if ($this->CSPEnabled === true)
		{
			$this->CSP->finalize($this);
		}
		else
		{
			$this->body = str_replace(['{csp-style-nonce}', '{csp-script-nonce}'], '', $this->body);
		}

		$this->sendHeaders();
		$this->sendCookies();
		$this->sendBody();

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sends the headers of this HTTP request to the browser.
	 *
	 * @return Response
	 */
	public function sendHeaders()
	{
		// Have the headers already been sent?
		if ($this->pretend || headers_sent())
		{
			return $this;
		}

		// Per spec, MUST be sent with each request, if possible.
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html
		if (! isset($this->headers['Date']) && php_sapi_name() !== 'cli-server')
		{
			$this->setDate(DateTime::createFromFormat('U', (string) time()));
		}

		// HTTP Status
		header(sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $this->statusCode, $this->reason), true, $this->statusCode);

		// Send all of our headers
		foreach ($this->getHeaders() as $name => $values)
		{
			header($name . ': ' . $this->getHeaderLine($name), true, $this->statusCode);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sends the Body of the message to the browser.
	 *
	 * @return Response
	 */
	public function sendBody()
	{
		echo $this->body;

		return $this;
	}
}
