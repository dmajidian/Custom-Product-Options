<?php
/**
 * Created by PhpStorm.
 * User: dmaji
 * Date: 10/30/2017
 * Time: 9:25 PM
 */
namespace Evil\Custom\Controller\Catalog\Adminhtml\Product;

use Magento\Framework\Data\Form\Element\Image as ImageField;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Escaper;
use Evil\Custom\Model\Catalog\Image as CatalogImage;
use Magento\Framework\UrlInterface;

/**
 * @method string getValue()
 */
class Image extends ImageField
{
    const NAME = 'file';
    protected $imageModel;

    /**
     * @param CatalogImage $imageModel
     * @param ElementFactory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        CatalogImage $imageModel,
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        UrlInterface $urlBuilder,
        $data = []
    )
    {
        $this->imageModel = $imageModel;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $urlBuilder, $data);
    }
    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->imageModel->getBaseUrl().$this->getValue();
        }
        return $url;
    }
}