<?php declare(strict_types = 1);

namespace WebChemistry\Stocks;

use WebChemistry\Stocks\Collection\SymbolCollection;
use WebChemistry\Stocks\Enum\PeriodEnum;
use WebChemistry\Stocks\Enum\TimeSeriesTypeEnum;
use WebChemistry\Stocks\Period\DateTimeRange;
use WebChemistry\Stocks\Period\StockPeriod;
use WebChemistry\Stocks\Result\FinancialInterface;
use WebChemistry\Stocks\Result\HistoricalPriceInterface;
use WebChemistry\Stocks\Result\QuoteInterface;
use WebChemistry\Stocks\Result\RealtimePriceInterface;
use WebChemistry\Stocks\Result\SymbolInterface;
use WebChemistry\Stocks\Result\TimeSeriesInterface;

interface StockClientInterface
{

	/**
	 * @param mixed[] $options
	 * @return HistoricalPriceInterface[]
	 */
	public function historicalPrice(
		string $symbol,
		StockPeriod $period,
		?DateTimeRange $range = null,
		array $options = [],
	): array;

	/**
	 * @param mixed[] $options
	 */
	public function realtimePrice(string $symbol, array $options = []): RealtimePriceInterface;

	/**
	 * @param mixed[] $options
	 * @param string[] $symbols
	 * @return SymbolCollection<RealtimePriceInterface>
	 */
	public function realtimePrices(array $symbols, array $options = []): SymbolCollection;

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<SymbolInterface>
	 */
	public function symbolList(array $options = []): SymbolCollection;

	/**
	 * @param mixed[] $options
	 * @return FinancialInterface[]
	 */
	public function financials(string $symbol, ?int $limit = null, ?PeriodEnum $period = null, array $options = []): array;

	/**
	 * @param mixed[] $options
	 */
	public function financial(string $symbol, ?PeriodEnum $period = null, array $options = []): ?FinancialInterface;

	/**
	 * @param mixed[] $options
	 */
	public function quote(string $symbol, array $options = []): QuoteInterface;

	/**
	 * @param string[] $symbols
	 * @param mixed[] $options
	 * @return SymbolCollection<QuoteInterface>
	 */
	public function quotes(array $symbols, array $options = []): SymbolCollection;

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<SymbolInterface>
	 */
	public function funds(array $options = []): SymbolCollection;

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<SymbolInterface>
	 */
	public function etfs(array $options = []): SymbolCollection;

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<SymbolInterface>
	 */
	public function indexes(array $options = []): SymbolCollection;

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<SymbolInterface>
	 */
	public function cryptos(array $options = []): SymbolCollection;

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<SymbolInterface>
	 */
	public function commodities(array $options = []): SymbolCollection;

	/**
	 * @return TimeSeriesInterface[]
	 */
	public function timeSeries(string $symbol, TimeSeriesTypeEnum $type): array;

}
