<?php
/**
 * Created by PhpStorm.
 * User: dmaji
 * Date: 11/1/2017
 * Time: 11:22 PM
 */
namespace Evil\Custom\Model;
/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_request = $request;
    }



    public function getData()
    {
       die();
    }
}