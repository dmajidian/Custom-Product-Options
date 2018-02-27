<?php
/**
 * Created by PhpStorm.
 * User: WEB_Freelance 2017-4
 * Date: 10/31/2017
 * Time: 1:11 PM
 */

namespace Evil\Custom\Model;

use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;

class EvilCustomOptionValues extends AbstractModel
{

    protected $_dateTime;

    protected function _construct()
    {
        $this->_init(\Evil\Custom\Model\ResourceModel\EvilCustomOptionValues::class);
    }
}
