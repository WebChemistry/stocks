<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result;

abstract class ArrayResult
{

	/**
	 * @param mixed[] $data
	 */
	public function __construct(
		protected array $data,
	)
	{
	}

	/**
	 * @return mixed[]
	 */
	public function toArray(): array
	{
		return $this->data;
	}

}
