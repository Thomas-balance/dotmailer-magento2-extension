<?php

namespace Dotdigitalgroup\Email\Controller\Adminhtml\Rules;

class Delete extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var \Dotdigitalgroup\Email\Model\Rules
     */
    private $rules;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * Delete constructor.
     *
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Dotdigitalgroup\Email\Model\Rules         $rules
     * @param \Magento\Framework\Escaper                 $escaper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Dotdigitalgroup\Email\Model\Rules $rules,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($context);
        $this->rules        = $rules;
        $this->storeManager = $storeManagerInterface;
        $this->escaper      = $escaper;
    }

    /**
     * Check the permission to run it.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dotdigitalgroup_Email::exclusion_rules');
    }

    /**
     * Execute method.
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->rules;
                $model->setId($id);
                $model->getResource()->delete($model);
                $this->messageManager->addSuccessMessage(
                    __('The rule has been deleted.')
                );
                $this->_redirect('*/*/');

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('An error occurred while deleting the rule. Please review the log and try again.')
                );
                $this->_redirect(
                    '*/*/edit',
                    ['id' => $id]
                );

                return;
            }
        }
        $this->messageManager->addErrorMessage(
            __('Unable to find a rule to delete.')
        );
        $this->_redirect('*/*/');
    }
}
