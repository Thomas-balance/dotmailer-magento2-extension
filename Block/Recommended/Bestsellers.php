<?php

namespace Dotdigitalgroup\Email\Block\Recommended;

use Magento\Store\Model\Store;

class Bestsellers extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    public $helper;
    /**
     * @var \Dotdigitalgroup\Email\Helper\Recommended
     */
    public $recommnededHelper;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productFactory;
    /**
     * @var \Dotdigitalgroup\Email\Model\ResourceModel\CatalogFactory
     */
    public $catalogFactory;

    /**
     * Bestsellers constructor.
     *
     * @param \Dotdigitalgroup\Email\Model\ResourceModel\CatalogFactory           $catalogFactory
     * @param \Dotdigitalgroup\Email\Helper\Data                                  $helper
     * @param \Dotdigitalgroup\Email\Helper\Recommended                           $recommended
     * @param \Magento\Catalog\Block\Product\Context                              $context
     * @param \Magento\Catalog\Model\ProductFactory                               $productFactory
     * @param array                                                               $data
     */
    public function __construct(
        \Dotdigitalgroup\Email\Model\ResourceModel\CatalogFactory $catalogFactory,
        \Dotdigitalgroup\Email\Helper\Data $helper,
        \Dotdigitalgroup\Email\Helper\Recommended $recommended,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->productFactory     = $productFactory;
        $this->helper             = $helper;
        $this->recommnededHelper  = $recommended;
        $this->catalogFactory     = $catalogFactory;
        parent::__construct($context, $data);
    }

    /**
     * Collection
     *
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getLoadedProductCollection()
    {
        $params = $this->getRequest()->getParams();
        if (! isset($params['code']) || ! $this->helper->isCodeValid($params['code'])) {
            $this->helper->log('Best sellers no valid code is set');
            return [];
        }

        //mode param grid/list
        $mode = $this->getRequest()->getActionName();
        //limit of the products to display
        $limit = $this->recommnededHelper->getDisplayLimitByMode($mode);
        //date range
        $from = $this->recommnededHelper->getTimeFromConfig($mode);
        $to = $this->_localeDate->date()->format(\Zend_Date::ISO_8601);
        $storeId = $this->_storeManager->getStore()->getId();

        return $this->catalogFactory->create()
            ->getBestsellerCollection($from, $to, $limit, $storeId);
    }

    /**
     * Display type mode.
     *
     * @return mixed|string
     */
    public function getMode()
    {
        return $this->recommnededHelper->getDisplayType();
    }

    /**
     * @param $store
     *
     * @return mixed
     */
    public function getTextForUrl($store)
    {
        /** @var Store $store */
        $store = $this->_storeManager->getStore($store);

        return $store->getConfig(
            \Dotdigitalgroup\Email\Helper\Config::XML_PATH_CONNECTOR_DYNAMIC_CONTENT_LINK_TEXT
        );
    }
}
