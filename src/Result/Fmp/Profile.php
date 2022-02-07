<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use Utilitte\Asserts\ArrayTypeAssert;
use WebChemistry\Stocks\Result\ArrayResult;

final class Profile extends ArrayResult
{

	public function getSymbol(): string
	{
		return ArrayTypeAssert::string($this->data, 'Symbol');
	}

	public function getName(): string
	{
		return ArrayTypeAssert::string($this->data, 'companyName');
	}

	public function getCurrency(): string
	{
		return ArrayTypeAssert::string($this->data, 'currency');
	}

	public function getExchange(): string
	{
		return ArrayTypeAssert::string($this->data, 'exchange');
	}

	public function getExchangeShortName(): string
	{
		return ArrayTypeAssert::string($this->data, 'exchangeShortName');
	}

	public function getIndustry(): string
	{
		return ArrayTypeAssert::string($this->data, 'industry');
	}

	public function getSector(): string
	{
		return ArrayTypeAssert::string($this->data, 'sector');
	}

	public function getWebsite(): ?string
	{
		return $this->nullable(ArrayTypeAssert::string($this->data, 'website'));
	}

	public function getDescription(): string
	{
		return ArrayTypeAssert::string($this->data, 'description');
	}

	public function isEtf(): bool
	{
		return $this->bool(ArrayTypeAssert::string($this->data, 'isEtf'));
	}

	public function isFund(): bool
	{
		return $this->bool(ArrayTypeAssert::string($this->data, 'isFund'));
	}

	private function nullable(string $str): ?string
	{
		return $str ?: null;
	}

	private function bool(string $str): bool
	{
		return strtolower($str) === 'true';
	}

}
