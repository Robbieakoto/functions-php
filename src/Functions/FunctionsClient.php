<?php

/**
 * A PHP client library to interact with Supabase Edge Functions.
 */

namespace Supabase\Functions;

use Psr\Http\Message\ResponseInterface;
use Supabase\Util\Request;

class FunctionsClient
{
	/**
	 * Location to call the function endpoint.
	 *
	 * @var string
	 */
	protected string $url;

	/**
	 * Last response.
	 *
	 * @var ResponseInterface 
	 */
	protected mixed $lastResponse;

	/**
	 * A header Bearer Token generated by the server in response to a login request
	 * [service key, not anon key].
	 *
	 * @var array
	 */
	protected array $headers = [];

	/**
	 * Get the url.
	 */
	public function __getUrl(): string
	{
		return $this->url;
	}

	/**
	 * Get the headers.
	 */
	public function __getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * Get Last Response.
	 */
	public function __getLastResponse(): ResponseInterface 
	{
		return $this->lastResponse;
	}

	/**
	 * FunctionsClient constructor.
	 *
	 * @param  string  $reference_id  Reference ID
	 * @param  string  $api_key  The anon or service role key
	 * @param  string  $domain  The domain pointing to api
	 * @param  string  $scheme  The api sheme
	 *
	 * @throws Exception
	 */
	public function __construct($reference_id, $api_key, $domain = 'supabase.co', $scheme = 'https')
	{
		$headers = ['Authorization' => "Bearer {$api_key}"];
		$this->url = "{$scheme}://{$reference_id}.functions.{$domain}";

		$this->headers = $headers ?? null;
	}

	public function __request($method, $url, $headers, $body = null): ResponseInterface
	{
		return Request::request($method, $url, $headers, $body);
	}

	public function __prepareBody($body, $options): array
	{
// @TODO - finish

		return [
			'body' => $body,
			'headers' => $headers,
		];
	}

	public function __prepareResult($response): mixed 
	{
// @TODO - finish

		return [
			'body' => $body,
			'headers' => $headers,
		];
	}

	/**
	 * Invoke a edge function.
	 *
	 * @param  string $functionName  The name of the function.
	 * @param  mixed  $body          Body to send to the edge function.
	 * @param  array  $options       The options for invoke a function.
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public function invoke($functionName, $body = [], $options = []): mixed
	{
		// @TODO - why do we not pass the body as param 2 and why is $options not well described
		try {
			$this->lastResponse = null;
			$method = $options['method'] ?? 'POST';

			// @TODO - what in the world are we doing here!?
			if (!is_array($body)) {
				if (base64_decode($body, true) === false) {
					$payload = file_get_contents($body);
				} else {
					$payload = base64_decode($body);
				}
			} elseif (is_string($body)) {
				$this->headers['Content-Type'] = 'text/plain';
				$payload = $body;
			} elseif (is_array($body)) {
				$payload = json_encode($body);
			} else {
				$this->headers['Content-Type'] = 'application/json';
				$payload = json_encode($body);
			}

			$url = "{$this->url}/{$functionName}";

			// Send the request
			$response = $this->__request($method, $url, $this->headers, $payload);
			$this->lastResponse = $response;
			$responseType = explode(';', $response->getHeader('content-type')[0] ?? 'text/plain')[0];
			$contents = $response->getBody()->getContents();
			if ($responseType === 'application/json') {
				return json_decode($contents);
			} 
			return $contents;
		} catch (\Exception $e) {
			throw $e;
		}
	}
}
