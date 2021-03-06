<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\HttpClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

final class HttpClientTransaction implements HttpClientInterface
{

	/** @var RepeatableResponse[] */
	private array $responses = [];

	public function __construct(
		private HttpClientInterface $client,
		private int $limit = 1,
		private int $sleep = 500,
	)
	{
	}

	/**
	 * @param mixed[] $options
	 */
	public function request(string $method, string $url, array $options = [], ?string $key = null): ResponseInterface
	{
		$response = new RepeatableResponse(
			$this->client->request($method, $url, $options),
			fn () => $this->client->request($method, $url, $options),
		);

		if ($key) {
			$this->responses[$key] = $response;
		} else {
			$this->responses[] = $response;
		}

		return $response;
	}

	/**
	 * @param ResponseInterface|iterable<array-key, ResponseInterface> $responses
	 */
	public function stream($responses, float $timeout = null): ResponseStreamInterface
	{
		return $this->client->stream($responses, $timeout);
	}

	public function commit(): void
	{
		$limit ??= $this->limit;

		while ($limit > 0) {
			$repeat = [];
			foreach ($this->responses as $response) {
				if ($response->getStatusCode() === 429) {
					$repeat[] = $response;
				}
			}

			if (!$repeat) {
				return;
			}

			usleep($this->sleep);

			foreach ($repeat as $request) {
				$request->repeat();
			}

			$limit--;
		}
	}

	/**
	 * @return RepeatableResponse[]
	 */
	public function getResponses(): array
	{
		return $this->responses;
	}

	/**
	 * @param mixed[] $options
	 */
	public function withOptions(array $options): static
	{
		$cloned = clone $this;
		$cloned->client = $cloned->client->withOptions($options);

		return $this;
	}

}
