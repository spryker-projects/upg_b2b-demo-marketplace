<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\Shipment;

use Spryker\Zed\Shipment\ShipmentConfig as SprykerShipmentConfig;

class ShipmentConfig extends SprykerShipmentConfig
{
    /**
     * Specification:
     * - If set to `true` a stack of {@link \Spryker\Zed\CalculationExtension\Dependency\Plugin\QuotePostRecalculatePluginStrategyInterface} will be executed after quote recalculation.
     * - Impacts {@link \Spryker\Zed\Shipment\Business\ShipmentFacade::expandQuoteWithShipmentGroups()} method.
     *
     * @api
     *
     * @return bool
     */
    public function shouldExecuteQuotePostRecalculationPlugins(): bool
    {
        return false;
    }
}
