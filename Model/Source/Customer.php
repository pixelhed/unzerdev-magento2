<?php
declare(strict_types=1);

namespace Unzer\PAPI\Model\Source;

use UnzerSDK\Resources\Customer as UnzerCustomer;

/**
 * This represents the customer resource.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
class Customer extends UnzerCustomer
{
    /** @var string|null $threatMetrixId */
    protected ?string $threatMetrixId;

    /**
     * Set Threat Metrix ID
     *
     * @param string|null $threatMetrixId
     * @return Customer
     */
    public function setThreatMetrixId(?string $threatMetrixId): Customer
    {
        $this->threatMetrixId = $threatMetrixId;
        return $this;
    }

    /**
     * Get Threat Metrix ID
     *
     * @return string|null
     */
    public function getThreatMetrixId(): ?string
    {
        return $this->threatMetrixId;
    }
}
