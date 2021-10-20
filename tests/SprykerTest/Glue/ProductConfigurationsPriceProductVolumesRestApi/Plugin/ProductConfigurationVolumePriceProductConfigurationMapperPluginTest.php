<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Glue\ProductConfigurationsPriceProductVolumesRestApi\Plugin;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\CurrencyBuilder;
use Generated\Shared\DataBuilder\PriceProductBuilder;
use Generated\Shared\DataBuilder\ProductConfigurationInstanceBuilder;
use Generated\Shared\DataBuilder\RestCurrencyBuilder;
use Generated\Shared\DataBuilder\RestProductConfigurationPriceAttributesBuilder;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\ProductConfigurationInstanceTransfer;
use Generated\Shared\Transfer\RestCurrencyTransfer;
use Generated\Shared\Transfer\RestProductConfigurationPriceAttributesTransfer;
use Spryker\Glue\ProductConfigurationsPriceProductVolumesRestApi\Plugin\ProductConfigurationsRestApi\ProductConfigurationVolumePriceProductConfigurationPriceMapperPlugin;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group ProductConfigurationsPriceProductVolumesRestApi
 * @group Plugin
 * @group ProductConfigurationVolumePriceProductConfigurationMapperPluginTest
 * Add your own group annotations below this line
 */
class ProductConfigurationVolumePriceProductConfigurationMapperPluginTest extends Unit
{
    /**
     * @var string
     */
    protected const PRICE_TYPE_NAME = 'priceTypeName';

    /**
     * @var string
     */
    protected const CURRENCY_NAME = 'EUR';

    /**
     * @return void
     */
    public function testMapWillMapRestCartItemProductConfigurationInstanceAttributesToProductConfigurationInstanceTransfer(): void
    {
        // Arrange
        $restProductConfigurationPriceAttributesTransfer = (new RestProductConfigurationPriceAttributesBuilder([
            RestProductConfigurationPriceAttributesTransfer::PRICE_TYPE_NAME => static::PRICE_TYPE_NAME,
            RestProductConfigurationPriceAttributesTransfer::CURRENCY => (new RestCurrencyBuilder([
                RestCurrencyTransfer::NAME => static::CURRENCY_NAME,
            ]))->build()->toArray(),
        ]))
            ->withVolumePrice()
            ->withAnotherVolumePrice()
            ->build();

        $priceProductTransfer = (new PriceProductBuilder([PriceProductTransfer::PRICE_TYPE_NAME => static::PRICE_TYPE_NAME]))
            ->withMoneyValue([
                MoneyValueTransfer::CURRENCY => (new CurrencyBuilder([CurrencyTransfer::NAME => static::CURRENCY_NAME]))->build()->toArray(),
            ])
            ->build();

        $productConfigurationInstanceTransfer = (new ProductConfigurationInstanceBuilder([
            ProductConfigurationInstanceTransfer::PRICES => [$priceProductTransfer->toArray()],
        ]))->build();

        $productConfigurationVolumePriceProductConfigurationPriceMapperPlugin = new ProductConfigurationVolumePriceProductConfigurationPriceMapperPlugin();

        // Act
        $productConfigurationInstanceTransfer = $productConfigurationVolumePriceProductConfigurationPriceMapperPlugin->map(
            [$restProductConfigurationPriceAttributesTransfer],
            $productConfigurationInstanceTransfer,
        );

        // Assert
        $this->assertCount(3, $productConfigurationInstanceTransfer->getPrices());
    }
}
