<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\Authorization;

use Spryker\Zed\Authorization\AuthorizationConfig as SprykerAuthorizationConfig;

class AuthorizationConfig extends SprykerAuthorizationConfig
{
    /**
     * Specification:
     * - Defines if multiple authorization strategies can be executed during request.
     *
     * @api
     *
     * @deprecated Will be removed with next major. Multistrategy authorization will be enabled by default.
     *
     * @return bool
     */
    public function isMultistrategyAuthorizationAllowed(): bool
    {
        return true;
    }
}
