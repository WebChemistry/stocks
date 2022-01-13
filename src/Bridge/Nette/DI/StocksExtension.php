<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Bridge\Nette\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;
use WebChemistry\Stocks\FmpStockClient;
use WebChemistry\Stocks\StockClientInterface;

final class StocksExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'fmp' => Expect::structure([
				'secret' => Expect::string()->required(),
			]),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		/** @var stdClass $config */
		$config = $this->getConfig();

		$builder->addDefinition($this->prefix('fmpClient'))
			->setFactory(FmpStockClient::class, [$config->fmp->secret])
			->setType(StockClientInterface::class);
	}

}
