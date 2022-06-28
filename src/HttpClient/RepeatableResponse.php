<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\HttpClient;

use Symfony\Contracts\HttpClient\ResponseInterface;

final class RepeatableResponse implements ResponseInterface
{

	/** @var callable(): ResponseInterface */
	private $repeater;

	/**
	 * @param callable(): ResponseInterface $repeater
	 */
	public function __construct(
		private ResponseInterface $response,
		callable $repeater,
	)
	{
		$this->repeater = $repeater;
	}

	public function getStatusCode(): int
	{
		return $this->response->getStatusCode();
	}

	/**
	 * @return string[][]
	 */
	public function getHeaders(bool $throw = true): array
	{
		return $this->response->getHeaders($throw);
	}

	public function getContent(bool $throw = true): string
	{
		return $this->response->getContent($throw);
	}

	/**
	 * @return mixed[]
	 */
	public function toArray(bool $throw = true): array
	{
		return $this->response->toArray($throw);
	}

	public function cancel(): void
	{
		$this->response->cancel();
	}

	public function getInfo(string $type = null): mixed
	{
		return $this->response->getInfo($type);
	}

	public function repeat(): void
	{
		$this->response = ($this->repeater)();
	}

}
