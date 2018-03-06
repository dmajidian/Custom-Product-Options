<?php
/**
 * Created by PhpStorm.
 * User: wsun
 * Date: 11/09/2015
 * Time: 3:54 PM
 */
namespace Evil\Custom\Controller\Catalog\Adminhtml\Product;

class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Save
{
    private $storeManager;

    const DESTINATION_FOLDER = 'evilcustom';

    const CTM_PRODUCT_ID = 'product_id';
    const CTM_OPTION_ID = 'option_id';
    const CTM_OPTION_TYPE_ID = 'option_type_id';
    const CTM_HEADLINE    = 'headline';
    const CTM_DESCRIPTION = 'description';
    const CTM_PROMO = 'promo';
    const CTM_SWATCH = 'swatch';
    const CTM_IMAGE1 = 'image1';
    const CTM_IMAGE2 = 'image2';
    const CTM_ICON = 'icon';
    const CTM_ICON_HIDDEN = 'icon_hidden';
    const CTM_SWATCH_HIDDEN = 'swatch_hidden';
    const CTM_IMAGE1_HIDDEN = 'image1_hidden';
    const CTM_IMAGE2_HIDDEN = 'image2_hidden';
    const CTM_SWITCH = 'switch';
    const CTM_PARENT = 'parentable';

    /**
     * Save product action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store', 0);
        $store = $this->getStoreManager()->getStore($storeId);
        $this->getStoreManager()->setCurrentStore($store->getCode());
        $redirectBack = $this->getRequest()->getParam('back', false);
        $productId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $productAttributeSetId = $this->getRequest()->getParam('set');
        $productTypeId = $this->getRequest()->getParam('type');
        if ($data) {
            try {
                $product = $this->initializationHelper->initialize(
                    $this->productBuilder->build($this->getRequest())
                );
                $this->productTypeManager->processProduct($product);

                if (isset($data['product'][$product->getIdFieldName()])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save product'));
                }

                $originalSku = $product->getSku();
                $product->save();
                $this->handleImageRemoveError($data, $product->getId());
                $this->getCategoryLinkManagement()->assignProductToCategories(
                    $product->getSku(),
                    $product->getCategoryIds()
                );
                $productId = $product->getEntityId();
                $productAttributeSetId = $product->getAttributeSetId();
                $productTypeId = $product->getTypeId();

                $this->copyToStores($data, $productId);

                /**
                 * Evil Custom
                 * Handle Custom Product Options Here
                 **/
                $this->handleCustomOptions($productId);


                $this->messageManager->addSuccess(__('You saved the product.'));
                $this->getDataPersistor()->clear('catalog_product');
                if ($product->getSku() != $originalSku) {
                    $this->messageManager->addNotice(
                        __(
                            'SKU for product %1 has been changed to %2.',
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName()),
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getSku())
                        )
                    );
                }
                $this->_eventManager->dispatch(
                    'controller_action_catalog_product_save_entity_after',
                    ['controller' => $this, 'product' => $product]
                );

                if ($redirectBack === 'duplicate') {
                    $newProduct = $this->productCopier->copy($product);
                    $this->messageManager->addSuccess(__('You duplicated the product.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->getDataPersistor()->set('catalog_product', $data);
                $redirectBack = $productId ? true : 'new';
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->messageManager->addError($e->getMessage());
                $this->getDataPersistor()->set('catalog_product', $data);
                $redirectBack = $productId ? true : 'new';
            }
        } else {
            $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
            $this->messageManager->addError('No data to save');
            return $resultRedirect;
        }

        if ($redirectBack === 'new') {
            $resultRedirect->setPath(
                'catalog/*/new',
                ['set' => $productAttributeSetId, 'type' => $productTypeId]
            );
        } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $newProduct->getEntityId(), 'back' => null, '_current' => true]
            );
        } elseif ($redirectBack) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $productId, '_current' => true, 'set' => $productAttributeSetId]
            );
        } else {
            $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
        }
        return $resultRedirect;
    }

    /**
     * Saves the Evil Controller Custom Options.
     *
     * @param $productId
     */
    private function handleCustomOptions($productId)
    {
        $optionvalues = $this->generateCustomOptionValues($productId);
        $options = $this->generateCustomOptions($productId);

        $this->saveOptions($options);
        $this->saveOptionValues($optionvalues);

    }

    /**
     * @return mixed
     */
    private function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Store\Model\StoreManagerInterface');
        }
        return $this->storeManager;
    }

    /**
     * @return \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    private function getCategoryLinkManagement()
    {
        if (null === $this->categoryLinkManagement) {
            $this->categoryLinkManagement = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Api\CategoryLinkManagementInterface');
        }
        return $this->categoryLinkManagement;
    }

    /**
     * @param array $postData
     * @param int $productId
     */
    private function handleImageRemoveError($postData, $productId)
    {
        if (isset($postData['product']['media_gallery']['images'])) {
            $removedImagesAmount = 0;
            foreach ($postData['product']['media_gallery']['images'] as $image) {
                if (!empty($image['removed'])) {
                    $removedImagesAmount++;
                }
            }
            if ($removedImagesAmount) {
                $expectedImagesAmount = count($postData['product']['media_gallery']['images']) - $removedImagesAmount;
                $product = $this->productRepository->getById($productId);
                if ($expectedImagesAmount != count($product->getMediaGallery('images'))) {
                    $this->messageManager->addNotice(
                        __('The image cannot be removed as it has been assigned to the other image role')
                    );
                }
            }
        }
    }

    /**
     * @param array $options
     * @return bool
     */
    private function saveOptionValues(array $options)
    {
        foreach ($options as $key => $option) {
            foreach ($option as $opt) {
                $model = $this->_objectManager->create('Evil\Custom\Model\EvilCustomOptionValues');
                $model->load($opt['option_type_id'], 'option_type_id');
                if (!$model->getData('option_type_id')) {
                  $this->_resources = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
                  $connection= $this->_resources->getConnection();
                  $themeTable = $this->_resources->getTableName('evil_custom_options_value');
                  $sql = 'INSERT INTO ' . $themeTable .
                    '(`'.
                    static::CTM_OPTION_TYPE_ID.'`, `'.
                    static::CTM_OPTION_ID.'`, `'.
                    static::CTM_SWATCH.'`, `'.
                    static::CTM_IMAGE1.'`, `'.
                    static::CTM_IMAGE2.'`)' .
                    ' VALUES ('.
                    $opt[static::CTM_OPTION_TYPE_ID].', '.
                    $opt[static::CTM_OPTION_ID].', "'.
                    $opt[static::CTM_SWATCH].'", "'.
                    $opt[static::CTM_IMAGE1].'", "'.
                    $opt[static::CTM_IMAGE2].'")';
                  $connection->query($sql);
                } else {
                    $model
                        ->setData(static::CTM_OPTION_ID, $opt[static::CTM_OPTION_ID])
                        ->setData(static::CTM_SWATCH, $opt[static::CTM_SWATCH])
                        ->setData(static::CTM_IMAGE1, $opt[static::CTM_IMAGE1])
                        ->setData(static::CTM_IMAGE2, $opt[static::CTM_IMAGE2])
                        ->save();
                }
            }

        }

        return true;
    }

    /**
     * @param array $options
     * @return bool
     */
    private function saveOptions(array $options)
    {
        foreach ($options as $opt) {
            $model = $this->_objectManager->create('Evil\Custom\Model\EvilCustomOptions');
            $model->load($opt['option_id'], 'option_id');
            if (!$model->getData('option_id')) {
              $this->_resources = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
              $connection= $this->_resources->getConnection();
              $themeTable = $this->_resources->getTableName('evil_custom_options');
              $sql = 'INSERT INTO ' . $themeTable .
                '(`'.
                static::CTM_OPTION_ID.'`, `'.
                static::CTM_PRODUCT_ID.'`, `'.
                static::CTM_HEADLINE.'`, `'.
                static::CTM_DESCRIPTION.'`, `'.
                static::CTM_PROMO.'`, `'.
                static::CTM_ICON.'`, `'.
                static::CTM_SWITCH.'`, `'.
                static::CTM_PARENT.'`)' .
                ' VALUES ('.
                $opt[static::CTM_OPTION_ID].', '.
                $opt[static::CTM_PRODUCT_ID].', "'.
                $opt[static::CTM_HEADLINE].'", "'.
                $opt[static::CTM_DESCRIPTION].'", "'.
                $opt[static::CTM_PROMO].'", "'.
                $opt[static::CTM_ICON].'", "'.
                $opt[static::CTM_SWITCH].'", "'.
                $opt[static::CTM_PARENT].'")';
              $connection->query($sql);
            } else {
                $model
                    ->setData(static::CTM_PRODUCT_ID, $opt[static::CTM_PRODUCT_ID])
                    ->setData(static::CTM_HEADLINE, $opt[static::CTM_HEADLINE])
                    ->setData(static::CTM_DESCRIPTION, $opt[static::CTM_DESCRIPTION])
                    ->setData(static::CTM_PROMO, $opt[static::CTM_PROMO])
                    ->setData(static::CTM_ICON, $opt[static::CTM_ICON])
                    ->setData(static::CTM_SWITCH, $opt[static::CTM_SWITCH])
                    ->setData(static::CTM_PARENT, $opt[static::CTM_PARENT])
                    ->save();
            }
        }



        return true;
    }
    /**
     * @param $files
     * @param $post
     * @param $destinationFolder
     * @return array
     */
    private function generateCustomOptionValues($productid)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $_objectManager->get('\Magento\Catalog\Model\Product')->load($productid);
        $options = $_objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
        $newoptions = [];
        if(!empty($this->getRequest()->getPostValue('product')['options'])) {
          $post = $this->getRequest()->getPostValue('product')['options'];
        } else {
          return $newoptions;
        }
        $files = $this->getRequest()->getFiles('product')['options'];

        $c = 0;
        foreach ($options as $option) {
            $values = $option->getValues();

            $i = 0;
            foreach ($values as $value) {
                $newoptions[$c][$i][static::CTM_OPTION_ID] = $value->getData(static::CTM_OPTION_ID);
                $newoptions[$c][$i][static::CTM_OPTION_TYPE_ID] = $value->getData(static::CTM_OPTION_TYPE_ID);
                if ($files[$c]['values'][$i]['swatch']['error'] === 0) {
                    $filename = static::uploadFile($files[$c]['values'][$i][static::CTM_SWATCH], 'swatch');
                    $newoptions[$c][$i][static::CTM_SWATCH] = $filename;
                } else {
                    $newoptions[$c][$i][static::CTM_SWATCH] = (!empty($post[$c]['values'][$i][static::CTM_SWATCH_HIDDEN])) ? $post[$c]['values'][$i][static::CTM_SWATCH_HIDDEN] : null;
                }
                if ($files[$c]['values'][$i][static::CTM_IMAGE1]['error'] === 0) {
                    $filename = static::uploadFile($files[$c]['values'][$i][static::CTM_IMAGE1], 'front');
                    $newoptions[$c][$i][static::CTM_IMAGE1] = $filename;
                } else {
                    $newoptions[$c][$i][static::CTM_IMAGE1] = (!empty($post[$c]['values'][$i][static::CTM_IMAGE1_HIDDEN])) ? $post[$c]['values'][$i][static::CTM_IMAGE1_HIDDEN] : null;
                }
                if ($files[$c]['values'][$i][static::CTM_IMAGE2]['error'] === 0) {
                    $filename = static::uploadFile($files[$c]['values'][$i][static::CTM_IMAGE2], 'back');
                    $newoptions[$c][$i][static::CTM_IMAGE2] = $filename;
                } else {
                    $newoptions[$c][$i][static::CTM_IMAGE2] = (!empty($post[$c]['values'][$i][static::CTM_IMAGE2_HIDDEN])) ? $post[$c]['values'][$i][static::CTM_IMAGE2_HIDDEN] : null;
                }
                //var_dump($post[$c]);die;
                $i++;
            }
            $c++;
        }

        return $newoptions;
    }

    private function generateCustomOptions($productid)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $_objectManager->get('\Magento\Catalog\Model\Product')->load($productid);
        $options = $_objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
        $newoptions = [];
        if(!empty($this->getRequest()->getPostValue('product')['options'])) {
          $post = $this->getRequest()->getPostValue('product')['options'];
        } else {
          return $newoptions;
        }
        $files = $this->getRequest()->getFiles('product')['options'];

        $c = 0;
        //echo "<pre>" .print_r($post).'</pre>';
        foreach ($options as $value) {
            $newoptions[$c][static::CTM_PRODUCT_ID] = $productid;
            $newoptions[$c][static::CTM_OPTION_ID] = $value->getData(static::CTM_OPTION_ID);
            $newoptions[$c][static::CTM_HEADLINE] = $post[$c][static::CTM_HEADLINE];
            $newoptions[$c][static::CTM_DESCRIPTION] = $post[$c][static::CTM_DESCRIPTION];
            $newoptions[$c][static::CTM_PROMO] = $post[$c][static::CTM_PROMO];
            $newoptions[$c][static::CTM_SWITCH] = $post[$c][static::CTM_SWITCH];
            if ($files[$c][static::CTM_ICON]['error'] === 0) {
                $filename = static::uploadFile($files[$c][static::CTM_ICON], 'icons');
                $newoptions[$c][static::CTM_ICON] = $filename;
            } else {
                //echo 'xxxxxx';
                $newoptions[$c][static::CTM_ICON] = (!empty($post[$c][static::CTM_ICON_HIDDEN])) ? $post[$c][static::CTM_ICON_HIDDEN] : null;
            }
            //exit;
            $newoptions[$c][static::CTM_PARENT] = $post[$c][static::CTM_PARENT];

            $c++;
        }

        return $newoptions;
    }

    /**
     * @param array $fileInfo
     * @return null|string
     */
    private static function uploadFile(array $fileInfo, $dir)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
        $mediaPath  =   $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        $media  =  $mediaPath.static::DESTINATION_FOLDER . DIRECTORY_SEPARATOR . $dir  .DIRECTORY_SEPARATOR ;

        if(is_array($fileInfo))
        {
            $file_name = mt_rand() . '_' . $fileInfo['name'];
            $file_size = $fileInfo['size'];
            $file_tmp  = $fileInfo['tmp_name'];
            $file_type = $fileInfo['type'];
            if(move_uploaded_file($file_tmp, $media . $file_name))
            {
                return static::DESTINATION_FOLDER . '/' . $dir .'/' . $file_name;
            }
        }

        return null;
    }
}
