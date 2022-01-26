<?php declare(strict_types = 1);

namespace WebChemistry\Stocks;

use Nette\Http\Url;
use Nette\Utils\Arrays;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Utilitte\Asserts\ArrayTypeAssert;
use Utilitte\Asserts\TypeAssert;
use WebChemistry\Stocks\Collection\SymbolCollection;
use WebChemistry\Stocks\Enum\PeriodEnum;
use WebChemistry\Stocks\Exception\StockClientException;
use WebChemistry\Stocks\Exception\StockClientNoDataException;
use WebChemistry\Stocks\Helper\StockMapperHelper;
use WebChemistry\Stocks\Period\DateTimeRange;
use WebChemistry\Stocks\Period\StockPeriod;
use WebChemistry\Stocks\Result\FinancialInterface;
use WebChemistry\Stocks\Result\Fmp\Financial;
use WebChemistry\Stocks\Result\Fmp\HistoricalPrice;
use WebChemistry\Stocks\Result\Fmp\Quote;
use WebChemistry\Stocks\Result\Fmp\RealtimePrice;
use WebChemistry\Stocks\Result\Fmp\Symbol;
use WebChemistry\Stocks\Result\QuoteInterface;
use WebChemistry\Stocks\Result\RealtimePriceInterface;
use WebChemistry\Stocks\Result\SymbolInterface;

final class FmpStockClient implements StockClientInterface
{

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
	 * @param mixed[] $options
	 * @return HistoricalPrice[]
	 */
	public function historicalPrice(string $symbol, StockPeriod $period, ?DateTimeRange $range = null, array $options = []): array
	{
		$range ??= DateTimeRange::createFromPeriod($period, 10);
		$response = $this->request(
			$this->createUrl(
				sprintf(
					'%s/%s/%d/%s/%s/%s',
					($options[self::HISTORICAL_PRICE_CRYPTO] ?? false) ? 'historical-price-crypto-interval' : 'historical-price',
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
	 * @see https://site.financialmodelingprep.com/developer/docs#Symbols-List
	 * @param mixed[] $options
	 * @return SymbolCollection<SymbolInterface>
	 */
	public function symbolList(array $options = []): SymbolCollection
	{
		$stocks = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => new Symbol($data),
			$this->request($this->createUrl('stock/list'))
		);
		$indexes = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromIndex($data),
			$this->request($this->createUrl('quotes/index'))
		);
		$cryptos = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromCrypto($data),
			$this->request($this->createUrl('quotes/crypto'))
		);
		$commodities = StockMapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromCommodity($data),
			$this->request($this->createUrl('quotes/commodity'))
		);

		return new SymbolCollection(array_merge($stocks, $indexes, $cryptos, $commodities));
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
	private function createUrl(
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
	private function request(Url $url): array
	{
		try {
			$response = $this->client->request('GET', (string) $url);

			return $response->toArray();
		} catch (ExceptionInterface $exception) {
			throw new StockClientException('Error occured while http request.', 0, $exception);
		}
	}

}
