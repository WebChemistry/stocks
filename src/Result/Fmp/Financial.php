<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use Typertion\Php\TypeAssert;
use WebChemistry\Stocks\Result\ArrayResult;
use WebChemistry\Stocks\Result\FinancialInterface;

final class Financial extends ArrayResult implements FinancialInterface
{

	public function getEbitda(): ?int
	{
		return TypeAssert::intOrNull($this->data['ebitda'] ?? null);
	}

}
