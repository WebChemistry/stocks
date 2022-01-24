<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Regex;

final class TickerRegex
{

	private const PATTERN = '(?:\^)?[a-zA-Z0-9._&=-]*[a-zA-Z0-9]';

	public static function match(string $ticker): bool
	{
		return (bool) preg_match('#^' . self::PATTERN. '$#', $ticker);
	}

	public static function regexPattern(): string
	{
		return self::PATTERN;
	}

	public static function patternInText(string $symbolMatch = '$'): string
	{
		return sprintf('#(?:%s(%s))#', preg_quote($symbolMatch, '#'), self::PATTERN);
	}

}
