<?php
declare(strict_types=1);

namespace Unzer\PAPI\Model\Config;

use Magento\Vault\Model\VaultPaymentInterface;
use Unzer\PAPI\Model\Config;
use Unzer\PAPI\Model\Method\Base as MethodBase;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * JavaScript configuration provider
 *
 * Copyright (C) 2021 - today Unzer GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.unzer.com/
 */
class Provider implements ConfigProviderInterface
{
    /**
     * @var array
     */
    protected array $_methodCodes = [
        Config::METHOD_BANK_TRANSFER,
        Config::METHOD_CARDS,
        Config::METHOD_DIRECT_DEBIT,
        Config::METHOD_DIRECT_DEBIT_SECURED,
        Config::METHOD_EPS,
        Config::METHOD_GIROPAY,
        Config::METHOD_IDEAL,
        Config::METHOD_INVOICE,
        Config::METHOD_INVOICE_SECURED,
        Config::METHOD_INVOICE_SECURED_B2B,
        Config::METHOD_PAYLATER_INVOICE,
        Config::METHOD_PAYLATER_INVOICE_B2B,
        Config::METHOD_PAYPAL,
        Config::METHOD_SOFORT,
        Config::METHOD_ALIPAY,
        Config::METHOD_WECHATPAY,
        Config::METHOD_PRZELEWY24,
        Config::METHOD_BANCONTACT,
        Config::METHOD_PREPAYMENT,
        Config::METHOD_APPLEPAY,
    ];

    /**
     * @var Config
     */
    private Config $_moduleConfig;

    /**
     * @var PaymentHelper
     */
    private PaymentHelper $_paymentHelper;

    /**
     * Provider constructor.
     * @param Config $moduleConfig
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(Config $moduleConfig, PaymentHelper $paymentHelper)
    {
        $this->_moduleConfig = $moduleConfig;
        $this->_paymentHelper = $paymentHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @throws LocalizedException
     */
    public function getConfig(): array
    {
        $methodConfigs = [
            Config::METHOD_BASE => [
                'publicKey' => $this->_moduleConfig->getPublicKey(),
            ],
        ];

        foreach ($this->_methodCodes as $methodCode) {
            /** @var MethodBase $model */
            $model = $this->_paymentHelper->getMethodInstance($methodCode);
            if ($model instanceof VaultPaymentInterface) {
                continue;
            }

            if ($model->isAvailable()) {
                $methodConfigs[$model->getCode()] = $model->getFrontendConfig();
            }
        }

        return [
            'payment' => array_filter($methodConfigs),
        ];
    }
}
