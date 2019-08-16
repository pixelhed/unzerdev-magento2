<?php

namespace Heidelpay\Gateway2\Model\Method;

use Heidelpay\Gateway2\Model\Config;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class Invoice extends Base
{
    /**
     * @var Config
     */
    protected $_moduleConfig;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Base constructor.
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $moduleConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param CommandPoolInterface|null $commandPool
     * @param ValidatorPoolInterface|null $validatorPool
     * @param CommandManagerInterface|null $commandExecutor
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        ScopeConfigInterface $scopeConfig,
        Config $moduleConfig,
        PriceCurrencyInterface $priceCurrency,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $scopeConfig,
            $commandPool,
            $validatorPool,
            $commandExecutor,
            $logger
        );

        $this->_moduleConfig = $moduleConfig;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalPaymentInformation(Order $order): string
    {
        /** @var Payment $payment */
        $payment = $this->_moduleConfig
            ->getHeidelpayClient()
            ->fetchPaymentByOrderId($order->getIncrementId());

        /** @var Charge|null $charge */
        $charge = $payment->getChargeByIndex(0);

        if ($charge === null) {
            return '';
        }

        $formattedAmount = $this->_priceCurrency->format(
            $charge->getAmount(),
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $order->getStoreId(),
            $order->getOrderCurrency()
        );

        return __(
            'Please transfer the amount of %1 to the following account after your order has arrived:<br /><br />'
            . 'Holder: %2<br/>'
            . 'IBAN: %3<br/>'
            . 'BIC: %4<br/><br/>'
            . '<i>Please use only this identification number as the descriptor: </i><br/>'
            . '%5',
            $formattedAmount,
            $charge->getHolder(),
            $charge->getIban(),
            $charge->getBic(),
            $charge->getShortId()
        );
    }
}
