<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result;

use WebChemistry\Stocks\Enum\TickerTypeEnum;

interface SymbolInterface
{

	public function getSymbol(): string;

	public function getName(): string;

	public function getExchange(): ?string;

	public function getType(): TickerTypeEnum;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
