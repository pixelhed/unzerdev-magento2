<?php

namespace Unzer\PAPI\Controller\Webhooks;

use Exception;
use Unzer\PAPI\Helper\Payment as PaymentHelper;
use Unzer\PAPI\Helper\Webhooks;
use Unzer\PAPI\Model\Config;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\AbstractHeidelpayResource;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\EmulationFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Controller for processing webhook events
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
class Process extends Action implements CsrfAwareActionInterface
{
    /**
     * @var EmulationFactory
     */
    protected $_emulationFactory;

    /**
     * @var Manager
     */
    protected $_eventManager;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Config
     */
    protected $_moduleConfig;

    /**
     * @var PaymentHelper
     */
    protected $_paymentHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Process constructor.
     * @param Context $context
     * @param EmulationFactory $emulationFactory
     * @param Manager $eventManager
     * @param LoggerInterface $logger
     * @param Config $moduleConfig
     * @param PaymentHelper $paymentHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        EmulationFactory $emulationFactory,
        Manager $eventManager,
        LoggerInterface $logger,
        Config $moduleConfig,
        PaymentHelper $paymentHelper,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);

        $this->_emulationFactory = $emulationFactory;
        $this->_eventManager = $eventManager;
        $this->_logger = $logger;
        $this->_moduleConfig = $moduleConfig;
        $this->_paymentHelper = $paymentHelper;
        $this->_storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function execute(): ResponseInterface
    {
        /** @var HttpRequest $request */
        $request = $this->getRequest();

        $requestBody = $request->getContent();

        /** @var HttpResponse $response */
        $response = $this->getResponse();
        $response->setHttpResponseCode(200);
        $response->setBody('OK');

        $store = $this->getStoreFromRequest($request);

        if ($store === null) {
            $response->setStatusCode(404);
            $response->setBody('Not found');
            return $response;
        }

        $emulation = $this->_emulationFactory->create();
        $emulation->startEnvironmentEmulation($store->getId());

        /** @var stdClass $event */
        $event = json_decode($requestBody);

        if (!$event || !$this->isValidEvent($event)) {
            $response->setStatusCode(400);
            $response->setBody('Bad request');
            return $response;
        }

        try {
            $payment = $this->getPaymentFromEvent($requestBody);

            if ($payment !== null && $payment->getOrderId() !== null) {
                /** @var Order $order */
                $order = $this->_objectManager->create(Order::class);
                $order->loadByIncrementId($payment->getOrderId());

                if ($order->getId()) {
                    $this->_paymentHelper->processState($order, $payment);
                } else {
                    $response->setStatusCode(404);
                    $response->setBody('Not found');
                }
            }
        } catch (UnzerApiException $e) {
            $response->setStatusCode(500);
            $response->setBody($e->getClientMessage());

            $this->_logger->error($e->getMerchantMessage(), ['event' => $event]);
        } catch (Exception $e) {
            $response->setStatusCode(500);
            $response->setBody($e->getMessage());
        }

        $emulation->stopEnvironmentEmulation();

        return $response;
    }

    /**
     * @param string $requestBody
     * @return Payment|null
     * @throws UnzerApiException
     */
    protected function getPaymentFromEvent(string $requestBody): ?Payment
    {
        $resource = $this->_moduleConfig
            ->getHeidelpayClient()
            ->fetchResourceFromEvent($requestBody);

        if ($resource instanceof Payment) {
            return $resource;
        } elseif ($resource instanceof AbstractTransactionType) {
            return $resource->getPayment();
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    protected function getPublicKey(): string
    {
        return $this->_moduleConfig->getPublicKey();
    }

    /**
     * Returns whether the given webhook event is valid.
     *
     * @param stdClass $event
     *
     * @return bool
     */
    protected function isValidEvent(stdClass $event): bool
    {
        return isset($event->event)
            && isset($event->publicKey)
            && isset($event->retrieveUrl)
            && $event->publicKey === $this->getPublicKey();
    }

    protected function getStoreFromRequest(RequestInterface $request): ?StoreInterface
    {
        $storeCode = $request->getParam(Webhooks::URL_PARAM_STORE);

        if (empty($storeCode)) {
            return $this->_storeManager->getDefaultStoreView();
        }

        try {
            return $this->_storeManager->getStore($storeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
