<?php
/**
 * Created in PhpStorm.
 * by marcin.paul
 * on 07, styczeń, 10:54, 2021
 * @category Edirect
 * @package  Edirect_Module
 * @author   marcin.paul <marcin.paul@edirect24.pl>
 * @projekt   Erli
 * @copyright Copyright (C) 2020 marcin.paul
 **/

namespace Edirect\Erli\Model\System\Message;

use Magento\Framework\Notification\MessageInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Erli Notification Management
 * Class ErliNotification
 */
class ErliNotification implements MessageInterface
{

    const MESSAGE_IDENTITY = 'erli_system_message';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * ErliNotification constructor.
     * @param UrlInterface $urlBuilder
     * @param ResourceConnection $resource
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ResourceConnection $resource
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->resource = $resource;
    }

    /**
     * Retrieve unique system message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return self::MESSAGE_IDENTITY;
    }

    /**
     * Check whether the system message should be shown
     *
     * @return bool
     */
    public function isDisplayed()
    {
        $problematicOrders = $this->resource->getConnection()->fetchCol('SELECT order_id FROM ed_erli_problematic_orders;');

        if (count($problematicOrders) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve system message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        return __('There was a problem syncing orders with erli. Please check <a href="%1">here</a>.', $this->urlBuilder->getRouteUrl('erli/erlilog/index', ['key' => $this->urlBuilder->getSecretKey('erli', 'erlilog', 'index')]));
    }

    /**
     * Retrieve system message severity
     * Possible default system message types:
     * - MessageInterface::SEVERITY_CRITICAL
     * - MessageInterface::SEVERITY_MAJOR
     * - MessageInterface::SEVERITY_MINOR
     * - MessageInterface::SEVERITY_NOTICE
     *
     * @return int
     */
    public function getSeverity()
    {
        return MessageInterface::SEVERITY_MAJOR;
    }
}
