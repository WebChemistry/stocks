<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result;

interface RealtimePriceInterface
{

	public function getSymbol(): string;

	public function getPrice(): float;

	public function getVolume(): int;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
