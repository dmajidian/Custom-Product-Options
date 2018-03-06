<?php
/**
 * Created by PhpStorm.
 * User: WEB_Freelance 2017-4
 * Date: 10/31/2017
 * Time: 1:20 PM
 */
namespace Evil\Custom\Model\ResourceModel\EvilCustomOptions;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Evil\Custom\Model\EvilCustomOptions', 'Evil\Custom\Model\ResourceModel\EvilCustomOptions');
    }
}