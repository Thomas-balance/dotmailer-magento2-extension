<?php

namespace Dotdigitalgroup\Email\Model\Newsletter;

class SubscriberExporter
{
    /**
     * @var \Dotdigitalgroup\Email\Model\ImporterFactory
     */
    public $importerFactory;

    /**
     * @var \Dotdigitalgroup\Email\Helper\File
     */
    public $file;

    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    public $subscriberFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Dotdigitalgroup\Email\Model\ResourceModel\Contact
     */
    private $contactResource;

    /**
     * @param \Dotdigitalgroup\Email\Model\ImporterFactory $importerFactory
     * @param \Dotdigitalgroup\Email\Helper\File $file
     * @param \Dotdigitalgroup\Email\Helper\Data $helper
     * @param \Dotdigitalgroup\Email\Model\ResourceModel\Contact $contactResource
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Dotdigitalgroup\Email\Model\ImporterFactory $importerFactory,
        \Dotdigitalgroup\Email\Helper\File $file,
        \Dotdigitalgroup\Email\Helper\Data $helper,
        \Dotdigitalgroup\Email\Model\ResourceModel\Contact $contactResource,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->contactResource = $contactResource;
        $this->importerFactory   = $importerFactory;
        $this->file              = $file;
        $this->helper            = $helper;
        $this->subscriberFactory = $subscriberFactory;
        $this->storeManager      = $storeManager;
    }

    /**
     * Export subscribers
     *
     * @param \Magento\Store\Model\Website $website
     * @param  \Dotdigitalgroup\Email\Model\ResourceModel\Contact\Collection $subscribers
     *
     * @return int
     */
    public function exportSubscribers($website, $subscribers)
    {
        $updated = 0;
        $subscribersFilename = strtolower($website->getCode() . '_subscribers_' . date('d_m_Y_Hi') . '.csv');
        //get mapped storename
        $subscriberStorename = $this->helper->getMappedStoreName($website);
        //file headers
        $this->file->outputCSV(
            $this->file->getFilePath($subscribersFilename),
            ['Email', 'emailType', $subscriberStorename]
        );
        $subscriberFactory = $this->subscriberFactory->create();
        $subscribersData = $subscriberFactory->getCollection()
            ->addFieldToFilter(
                'subscriber_email',
                ['in' => $subscribers->getColumnValues('email')]
            )
            ->addFieldToSelect(['subscriber_email', 'store_id'])
            ->toArray();
        foreach ($subscribers as $subscriber) {
            $email = $subscriber->getEmail();
            $storeId = $this->getStoreIdForSubscriber(
                $email,
                $subscribersData['items']
            );
            $storeName = $this->storeManager->getStore($storeId)->getName();
            // save data for subscribers
            $this->file->outputCSV(
                $this->file->getFilePath($subscribersFilename),
                [$email, 'Html', $storeName]
            );
            $subscriber->setSubscriberImported(1);
            $this->contactResource->save($subscriber);
            $updated++;
        }
        $this->helper->log('Subscriber filename: ' . $subscribersFilename);
        //register in queue with importer
        $this->importerFactory->create()
            ->registerQueue(
                \Dotdigitalgroup\Email\Model\Importer::IMPORT_TYPE_SUBSCRIBERS,
                '',
                \Dotdigitalgroup\Email\Model\Importer::MODE_BULK,
                $website->getId(),
                $subscribersFilename
            );

        return $updated;
    }

    /**
     * Get the store id from newsletter_subscriber, return default if not found.
     *
     * @param string $email
     * @param array $subscribers
     *
     * @return int
     */
    public function getStoreIdForSubscriber($email, $subscribers)
    {
        $defaultStore = 1;
        foreach ($subscribers as $subscriber) {
            if ($subscriber['subscriber_email'] == $email) {
                return $subscriber['store_id'];
            }
        }
        return $defaultStore;
    }
}
