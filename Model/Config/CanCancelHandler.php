<?php
declare(strict_types=1);

namespace Unzer\PAPI\Model\Config;

use Magento\Sales\Model\Order\Payment;

/**
 * Handler for checking if payments can be canceled
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
class CanCancelHandler extends CanRefundHandler
{
    /**
     * @inheritDoc
     */
    public function handle(array $subject, $storeId = null)
    {
        $payment = $subject['payment']->getPayment();
        if (!$payment instanceof Payment) {
            return false;
        }
        if ($payment->getBaseAmountAuthorized() > $payment->getBaseAmountCanceled() ||
            $payment->getBaseAmountPaid() > $payment->getBaseAmountCanceled()) {
            return true;
        }
        return false;
    }
}
