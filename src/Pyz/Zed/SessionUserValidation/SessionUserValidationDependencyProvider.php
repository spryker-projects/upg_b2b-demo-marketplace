<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\SessionUserValidation;

use Spryker\Zed\SessionUserValidation\SessionUserValidationDependencyProvider as SprykerSessionUserValidationDependencyProvider;

class SessionUserValidationDependencyProvider extends SprykerSessionUserValidationDependencyProvider
{
    /**
     * @return \Spryker\Zed\SessionUserValidationExtension\Dependency\Plugin\SessionUserSaverPluginInterface
     */
    protected function getSessionUserSaverPlugin(): SessionUserSaverPluginInterface
    {
        return new SessionRedisSessionUserSaverPlugin();
    }

    /**
     * @return \Spryker\Zed\SessionUserValidationExtension\Dependency\Plugin\SessionUserValidatorPluginInterface
     */
    protected function getSessionUserValidatorPlugin(): SessionUserValidatorPluginInterface
    {
        return new SessionRedisSessionUserValidatorPlugin();
    }
}
