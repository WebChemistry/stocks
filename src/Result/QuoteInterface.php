<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result;

interface QuoteInterface
{

	public function getSymbol(): string;

	public function getName(): string;

	public function getChange(): float;

	public function getChangePercentage(): float;

	public function getPrice(): float;

	public function getOpen(): float;

	public function getPreviousClose(): float;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
