<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Exchange;

final class ExchangeList
{

	private const LIST = [
		'AMEX' => 'New York Stock Exchange Arca',
		'NASDAQ' => 'Nasdaq Global Select',
		'NYSE' => 'New York Stock Exchange',
		'ETF' => 'BATS Exchange',
		'OTC' => 'Other OTC',
		'MUTUAL_FUND' => 'Nasdaq Capital Market',
		'EURONEXT' => 'Euronext',
		'TSX' => 'Toronto',
		'MCX' => 'Multi Commodity Exchange of India',
		'XETRA' => 'Frankfurt Stock Exchange',
		'NSE' => 'National Stock Exchange of India',
		'LSE' => 'London Stock Exchange',
		'SIX' => 'SIX Swiss Exchange',
		'HKSE' => 'Hong Kong Stock Exchange',
		'OSE' => 'Osaka Exchange',
		'ASE' => 'Amman Stock Exchange',
		'BRU' => 'Brussels Stock Exchange',
		'JKT' => 'Jakarta Stock Exchange',
		'VIE' => 'Vienna Stock Exchange',
		'SGO' => 'Santiago Stock Exchange',
		'SHZ' => 'Shenzhen Stock Exchange',
		'SHH' => 'Shanghai Stock Exchange',
		'HAM' => 'Hamburg Stock Exchange',
		'CPH' => 'Copenhagen Stock Exchange',
		'ATH' => 'Athens Stock Exchange',
		'MIL' => 'Milan Stock Exchange',
		'JPX' => 'Japan Exchange',
		'KSE' => 'Karachi Stock Exchange',
		'KSC' => 'Karachi Stock Exchange',
		'KOE' => 'Korea Exchange', // KOSDAQ
		'STO' => 'Stockholm Stock Exchange',
		'IST' => 'Istanbul Exchange', // ISE
		'MEX' => 'Mexico Exchange',
		'JNB' => 'Johannesburg Stock Exchange',
		'LIS' => 'Euronext Lisbon',
		'TLV' => 'Tel Aviv Stock Exchange',
		'MCE' => 'Euronext',
		'WSE' => 'Warsaw Stock Exchange',
		'HEL' => 'Helsinki Stock Exchange',
		'SAO' => 'São Paulo Stock Exchange',
		'SET' => 'The Stock Exchange of Thailand',
		'IOB' => 'Indian Overseas Bank',
		''
	];

	private const E = [
		'GER' => 'XETRA',
	];

}
