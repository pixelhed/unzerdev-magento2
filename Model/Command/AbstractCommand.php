<?php

namespace Unzer\PAPI\Model\Command;

use Unzer\PAPI\Helper\Order;
use Unzer\PAPI\Model\Config;
use Unzer\PAPI\Model\Method\Observer\BaseDataAssignObserver;
use UnzerSDK\Unzer;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Services\ResourceNameService;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use function get_class;

/**
 * Abstract Command for using the heidelpay SDK
 *
 * Copyright (C) 2019 heidelpay GmbH
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
 * @link  https://docs.heidelpay.com/
 *
 * @author Justin Nuß
 *
 * @package  heidelpay/magento2-merchant-gateway
 */
abstract class AbstractCommand implements CommandInterface
{
    public const KEY_PAYMENT_ID = 'payment_id';

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Heidelpay
     */
    protected $_client;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Order
     */
    protected $_orderHelper;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * AbstractCommand constructor.
     * @param Session $checkoutSession
     * @param Config $config
     * @param LoggerInterface $logger
     * @param Order $orderHelper
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $checkoutSession,
        Config $config,
        LoggerInterface $logger,
        Order $orderHelper,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_config = $config;
        $this->_logger = $logger;
        $this->_orderHelper = $orderHelper;
        $this->_urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns the URL to which customers are redirected after payment.
     *
     * @return string
     */
    protected function _getCallbackUrl(): string
    {
        return $this->_urlBuilder->getUrl('hpmgw/payment/callback');
    }

    /**
     * @param string|null $storeCode
     * @return Heidelpay
     */
    protected function _getClient(string $storeCode = null): Unzer
    {
        if ($this->_client === null) {
            $this->_client = $this->_config->getHeidelpayClient($storeCode);
        }

        return $this->_client;
    }

    /**
     * Returns the customer ID for given current payment or quote.
     *
     * @param InfoInterface $payment
     * @param \Magento\Sales\Model\Order $order
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \UnzerSDK\Exceptions\UnzerApiException
     */
    protected function _getCustomerId(InfoInterface $payment, \Magento\Sales\Model\Order $order): ?string
    {
        /** @var string|null $customerId */
        $customerId = $payment->getAdditionalInformation(BaseDataAssignObserver::KEY_CUSTOMER_ID);

        if (empty($customerId)) {
            try {
                $papiCustomer = $this->_orderHelper->createCustomerFromOrder($order, $order->getCustomerEmail(), true);
                $customerId = $papiCustomer->getId();
            } catch (\Exception $exception) {
                $customerId = null;
            }
        }

        if (empty($customerId)) {
            return null;
        }

        /** @var Customer $customer */
        $customer = $this->_getClient()->fetchCustomer($customerId);

        if (!$this->_orderHelper->validateGatewayCustomerAgainstOrder($order, $customer)) {
            $this->_orderHelper->updateGatewayCustomerFromOrder($order, $customer);
        }

        return $customerId;
    }

    /**
     * Sets the transaction information on the given payment from an authorization or charge.
     *
     * @param OrderPayment $payment
     * @param Authorization|Charge|AbstractUnzerResource $resource
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _setPaymentTransaction(
        OrderPayment $payment,
        AbstractUnzerResource $resource
    ): void
    {
        $payment->setLastTransId($resource->getId());
        $payment->setTransactionId($resource->getId());
        $payment->setIsTransactionClosed(false);
        $payment->setIsTransactionPending($resource->isPending());

        $payment->setAdditionalInformation(static::KEY_PAYMENT_ID, $resource->getPaymentId());
    }

    /**
     * Writes heidelpay Ids of the transaction to order history.
     *
     * @param SalesOrder $order
     * @param AbstractTransactionType $transaction
     */
    protected function addHeidelpayIdsToHistory(SalesOrder $order, AbstractTransactionType $transaction): void
    {
        $order->addCommentToStatusHistory(
            'heidelpay ' . ResourceNameService::getClassShortName(get_class($transaction)) . ' transaction: ' .
            'UniqueId: ' . $transaction->getUniqueId() . ' | ShortId: ' . $transaction->getShortId()
        );
    }

    /**
     * Add heidelpay error messages to order history.
     *
     * @param SalesOrder $order
     * @param string $code
     * @param string $message
     */
    protected function addHeidelpayErrorToOrderHistory(SalesOrder $order, $code, $message): void {
        $order->addCommentToStatusHistory("heidelpay Error (${code}): ${message}");
    }

    /**
     * @param int $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCode(int $storeId)
    {
        return $this->storeManager->getStore($storeId)->getCode();
    }
}
