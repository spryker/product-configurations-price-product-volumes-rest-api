<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductConfigurationsPriceProductVolumesRestApi\Processor\Mapper;

use Generated\Shared\Transfer\ProductConfigurationInstanceTransfer;
use Generated\Shared\Transfer\RestCartItemProductConfigurationInstanceAttributesTransfer;

interface RestCartItemProductConfigurationPriceProductVolumeMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\RestCartItemProductConfigurationInstanceAttributesTransfer $restCartItemProductConfigurationInstanceAttributesTransfer
     * @param \Generated\Shared\Transfer\ProductConfigurationInstanceTransfer $productConfigurationInstanceTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConfigurationInstanceTransfer
     */
    public function mapRestCartItemProductConfigurationInstanceAttributesToProductConfigurationInstance(
        RestCartItemProductConfigurationInstanceAttributesTransfer $restCartItemProductConfigurationInstanceAttributesTransfer,
        ProductConfigurationInstanceTransfer $productConfigurationInstanceTransfer
    ): ProductConfigurationInstanceTransfer;
}
