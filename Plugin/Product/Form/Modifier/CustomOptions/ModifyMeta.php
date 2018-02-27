<?php
/**
 * Created by PhpStorm.
 * User: dmaji
 * Date: 11/5/2017
 * Time: 11:43 AM
 */
namespace Evil\Custom\Plugin\Product\Form\Modifier\CustomOptions;


use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Evil\Custom\Controller\Catalog\Adminhtml\Product\Image;
use Evil\Custom\Controller\Catalog\Adminhtml\Product\Media;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Framework\Locale\CurrencyInterface;

class ModifyMeta
{
    const GROUP_CUSTOM_OPTIONS_NAME = 'custom_options';
    const GROUP_CUSTOM_OPTIONS_SCOPE = 'data.product';
    const GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME = 'search-engine-optimization';
    const GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER = 31;
    /**#@-*/

    /**#@+
     * Button values
     */
    const BUTTON_ADD = 'button_add';
    const BUTTON_IMPORT = 'button_import';
    /**#@-*/

    /**#@+
     * Container values
     */
    const CONTAINER_HEADER_NAME = 'container_header';
    const CONTAINER_OPTION = 'container_option';
    const CONTAINER_COMMON_NAME = 'container_common';
    const CONTAINER_TYPE_STATIC_NAME = 'container_type_static';
    /**#@-*/

    /**#@+
     * Grid values
     */
    const GRID_OPTIONS_NAME = 'options';
    const GRID_TYPE_SELECT_NAME = 'values';
    /**#@-*/

    /**#@+
     * Field values
     */
    const FIELD_ENABLE = 'affect_product_custom_options';
    const FIELD_OPTION_ID = 'option_id';
    const FIELD_TITLE_NAME = 'title';
    const FIELD_TYPE_NAME = 'type';
    const FIELD_IS_REQUIRE_NAME = 'is_require';
    const FIELD_SORT_ORDER_NAME = 'sort_order';
    const FIELD_PRICE_NAME = 'price';
    const FIELD_PRICE_TYPE_NAME = 'price_type';
    const FIELD_SKU_NAME = 'sku';
    const FIELD_MAX_CHARACTERS_NAME = 'max_characters';
    const FIELD_FILE_EXTENSION_NAME = 'file_extension';
    const FIELD_IMAGE_SIZE_X_NAME = 'image_size_x';
    const FIELD_IMAGE_SIZE_Y_NAME = 'image_size_y';
    const FIELD_IS_DELETE = 'is_delete';
    /**#@-*/

    /**#@+
     * Import options values
     */
    const IMPORT_OPTIONS_MODAL = 'import_options_modal';
    const CUSTOM_OPTIONS_LISTING = 'product_custom_options_listing';
    const FIELD_HEADLINE_NAME    = 'headline';
    const FIELD_DESCRIPTION_NAME = 'description';
    const FIELD_PROMO_NAME       = 'promo';
    const FIELD_PARENTABLE_NAME  = 'parentable';
    const FIELD_IMAGE1           = 'image1';
    const FIELD_IMAGE2           = 'image2';
    const FIELD_SWATCH           = 'swatch';
    const FIELD_ICON             = 'icon';
    const FIELD_ICON_HIDDEN      = 'icon_hidden';
    const FIELD_SWITCH_NAME      = 'switch';
    const FIELD_SWATCH_HIDDEN    = 'swatch_hidden';
    const FIELD_IMAGE1_HIDDEN    = 'image1_hidden';
    const FIELD_IMAGE2_HIDDEN    = 'image2_hidden';

    protected $productOptionsConfig;
    protected $locator;
    private $_objectManager;
    protected $storeManager;
    protected $productOptionsPrice;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionsConfig,
        \Magento\Catalog\Model\Locator\LocatorInterface $locator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductOptionsPrice $productOptionsPrice
    ) {
        $this->locator = $locator;
        $this->_objectManager = $objectmanager;
        $this->productOptionsConfig = $productOptionsConfig;
        $this->storeManager = $storeManager;
        $this->productOptionsPrice = $productOptionsPrice;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->createCustomOptionsPanel();

        return $this->meta;
    }

    public function aftermodifyData($subject, $result)
    {
        if(is_array($result) && count($result) > 0)
        {
            foreach ($result as $index_1 => $product)
            {
                if (isset($product['product']['options']) && count($product['product']['options']) > 0)
                {
                    $options = $product['product']['options'];

                    foreach ($options as $index_2 => $option)
                    {
                        $result[$index_1]['product']['options'][$index_2] = $this->getSavedCustomOptionByOptionId($option);
                        //highlight_string("<?php\n\$data =\n" . var_export($result[$index_1]['product']['options'][$index_2],true) . ';\n?\>'); die();
                    }
                }

            }
        }
        //highlight_string("<?php\n\$data =\n" . var_export($result[36]['product']['options'],true) . ';\n?\>'); die();

        return $result;
    }

    private function getSavedCustomOptionByOptionId(array $options)
    {
        $eviloptions      = $this->_objectManager->create('Evil\Custom\Model\EvilCustomOptions');
        $eviloptionvalues = $this->_objectManager->create('Evil\Custom\Model\EvilCustomOptionValues');

        if(isset($options['option_id']) && !empty($options['option_id']))
        {
            $eviloptions->load($options['option_id'], 'option_id');

            if (count($eviloptions->getData()) > 0)
            {
                $current = $eviloptions->getData();
                $options[static::FIELD_HEADLINE_NAME]    = $current[static::FIELD_HEADLINE_NAME];
                $options[static::FIELD_DESCRIPTION_NAME] = $current[static::FIELD_DESCRIPTION_NAME];
                $options[static::FIELD_PROMO_NAME]       = $current[static::FIELD_PROMO_NAME];
                $options[static::FIELD_ICON]             = $current[static::FIELD_ICON];
                $options[static::FIELD_ICON_HIDDEN]      = $current[static::FIELD_ICON];
                $options[static::FIELD_SWITCH_NAME]      = $current[static::FIELD_SWITCH_NAME];
                $options[static::FIELD_PARENTABLE_NAME]  = $current[static::FIELD_PARENTABLE_NAME];
            }

            if(is_array($options['values']) && count($options['values']) > 0)
            {
                foreach ($options['values'] as $index=>$opt)
                {
                    $eviloptionvalues->load($opt['option_type_id'], 'option_type_id');

                    if (count($eviloptionvalues->getData()) > 0)
                    {
                        $current_values = $eviloptionvalues->getData();
                        $options['values'][$index][static::FIELD_IMAGE1] = $current_values[static::FIELD_IMAGE1];
                        $options['values'][$index][static::FIELD_IMAGE2] = $current_values[static::FIELD_IMAGE2];
                        $options['values'][$index][static::FIELD_SWATCH] = $current_values[static::FIELD_SWATCH];
                        $options['values'][$index][static::FIELD_SWATCH_HIDDEN] = $current_values[static::FIELD_SWATCH];
                        $options['values'][$index][static::FIELD_IMAGE1_HIDDEN] = $current_values[static::FIELD_IMAGE1];
                        $options['values'][$index][static::FIELD_IMAGE2_HIDDEN] = $current_values[static::FIELD_IMAGE2];

                    }
                }
            }
        }

        return $options;
    }

    public function afterModifyMeta($subject, $result)
    {
        /**
         * Overwrite Custom Options      [Main]
         * Overwrite Custom Option Types [Sub]
         */
        try
        {
            $result['custom_options']['children']['options']['children']['record']['children']['container_option']['children']['container_common'] = $this->getNewOptions(10);
            $result['custom_options']['children']['options']['children']['record']['children']['container_option']['children']['values']           = $this->getNewOptionTypes(11);
        }
        catch (\Exception $e)
        {
            echo $e->getMessage(); die();
        }
        //highlight_string("<?php\n\$data =\n" . var_export($result,true) . ';\n?\>'); die();
        return $result;
    }

    protected function getNewOptions($sortOrder)
    {
        $commonContainer = [
            'arguments' => [
                'data'  => [
                    'config' => [
                        'componentType'     => Container::NAME,
                        'formElement'       => Container::NAME,
                        'component'         => 'Magento_Ui/js/form/components/group',
                        'breakLine'         => false,
                        'showLabel'         => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder'         => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_OPTION_ID  => $this->getOptionIdFieldConfig(10),
                static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(
                    20,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Option Title'),
                                    'component' => 'Magento_Catalog/component/static-type-input',
                                    'valueUpdate' => 'input',
                                    'imports' => [
                                        'optionId' => '${ $.provider }:${ $.parentScope }.option_id'
                                    ]
                                ],
                            ],
                        ],
                    ]
                ),
                static::FIELD_HEADLINE_NAME    => $this->getHeadlineFieldConfig(24),
                static::FIELD_DESCRIPTION_NAME => $this->getDescriptionFieldConfig(25),
                static::FIELD_PROMO_NAME       => $this->getPromoFieldConfig(26),
                static::FIELD_TYPE_NAME        => $this->getTypeFieldConfig(27),
                static::FIELD_SWITCH_NAME      => $this->getSwitchFieldConfig(29),
                static::FIELD_ICON_HIDDEN      => $this->getHiddenIconFieldConfig(30),
                static::FIELD_ICON             => $this->getUploadIconFieldConfig(31),
                static::FIELD_PARENTABLE_NAME  => $this->getParentableFieldConfig(32),
                static::FIELD_IS_REQUIRE_NAME  => $this->getIsRequireFieldConfig(33)
            ]
        ];

        if ($this->locator->getProduct()->getStoreId()) {
            $useDefaultConfig = [
                'service' => [
                    'template' => 'Magento_Catalog/form/element/helper/custom-option-service',
                ]
            ];
            $titlePath = $this->arrayManager->findPath(static::FIELD_TITLE_NAME, $commonContainer, null)
                . static::META_CONFIG_PATH;
            $commonContainer = $this->arrayManager->merge($titlePath, $commonContainer, $useDefaultConfig);
        }

        return $commonContainer;
    }

    protected function getNewOptionTypes($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel'      => __('Add Value'),
                        'componentType'       => DynamicRows::NAME,
                        'component'           => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses'   => 'admin__field-wide',
                        'deleteProperty'      => static::FIELD_IS_DELETE,
                        'deleteValue'         => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder'           => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType'    => Container::NAME,
                                'component'        => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate'       => true,
                                'is_collection'    => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_TITLE_NAME       => $this->getTitleFieldConfig(10),
                        static::FIELD_PRICE_NAME       => $this->getPriceFieldConfig(20),
                        static::FIELD_PRICE_TYPE_NAME  => $this->getPriceTypeFieldConfig(30, ['fit' => true]),
                        static::FIELD_SKU_NAME         => $this->getSkuFieldConfig(40),
                        static::FIELD_SORT_ORDER_NAME  => $this->getPositionFieldConfig(50),
                        static::FIELD_SWATCH_HIDDEN    => $this->getHiddenSwatchFieldConfig(52),
                        static::FIELD_SWATCH           => $this->getUploadSwatchFieldConfig(53),
                        static::FIELD_IMAGE1_HIDDEN    => $this->getHiddenImage1FieldConfig(54),
                        static::FIELD_IMAGE1           => $this->getUpload1FieldConfig(55),
                        static::FIELD_IMAGE2_HIDDEN    => $this->getHiddenImage2FieldConfig(56),
                        static::FIELD_IMAGE2           => $this->getUpload2FieldConfig(57),
                        static::FIELD_IS_DELETE        => $this->getIsDeleteFieldConfig(60)
                    ]
                ]
            ]
        ];
    }

    protected function getParentableFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Child Of'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => static::FIELD_PARENTABLE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'value' => '0',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0'
                        ],
                    ],
                ],
            ],
        ];
    }
    protected function getHeadlineFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Headline'),
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => static::FIELD_HEADLINE_NAME,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder
                    ],
                ],
            ],
        ];
    }
    protected function getDescriptionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Description'),
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => static::FIELD_DESCRIPTION_NAME,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder
                    ],
                ],
            ],
        ];
    }
    protected function getSwitchFieldConfig($sortOrder)
    {
      return [
          'arguments' => [
              'data' => [
                  'config' => [
                      'label' => __('View'),
                      'componentType' => Field::NAME,
                      'formElement'   => Select::NAME,
                      'dataType'      => Text::NAME,
                      'dataScope'     => static::FIELD_SWITCH_NAME,
                      'options'       => $this->getSwitchOptions(),
                      'sortOrder'     => $sortOrder,
                  ],
              ],
          ],
      ];
    }

    protected function getHiddenIconFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses'  => 'preview-icon',
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => static::FIELD_ICON_HIDDEN,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder
                    ],
                ],
            ],
        ];
    }
    protected function getHiddenSwatchFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses'  => 'preview-icon',
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => static::FIELD_SWATCH_HIDDEN,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder
                    ],
                ],
            ],
        ];
    }
    protected function getHiddenImage1FieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses'  => 'preview-icon',
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => static::FIELD_IMAGE1_HIDDEN,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder
                    ],
                ],
            ],
        ];
    }
    protected function getHiddenImage2FieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses'  => 'preview-icon',
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => static::FIELD_IMAGE2_HIDDEN,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder
                    ],
                ],
            ],
        ];
    }
    protected function getPromoFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Promo'),
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => static::FIELD_PROMO_NAME,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder
                    ],
                ],
            ],
        ];
    }

    protected function getUploadSwatchFieldConfig($sortOrder, array $config = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Swatch'),
                            'additionalClasses'  => 'awesome-upload',
                            'componentType' => Field::NAME,
                            'formElement'   => Image::NAME,
                            'dataType'      => Image::NAME,
                            'sortOrder'     => $sortOrder,
                            'dataScope'     => static::FIELD_SWATCH,
                        ],
                    ],
                ],
            ],
            $config
        );
    }

    protected function getUpload1FieldConfig($sortOrder, array $config = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Image Front'),
                            'additionalClasses'  => 'awesome-upload',
                            'componentType' => Field::NAME,
                            'formElement'   => Image::NAME,
                            'dataType'      => Image::NAME,
                            'sortOrder'     => $sortOrder,
                            'dataScope'     => static::FIELD_IMAGE1,
                        ],
                    ],
                ],
            ],
            $config
        );
    }

    protected function getUpload2FieldConfig($sortOrder, array $config = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Image Back'),
                            'additionalClasses'  => 'awesome-upload',
                            'componentType' => Field::NAME,
                            'formElement'   => Image::NAME,
                            'dataType'      => Image::NAME,
                            'sortOrder'     => $sortOrder,
                            'dataScope'     => static::FIELD_IMAGE2,
                        ],
                    ],
                ],
            ],
            $config
        );
    }

    protected function getUploadIconFieldConfig($sortOrder, array $config = [])
    {

        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Icon'),
                            'additionalClasses'  => 'awesome-upload',
                            'componentType' => Field::NAME,
                            'formElement'   => Image::NAME,
                            'dataType'      => Image::NAME,
                            'sortOrder'     => $sortOrder,
                            'dataScope'     => static::FIELD_ICON
                        ],
                    ],
                ],
            ],
            $config
        );
    }

    protected function getOptionIdFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_OPTION_ID,
                        'sortOrder' => $sortOrder,
                        'visible' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getTitleFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Title'),
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataScope' => static::FIELD_TITLE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'validation' => [
                                'required-entry' => true
                            ],
                        ],
                    ],
                ],
            ],
            $options
        );
    }
    protected function getTypeFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Option Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'component' => 'Magento_Catalog/js/custom-options-type',
                        'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                        'selectType' => 'optgroup',
                        'dataScope' => static::FIELD_TYPE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'options' => $this->getProductOptionTypes(),
                        'disableLabel' => true,
                        'multiple' => false,
                        'selectedPlaceholders' => [
                            'defaultPlaceholder' => __('-- Please select --'),
                        ],
                        'validation' => [
                            'required-entry' => true
                        ],
                        'groupsConfig' => [
                            'text' => [
                                'values' => ['field', 'area'],
                                'indexes' => [
                                    static::CONTAINER_TYPE_STATIC_NAME,
                                    static::FIELD_PRICE_NAME,
                                    static::FIELD_PRICE_TYPE_NAME,
                                    static::FIELD_SKU_NAME,
                                    static::FIELD_MAX_CHARACTERS_NAME
                                ]
                            ],
                            'file' => [
                                'values' => ['file'],
                                'indexes' => [
                                    static::CONTAINER_TYPE_STATIC_NAME,
                                    static::FIELD_PRICE_NAME,
                                    static::FIELD_PRICE_TYPE_NAME,
                                    static::FIELD_SKU_NAME,
                                    static::FIELD_FILE_EXTENSION_NAME,
                                    static::FIELD_IMAGE_SIZE_X_NAME,
                                    static::FIELD_IMAGE_SIZE_Y_NAME
                                ]
                            ],
                            'select' => [
                                'values' => ['drop_down', 'radio', 'checkbox', 'multiple'],
                                'indexes' => [
                                    static::GRID_TYPE_SELECT_NAME
                                ]
                            ],
                            'data' => [
                                'values' => ['date', 'date_time', 'time'],
                                'indexes' => [
                                    static::CONTAINER_TYPE_STATIC_NAME,
                                    static::FIELD_PRICE_NAME,
                                    static::FIELD_PRICE_TYPE_NAME,
                                    static::FIELD_SKU_NAME
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }
    protected function getProductOptionTypes()
    {
        $options = [];
        $groupIndex = 0;

        foreach ($this->productOptionsConfig->getAll() as $option) {
            $group = [
                'value' => $groupIndex,
                //TODO: Wrap label with __() or remove this TODO after MAGETWO-49771 is closed
                'label' => $option['label'],
                'optgroup' => []
            ];

            foreach ($option['types'] as $type) {
                if ($type['disabled']) {
                    continue;
                }

                //TODO: Wrap label with __() or remove this TODO after MAGETWO-49771 is closed
                $group['optgroup'][] = ['label' => $type['label'], 'value' => $type['name']];
            }

            if (count($group['optgroup'])) {
                $options[] = $group;
                $groupIndex += 1;
            }
        }

        return $options;
    }

    protected function getIsRequireFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Required'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => static::FIELD_IS_REQUIRE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'value' => '1',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0'
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getPriceFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Price'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_NAME,
                        'dataType' => Number::NAME,
                        'addbefore' => $this->getCurrencySymbol(),
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'validate-zero-or-greater' => false
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }

    protected function getPriceTypeFieldConfig($sortOrder, array $config = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Price Type'),
                            'componentType' => Field::NAME,
                            'formElement' => Select::NAME,
                            'dataScope' => static::FIELD_PRICE_TYPE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'options' => $this->productOptionsPrice->toOptionArray(),
                        ],
                    ],
                ],
            ],
            $config
        );
    }

    protected function getSkuFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('SKU'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SKU_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    protected function getPositionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    protected function getIsDeleteFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'fit' => true,
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }
    public function getSwitchOptions()
    {
        $options =
            [
                0 => [
                    'value' => 'front',
                    'label' => 'Front'
                ],
                1 => [
                    'value' => 'back',
                    'label' => 'Back'
                ]
            ];
        return $options;
    }
}
