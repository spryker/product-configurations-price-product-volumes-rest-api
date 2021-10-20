<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductConfigurationsPriceProductVolumesRestApi\Processor\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductDimensionTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\ProductConfigurationInstanceTransfer;
use Generated\Shared\Transfer\RestCurrencyTransfer;
use Generated\Shared\Transfer\RestProductConfigurationPriceAttributesTransfer;
use Generated\Shared\Transfer\RestProductPriceVolumesAttributesTransfer;
use Spryker\Glue\ProductConfigurationsPriceProductVolumesRestApi\Dependency\Service\ProductConfigurationsPriceProductVolumesRestApiToProductConfigurationServiceInterface;
use Spryker\Glue\ProductConfigurationsPriceProductVolumesRestApi\Dependency\Service\ProductConfigurationsPriceProductVolumesRestApiToUtilEncodingServiceInterface;

class RestProductConfigurationPriceProductVolumeMapper implements RestProductConfigurationPriceProductVolumeMapperInterface
{
    /**
     * @var \Spryker\Glue\ProductConfigurationsPriceProductVolumesRestApi\Dependency\Service\ProductConfigurationsPriceProductVolumesRestApiToProductConfigurationServiceInterface
     */
    protected $productConfigurationService;

    /**
     * @var \Spryker\Glue\ProductConfigurationsPriceProductVolumesRestApi\Dependency\Service\ProductConfigurationsPriceProductVolumesRestApiToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @param \Spryker\Glue\ProductConfigurationsPriceProductVolumesRestApi\Dependency\Service\ProductConfigurationsPriceProductVolumesRestApiToProductConfigurationServiceInterface $productConfigurationService
     * @param \Spryker\Glue\ProductConfigurationsPriceProductVolumesRestApi\Dependency\Service\ProductConfigurationsPriceProductVolumesRestApiToUtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        ProductConfigurationsPriceProductVolumesRestApiToProductConfigurationServiceInterface $productConfigurationService,
        ProductConfigurationsPriceProductVolumesRestApiToUtilEncodingServiceInterface $utilEncodingService
    ) {
        $this->productConfigurationService = $productConfigurationService;
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param array<\Generated\Shared\Transfer\RestProductConfigurationPriceAttributesTransfer> $restProductConfigurationPriceAttributesTransfers
     * @param \Generated\Shared\Transfer\ProductConfigurationInstanceTransfer $productConfigurationInstanceTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConfigurationInstanceTransfer
     */
    public function mapRestProductConfigurationPriceAttributesToProductConfigurationInstance(
        array $restProductConfigurationPriceAttributesTransfers,
        ProductConfigurationInstanceTransfer $productConfigurationInstanceTransfer
    ): ProductConfigurationInstanceTransfer {
        foreach ($restProductConfigurationPriceAttributesTransfers as $restProductConfigurationPriceAttributesTransfer) {
            if ($restProductConfigurationPriceAttributesTransfer->getVolumePrices()->count() === 0) {
                continue;
            }

            $productConfigurationInstanceTransfer = $this->mapRestProductConfigurationPriceAttributesVolumePricesToProductConfigurationInstanceTransfer(
                $restProductConfigurationPriceAttributesTransfer,
                $productConfigurationInstanceTransfer,
            );
        }

        $priceProductTransfers = $this->fillUpPriceDimensionWithProductConfigurationInstanceHash(
            $productConfigurationInstanceTransfer->getPrices(),
            $this->productConfigurationService
                ->getProductConfigurationInstanceHash($productConfigurationInstanceTransfer),
        );

        return $productConfigurationInstanceTransfer->setPrices($priceProductTransfers);
    }

    /**
     * @param \Generated\Shared\Transfer\RestProductConfigurationPriceAttributesTransfer $restProductConfigurationPriceAttributesTransfer
     * @param \Generated\Shared\Transfer\ProductConfigurationInstanceTransfer $productConfigurationInstanceTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConfigurationInstanceTransfer
     */
    protected function mapRestProductConfigurationPriceAttributesVolumePricesToProductConfigurationInstanceTransfer(
        RestProductConfigurationPriceAttributesTransfer $restProductConfigurationPriceAttributesTransfer,
        ProductConfigurationInstanceTransfer $productConfigurationInstanceTransfer
    ): ProductConfigurationInstanceTransfer {
        $extractedPriceProductTransfers = [];
        foreach ($productConfigurationInstanceTransfer->getPrices() as $priceProductTransfer) {
            if ($restProductConfigurationPriceAttributesTransfer->getPriceTypeName() !== $priceProductTransfer->getPriceTypeName()) {
                continue;
            }

            $extractedPriceProductTransfers[] = $this->extractVolumePrices(
                $priceProductTransfer,
                $restProductConfigurationPriceAttributesTransfer,
            );
        }

        $extractedPriceProductTransfers = array_merge(...$extractedPriceProductTransfers);
        foreach ($extractedPriceProductTransfers as $priceProductTransfer) {
            $productConfigurationInstanceTransfer->addPrice($priceProductTransfer);
        }

        return $productConfigurationInstanceTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     * @param \Generated\Shared\Transfer\RestProductConfigurationPriceAttributesTransfer $restProductConfigurationPriceAttributesTransfer
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    protected function extractVolumePrices(
        PriceProductTransfer $priceProductTransfer,
        RestProductConfigurationPriceAttributesTransfer $restProductConfigurationPriceAttributesTransfer
    ): array {
        $extractedPrices = [];
        foreach ($restProductConfigurationPriceAttributesTransfer->getVolumePrices() as $restProductPriceVolumesAttributesTransfer) {
            $priceProductTransferForMapping = (new PriceProductTransfer())
                ->fromArray($priceProductTransfer->toArray(), true);

            $extractedPrices[] = $this->mapVolumePriceDataToPriceProductTransfer(
                $priceProductTransferForMapping,
                $restProductPriceVolumesAttributesTransfer,
                $restProductConfigurationPriceAttributesTransfer->getCurrencyOrFail(),
            );
        }

        return $extractedPrices;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     * @param \Generated\Shared\Transfer\RestProductPriceVolumesAttributesTransfer $restProductPriceVolumesAttributesTransfer
     * @param \Generated\Shared\Transfer\RestCurrencyTransfer $restCurrencyTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function mapVolumePriceDataToPriceProductTransfer(
        PriceProductTransfer $priceProductTransfer,
        RestProductPriceVolumesAttributesTransfer $restProductPriceVolumesAttributesTransfer,
        RestCurrencyTransfer $restCurrencyTransfer
    ): PriceProductTransfer {
        $groupKey = sprintf('%s-%s', $priceProductTransfer->getGroupKey(), $restProductPriceVolumesAttributesTransfer->getQuantity());
        $moneyValueTransfer = $priceProductTransfer->getMoneyValue() ?? new MoneyValueTransfer();
        $moneyValueTransfer
            ->fromArray($restProductPriceVolumesAttributesTransfer->toArray(), true)
            ->setPriceData($this->utilEncodingService->encodeJson($restProductPriceVolumesAttributesTransfer->toArray()))
            ->setCurrency((new CurrencyTransfer())->fromArray($restCurrencyTransfer->toArray(), true));

        return $priceProductTransfer
            ->setVolumeQuantity($restProductPriceVolumesAttributesTransfer->getQuantity())
            ->setGroupKey($groupKey)
            ->setIsMergeable(false)
            ->setMoneyValue($moneyValueTransfer);
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     * @param string $productConfigurationInstanceHash
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    protected function fillUpPriceDimensionWithProductConfigurationInstanceHash(
        ArrayObject $priceProductTransfers,
        string $productConfigurationInstanceHash
    ): ArrayObject {
        foreach ($priceProductTransfers as $priceProductTransfer) {
            $priceProductDimensionTransfer = $priceProductTransfer->getPriceDimension() ?? new PriceProductDimensionTransfer();
            $priceProductDimensionTransfer->setProductConfigurationInstanceHash($productConfigurationInstanceHash);

            $priceProductTransfer->setPriceDimension($priceProductDimensionTransfer);
        }

        return $priceProductTransfers;
    }
}
