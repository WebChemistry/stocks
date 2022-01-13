<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use DateTimeImmutable;
use Utilitte\Asserts\ArrayTypeAssert;
use WebChemistry\Stocks\Result\HistoricalPriceInterface;

final class HistoricalPrice implements HistoricalPriceInterface
{

	/**
	 * @param mixed[] $data
	 */
	public function __construct(
		private array $data,
	)
	{
	}

	public function getPrice(): float
	{
		return ArrayTypeAssert::float($this->data, 'c');
	}

	public function getDate(): DateTimeImmutable
	{
		if (isset($this->data['formated'])) { // their typo in v4
			return new DateTimeImmutable(ArrayTypeAssert::string($this->data, 'formated'));
		}

		return new DateTimeImmutable(ArrayTypeAssert::string($this->data, 'formatted'));
	}

}
