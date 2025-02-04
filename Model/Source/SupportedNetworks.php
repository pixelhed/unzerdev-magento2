<?php
declare(strict_types=1);

namespace Unzer\PAPI\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
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
class SupportedNetworks implements ArrayInterface
{
    /**
     * Supported Networks
     *
     * @var array
     */
    protected static array $networks = [
        //'amex',
        //'bancomat',
        //'bancontact',
        //'cartesBancaires',
        //'chinaUnionPay',
        //'dankort',
        //'discover',
        //'eftpos',
        //'electron',
        //'elo',
        //'girocard',
        //'interac',
        //'jcb',
        //'mada',
        'maestro',
        'masterCard',
        //'mir',
        //'privateLabel',
        'visa',
        //'vPay'
    ];

    /**
     * Return Supported Networks
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach (self::$networks as $network) {
            $options[] = [
                'value' => $network,
                'label' => $network,
            ];
        }
        return $options;
    }
}
