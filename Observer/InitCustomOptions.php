<?php
namespace Evil\Custom\Observer;

use Magento\Framework\Event\Observer;

class InitCustomOptions implements \Magento\Framework\Event\ObserverInterface
{
    const CTM_VARNAME     = 'EvilCustomOptions';
    protected $_request;
    protected $_view;
    private $_objectManager;
    private $customOptions;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Block\Product\View $view)
    {
        $this->_request = $request;
        $this->_view = $view;
        $this->_objectManager = $objectmanager;
    }


    public function execute(Observer $observer)
    {
        try
        {

            $handle = $this->_request->getParams('id');
            $productid = $handle['id'];

            $customoptions = $this->_objectManager->create('Evil\Custom\Model\EvilCustomOptions')
            ->getCollection()
            ->addFieldToFilter(
                'product_id',
                $productid
            )->setOrder('product_id','DESC');

            $customoptions->load();

            if (count($customoptions->getData()) > 0)
            {
                $returned = $customoptions->getData();

                if($returned) {
                  $data = [];
                  foreach ($returned as $index=>$value)
                  {
                      $data[$value['option_id']] = $value;
                  }

                }
            }

            if(!$data)
            {
              $data = null;
            }

            $this->customOptions = $data;
            $this->initCustomOptions();
            $this->_view->getProduct()->addData([static::CTM_VARNAME => $data]);
        }
        catch (\Exception $exception)
        {
            //$txt = $exception->getMessage();
            //`file_put_contents('observer.txt', print_r($txt, true).PHP_EOL , FILE_APPEND | LOCK_EX);
        }
    }

    private function initCustomOptions()
    {
        $_options = $this->_view->getProduct()->getOptions();
        $parentables = [];
        $all_option_ids = [];
        $i=0;

        foreach($_options as $index=>$_opt)
        {
            $all_option_ids[$i] = $_opt->getOptionId();
            $i++;
        }

        foreach($_options as $index=>$_opt)
        {
            if(in_array($this->customOptions[$_opt->getOptionId()]['parentable'], $all_option_ids))
            {
                $parentables[$index] = $this->customOptions[$_opt->getOptionId()]['parentable'];
            }
        }

        $this->_view->getProduct()->addData(['ctm_parentables' => $parentables]); 

        return null;
    }
    private function setCustomOptions(array $options)
    {
        $this->customOptions = $options;
    }

}
