<?php declare(strict_types = 1);

namespace WebChemistry\Stocks;

use WebChemistry\Stocks\Collection\SymbolCollection;
use WebChemistry\Stocks\Enum\PeriodEnum;
use WebChemistry\Stocks\Period\DateTimeRange;
use WebChemistry\Stocks\Period\StockPeriod;
use WebChemistry\Stocks\Result\FinancialInterface;
use WebChemistry\Stocks\Result\HistoricalPriceInterface;
use WebChemistry\Stocks\Result\QuoteInterface;
use WebChemistry\Stocks\Result\RealtimePriceInterface;
use WebChemistry\Stocks\Result\SymbolInterface;

interface StockClientInterface
{

	/**
	 * @return HistoricalPriceInterface[]
	 */
	public function historicalPrice(string $symbol, StockPeriod $period, ?DateTimeRange $range = null): array;

	public function realtimePrice(string $symbol): RealtimePriceInterface;

	/**
	 * @param string[] $symbols
	 * @return SymbolCollection<RealtimePriceInterface>
	 */
	public function realtimePrices(array $symbols): SymbolCollection;

	/**
	 * @return SymbolCollection<SymbolInterface>
	 */
	public function symbolList(): SymbolCollection;

	/**
	 * @return FinancialInterface[]
	 */
	public function financials(string $symbol, ?int $limit = null, ?PeriodEnum $period = null): array;

	public function financial(string $symbol, ?PeriodEnum $period = null): ?FinancialInterface;

	public function quote(string $symbol): QuoteInterface;

}
