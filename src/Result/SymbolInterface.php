<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result;

use WebChemistry\Stocks\Enum\TickerTypeEnum;

interface SymbolInterface extends ArrayResultInterface
{

	public function getSymbol(): string;

	public function getName(): string;

	public function getExchange(): ?string;

	public function getType(): TickerTypeEnum;

}
