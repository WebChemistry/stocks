<?php declare(strict_types = 1);

namespace WebChemistry\Stocks;

use DateTime;
use DateTimeInterface;
use Generator;
use League\Csv\Reader;
use Nette\Http\Url;
use Nette\Utils\Arrays;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Utilitte\Asserts\ArrayTypeAssert;
use Utilitte\Asserts\TypeAssert;
use WebChemistry\Stocks\Collection\SymbolCollection;
use WebChemistry\Stocks\Enum\PeriodEnum;
use WebChemistry\Stocks\Enum\TimeSeriesTypeEnum;
use WebChemistry\Stocks\Exception\StockClientNoDataException;
use WebChemistry\Stocks\Helper\StockMapperHelper;
use WebChemistry\Stocks\HttpClient\HttpClientTransaction;
use WebChemistry\Stocks\Period\DateTimeRange;
use WebChemistry\Stocks\Period\StockPeriod;
use WebChemistry\Stocks\Result\FinancialInterface;
use WebChemistry\Stocks\Result\Fmp\Financial;
use WebChemistry\Stocks\Result\Fmp\HistoricalPrice;
use WebChemistry\Stocks\Result\Fmp\Quote;
use WebChemistry\Stocks\Result\Fmp\Rating;
use WebChemistry\Stocks\Result\Fmp\RealtimePrice;
use WebChemistry\Stocks\Result\Fmp\Symbol;
use WebChemistry\Stocks\Result\Fmp\TimeSeries;
use WebChemistry\Stocks\Result\QuoteInterface;
use WebChemistry\Stocks\Result\RealtimePriceInterface;
use WebChemistry\Stocks\Result\SymbolInterface;

final class FmpStockClient implements StockClientInterface
{

	public const SEGMENT_ALL = [
		'amex',
		'nasdaq',
		'nyse',
		'etf',
		'mutual_fund',
		'euronext',
		'tsx',
		'mcx',
		'xetra',
		'nse',
		'lse',
		'six',
		'hkse',
		'ose',
		'ase',
		'bru',
		'jkt',
		'vie',
		'sgo',
		'shz',
		'shh',
		'ham',
		'cph',
		'ath',
		'mil',
		'jpx',
		'ksc',
		'koe',
		'sto',
		'ist',
		'tai',
		'mex',
		'jnb',
		'lis',
		'tlv',
		'mce',
		'wse',
		'hel',
		'sao',
		'set',
		'iob',
		'doh',
		'kls',
		'pra',
		'ams',
		'ber',
		'two',
		'sau',
		'ice',
		'commodity',
		'crypto',
		'index',
		'forex',
	];

	public const HISTORICAL_PRICE_CRYPTO = 'fmp_historical_price_crypto';

	private const API_URL = 'https://financialmodelingprep.com/api/v3/';
	private const API_URL_V4 = 'https://financialmodelingprep.com/api/v4/';

	private HttpClientInterface $client;

	public function __construct(
		private string $apiKey,
		?HttpClientInterface $client = null,
	)
	{
		$this->client = $client ?? HttpClient::create();
	}

	/**
	 * @param string[]|string $symbols
	 * @return SymbolCollection<Rating>
	 */
	public function rating(array|string $symbols): SymbolCollection
	{
		$transaction = $this->createTransaction();

		foreach ((array) $symbols as $symbol) {
			$transaction->request('GET', (string) $this->createUrl('rating', $symbol), key: $symbol);
		}

		$transaction->commit();

		$collection = [];
		foreach ($transaction->getResponses() as $symbol => $response) {
			$collection[$symbol] = new Rating($response->toArray());
		}

		return new SymbolCollection($collection);
	}

	public function profiles(): Reader
	{
		$response = $this->client->request('GET', (string) $this->createUrl('profile/all', apiUrl: self::API_URL_V4));

		$reader = Reader::createFromString($response->getContent());
		$reader->setHeaderOffset(0);

		return $reader;
	}

	/**
	 * @return Generator<HistoricalPrice>
	 */
	public function endPrices(DateTimeInterface $dateTime): Generator
	{
		$response = $this->client->request(
			'GET',
			(string) $this->createUrl('batch-request-end-of-day-prices', apiUrl: self::API_URL_V4)
		);

		$reader = Reader::createFromString($response->getContent());
		$reader->setHeaderOffset(0);

		foreach ($reader as $item) {
			yield new HistoricalPrice([
				'symbol' => $item['symbol'],
				'formatted' => $item['date'],
				'c' => $item['close'],
			]);
		}
	}

	/**
	 * @return TimeSeries[]
	 */
	public function timeSeries(string $symbol, TimeSeriesTypeEnum $type): array
	{
		if ($type->getValue() === TimeSeriesTypeEnum::DAY()->getValue()) {
			$data = $this->request($this->createUrl('historical-chart/5min', $symbol));

			return StockMapperHelper::mapToObjects(TimeSeries::class, array_slice($data, 0, 79));
		} else if ($type->getValue() === TimeSeriesTypeEnum::FIVE_DAYS()->getValue()) {
			$data = $this->request($this->createUrl('historical-chart/30min', $symbol));

			return StockMapperHelper::mapToObjects(TimeSeries::class, array_slice($data, 0, 70));
		} else {
			$data = $this->request(
				$this->createUrl('historical-price-full', $symbol)
					->setQueryParameter('serietype', 'line')
			);

			if (!isset($data['historical'])) {
				throw new StockClientNoDataException();
			}

			/** @var array{date: string, close: float|int}[] $data */
			$data = $data['historical'];

			if (TimeSeriesTypeEnum::MAX()->getValue() !== $type->getValue() || count($data) > 40) {
				$series = [];
				$check = null;
				$max = null;
				$min = $this->dateTime('+ 1 day');

				if ($type->getValue() === TimeSeriesTypeEnum::MONTH()->getValue()) {
					$max = $this->dateTime('- 1 month');
				} elseif ($type->getValue() === TimeSeriesTypeEnum::SIX_MONTHS()->getValue()) {
					$max = $this->dateTime('- 6 months');
				} elseif ($type->getValue() === TimeSeriesTypeEnum::YEAR()->getValue()) {
					$max = $this->dateTime('- 1 year');
					$check = '- 1 week';
				} elseif ($type->getValue() === TimeSeriesTypeEnum::FIVE_YEARS()->getValue()) {
					$max = $this->dateTime('- 5 years');
					$check = '- 1 week';
				}

				foreach ($data as $item) {
					$date = $this->dateTime($item['date']);

					if ($max > $date) {
						break;
					}

					if ($check) {
						if ($date <= $min) {
							$min = $date->modify('- 1 week');
						} else {
							continue;
						}
					}

					$series[] = $item;
				}
			} else {
				$series = $data;
			}
		}

		return StockMapperHelper::mapToObjects(TimeSeries::class, $series);
	}

	/**
	 * @param string|string[] $segment
	 * @return SymbolCollection<Quote>
	 */
	public function quotesBySegments(string|array $segments): SymbolCollection
	{
		$transaction = $this->createTransaction();

		$responses = [];
		foreach ((array) $segments as $segment) {
			$responses[] = $transaction->request('GET', (string) $this->createUrl('quotes', $segment));
		}

		$transaction->commit();

		$collection = [];

		foreach ($responses as $response) {
			$collection[] = StockMapperHelper::mapWithSymbolKey(
				fn (array $data) => new Quote($data),
				array_filter(
					$response->toArray(),
					fn (array $data) => $data['price'] !== null &&
										$data['name'] !== null &&
										$data['previousClose'] !== null &&
										$data['open'] !== null
				)
			);
		}

		return new SymbolCollection(array_merge(...$collection));
	}

	public function allEndOfDayPrices(): array
	{
		$url = $this->createUrl('batch-request-end-of-day-prices', apiUrl: self::API_URL_V4)
			->setQueryParameter('date', (new DateTime('- 1 day'))->format('Y-m-d'));
		$response = $this->client->request('GET', (string) $url);

		$array = [];
		foreach (explode("\n", $response->getContent()) as $line) {
			$array[] = str_getcsv($line);
		}

		return array_slice($array, 1);
	}

	/**
	 * @param mixed[] $options
	 * @return HistoricalPrice[]
	 */
	public function historicalPrice(string $symbol, StockPeriod $period, ?DateTimeRange $range = null, array $options = []): array
	{
		$range ??= DateTimeRange::createFromPeriod($period, 10);

		if (str_starts_with($symbol, '^')) {
			$endpoint = 'historical-price-index';
			$symbol = substr($symbol, 1);
		} else {
			$endpoint = ($options[self::HISTORICAL_PRICE_CRYPTO] ?? false) ? 'historical-price-crypto-interval' : 'historical-price';
		}

		$response = $this->request(
			$this->createUrl(
				sprintf(
					'%s/%s/%d/%s/%s/%s',
					$endpoint,
					$symbol,
					$period->getNumber(),
					$period->getPeriod(),
					$range->getEnd()->format('Y-m-d'),
					$range->getStart()->format('Y-m-d'),
				),
				apiUrl: self::API_URL_V4
			)
		);

		return StockMapperHelper::mapToObjects(HistoricalPrice::class, ArrayTypeAssert::array($response, 'results'));
	}

	/**
	 * @param mixed[] $options
	 */
	public function realtimePrice(string $symbol, array $options = []): RealtimePriceInterface
	{
		return StockMapperHelper::mapToObject(
			RealtimePrice::class,
			$this->request($this->createUrl('quote-short', $symbol))
		);
	}

	/**
	 * @param string[] $symbols
	 * @param mixed[] $options
	 * @return SymbolCollection<RealtimePriceInterface>
	 */
	public function realtimePrices(array $symbols, array $options = []): SymbolCollection
	{
		if (!$symbols) {
			return new SymbolCollection();
		}

		return StockMapperHelper::mapToCollection(
			RealtimePrice::class,
			$this->request($this->createUrl('quote-short', $symbols))
		);
	}

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<Symbol>
	 */
	public function indexes(array $options = []): SymbolCollection
	{
		$symbols = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromIndex($data),
			$this->request($this->createUrl('quotes/index'))
		);

		return new SymbolCollection($symbols);
	}

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<Symbol>
	 */
	public function etfs(array $options = []): SymbolCollection
	{
		$symbols = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromEtf($data),
			$this->request($this->createUrl('quotes/etf'))
		);

		return new SymbolCollection($symbols);
	}

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<Symbol>
	 */
	public function funds(array $options = []): SymbolCollection
	{
		$symbols = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromFund($data),
			$this->request($this->createUrl('quotes/mutual_fund'))
		);

		return new SymbolCollection($symbols);
	}

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<Symbol>
	 */
	public function cryptos(array $options = []): SymbolCollection
	{
		$symbols = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromFund($data),
			$this->request($this->createUrl('quotes/cryptos'))
		);

		return new SymbolCollection($symbols);
	}

	/**
	 * @param mixed[] $options
	 * @return SymbolCollection<Symbol>
	 */
	public function commodities(array $options = []): SymbolCollection
	{
		$symbols = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromCommodity($data),
			$this->request($this->createUrl('quotes/commodity'))
		);

		return new SymbolCollection($symbols);
	}

	/**
	 * @see https://site.financialmodelingprep.com/developer/docs#Symbols-List
	 * @param mixed[] $options
	 * @return SymbolCollection<SymbolInterface>
	 */
	public function symbolList(array $options = []): SymbolCollection
	{
		$transaction = $this->createTransaction();

		$stocks = $transaction->request('GET', (string) $this->createUrl('stock/list'));
		$etfs = $transaction->request('GET', (string) $this->createUrl('etf/list'));
		$indexes = $transaction->request('GET', (string) $this->createUrl('quotes/index'));
		$cryptos = $transaction->request('GET', (string) $this->createUrl('quotes/crypto'));
		$commodities = $transaction->request('GET', (string) $this->createUrl('quotes/commodity'));

		$transaction->commit();

		$stocks = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => new Symbol($data),
			$stocks->toArray(),
		);
		$indexes = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromIndex($data),
			$indexes->toArray()
		);
		$etfs = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromEtf($data),
			$etfs->toArray()
		);
		$cryptos = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromCrypto($data),
			$cryptos->toArray()
		);
		$commodities = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromCommodity($data),
			$commodities->toArray()
		);

		return new SymbolCollection(array_merge($stocks, $indexes, $cryptos, $commodities, $etfs));
	}

	/**
	 * @see https://site.financialmodelingprep.com/developer/docs#Company-Financial-Statements
	 * @param mixed[] $options
	 * @return FinancialInterface[]
	 */
	public function financials(string $symbol, ?int $limit = null, ?PeriodEnum $period = null, array $options = []): array
	{
		return StockMapperHelper::mapToObjects(
			Financial::class,
			$this->request(
				$this->createUrl('income-statement', $symbol)
					->setQueryParameter('limit', $limit)
					->setQueryParameter('period', $period?->getValue())
			)
		);
	}

	/**
	 * @param mixed[] $options
	 */
	public function financial(string $symbol, ?PeriodEnum $period = null, array $options = []): ?FinancialInterface
	{
		$financials = $this->financials($symbol, 1, $period);

		return Arrays::first($financials);
	}

	/**
	 * @see https://site.financialmodelingprep.com/developer/docs#Company-Quote
	 * @param mixed[] $options
	 * @throws StockClientNoDataException
	 */
	public function quote(string $symbol, array $options = []): QuoteInterface
	{
		$data = $this->request($this->createUrl('quote', $symbol));

		if (!isset($data[0])) {
			throw new StockClientNoDataException();
		}

		return new Quote(TypeAssert::array($data[0]));
	}

	/**
	 * @see https://site.financialmodelingprep.com/developer/docs#Company-Quote
	 * @param string[] $symbols
	 * @param mixed[] $options
	 * @return SymbolCollection<Quote>
	 */
	public function quotes(array $symbols, array $options = []): SymbolCollection
	{
		if (!$symbols) {
			return new SymbolCollection();
		}

		$data = $this->request($this->createUrl('quote', $symbols));

		return StockMapperHelper::mapToCollection(Quote::class, $data);
	}

	/**
	 * @param string[]|string $symbols
	 */
	public function createUrl(
		string $path,
		array|string|null $symbols = null,
		bool $apiKey = true,
		?string $apiUrl = null,
	): Url
	{
		$path = trim($path, '/');
		if ($symbols !== null) {
			$path .= '/' . implode(',', (array) $symbols);
		}

		$url = new Url(($apiUrl ?: self::API_URL) . $path);
		if ($apiKey) {
			$url->setQueryParameter('apikey', $this->apiKey);
		}

		return $url;
	}

	/**
	 * @return mixed[]
	 */
	public function request(Url $url): array
	{
		return $this->client->request('GET', (string) $url)->toArray();
	}

	private function createTransaction(): HttpClientTransaction
	{
		return new HttpClientTransaction($this->client, 2);
	}

	private function dateTime(string $date): DateTime
	{
		return (new DateTime($date))->setTime(0, 0);
	}

}
