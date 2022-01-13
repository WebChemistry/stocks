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
use WebChemistry\Stocks\Helper\MapperHelper;
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
	 * @return HistoricalPrice[]
	 */
	public function historicalPrice(string $symbol, StockPeriod $period, ?DateTimeRange $range = null): array
	{
		$range ??= DateTimeRange::createFromPeriod($period, 10);
		$response = $this->request(
			$this->createUrl(
				sprintf(
					'historical-price/%s/%d/%s/%s/%s',
					$symbol,
					$period->getNumber(),
					$period->getPeriod(),
					$range->getEnd()->format('Y-m-d'),
					$range->getStart()->format('Y-m-d'),
				),
				apiUrl: self::API_URL_V4
			)
		);

		return MapperHelper::mapToObjects(HistoricalPrice::class, ArrayTypeAssert::array($response, 'results'));
	}
	
	public function realtimePrice(string $symbol): RealtimePriceInterface
	{
		return MapperHelper::mapToObject(
			RealtimePrice::class,
			$this->request($this->createUrl('quote-short', $symbol))
		);
	}

	/**
	 * @param string[] $symbols
	 * @return SymbolCollection<RealtimePriceInterface>
	 */
	public function realtimePrices(array $symbols): SymbolCollection
	{
		return MapperHelper::mapToCollection(
			RealtimePrice::class,
			$this->request($this->createUrl('quote-short', $symbols))
		);
	}

	/**
	 * @see https://site.financialmodelingprep.com/developer/docs#Symbols-List
	 * @return SymbolCollection<SymbolInterface>
	 */
	public function symbolList(): SymbolCollection
	{
		$stocks = MapperHelper::mapWithSymbolKey(
			fn (array $data) => new Symbol($data),
			$this->request($this->createUrl('stock/list'))
		);
		$indexes = MapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromIndex($data),
			$this->request($this->createUrl('quotes/index'))
		);
		$cryptos = MapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromCrypto($data),
			$this->request($this->createUrl('quotes/crypto'))
		);
		$commodities = MapperHelper::mapWithSymbolKey(
			fn (array $data) => Symbol::createFromCommodity($data),
			$this->request($this->createUrl('symbol/available-commodities'))
		);

		return new SymbolCollection(array_merge($stocks, $indexes, $cryptos, $commodities));
	}

	/**
	 * @see https://site.financialmodelingprep.com/developer/docs#Company-Financial-Statements
	 * @return FinancialInterface[]
	 */
	public function financials(string $symbol, ?int $limit = null, ?PeriodEnum $period = null): array
	{
		return MapperHelper::mapToObjects(
			Financial::class,
			$this->request(
				$this->createUrl('income-statement', $symbol)
					->setQueryParameter('limit', $limit)
					->setQueryParameter('period', $period?->getValue())
			)
		);
	}

	public function financial(string $symbol, ?PeriodEnum $period = null): ?FinancialInterface
	{
		$financials = $this->financials($symbol, 1, $period);

		return Arrays::first($financials);
	}

	/**
	 * @see https://site.financialmodelingprep.com/developer/docs#Company-Quote
	 * @throws StockClientNoDataException
	 */
	public function quote(string $symbol): QuoteInterface
	{
		$data = $this->request($this->createUrl('quote', $symbol));

		if (!isset($data[0])) {
			throw new StockClientNoDataException();
		}

		return new Quote(TypeAssert::array($data[0]));
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
