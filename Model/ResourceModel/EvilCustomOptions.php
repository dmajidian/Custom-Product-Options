<?php
/**
 * Created by PhpStorm.
 * User: WEB_Freelance 2017-4
 * Date: 10/31/2017
 * Time: 1:17 PM
 */
namespace Evil\Custom\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Contact Resource Model
 *
 * @author      Pierre FAY
 */
class EvilCustomOptions extends AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('evil_custom_options', 'option_id');
    }
}
