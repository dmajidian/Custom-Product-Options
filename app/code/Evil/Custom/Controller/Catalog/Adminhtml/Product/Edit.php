<?php
/**
 * Created by PhpStorm.
 * User: wsun
 * Date: 11/09/2015
 * Time: 3:54 PM
 */
namespace Evil\Custom\Controller\Catalog\Adminhtml\Product;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Product\Edit
{
    /**
     * Product edit form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->messageManager->addSuccess('Message from Evil Custom admin controller.');
        return parent::execute();
    }
}