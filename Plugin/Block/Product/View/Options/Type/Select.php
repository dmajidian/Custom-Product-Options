<?php
/**
 * Created by PhpStorm.
 * User: WEB_Freelance 2017-4
 * Date: 11/2/2017
 * Time: 4:12 PM
 */
namespace Evil\Custom\Plugin\Block\Product\View\Options\Type;

use Evil\Custom\Controller\Catalog\Adminhtml\Product\Save as Label;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Catalog\Block\Product\View\Options\Type;

class Select
{
    private $customOptions;
    private $customOptionValues;
    private $mediaUrl;
    protected $pricingHelper;
    private $_objectManager;
    private $step;
    private $count;

    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\ObjectManagerInterface $objectmanager)
    {
        $this->pricingHelper = $pricingHelper;
        $this->_objectManager = $objectmanager;
        $this->step = 0;
    }

    public function beforeGetValuesHtml()
    {
        $this->step++;

        return null;
    }

    public function aroundGetValuesHtml(\Magento\Catalog\Block\Product\View\Options\Type\Select $subject, callable $proceed)
    {
        $_option = $subject->getOption();
        /**
         * Add Data to Current Block
         * Usage <?php var_dump($block->getData('customOptions'));?>
         */
        $this->getCustomOptions($_option->getId());
        $subject->getProduct()->addData(['current_option'=> $_option->getId()]);
        $subject->getProduct()->addData(['current_step' => $this->step]);

        return $this->getValuesHtml($subject, $_option);
    }

    private function getCustomOptionsArray()
    {
        return $this->toArray();
    }

    private function setCustomOptionValues(array $options)
    {
        $this->customOptionValues = $options;
    }

    private function setCustomOptions(array $options)
    {
        $this->customOptions = $options;
    }

    public function toArray()
    {
        $array = array();
        $data = $this->customOptions;
        foreach ($data as $key => $value) {
            if ($value instanceof Zend_Config) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    private function getValuesHtml($subject, $_option)
    {
        $configValue = $subject->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $subject->getProduct()->getStore();

        $this->mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );

        $subject->setSkipJsReloadPrice(1);
        // Remove inline prototype onclick and onchange events

        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE
        ) {
            $require = $_option->getIsRequire() ? ' required' : '';
            $extraParams = '';
            $select = $subject->getLayout()->createBlock(
                'Magento\Framework\View\Element\Html\Select'
            )->setData(
                [
                    'id' => 'select_' . $_option->getId(),
                    'class' => $require . ' product-custom-option admin__control-select'
                ]
            );
            if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options[' . $_option->getid() . ']')->addOption('', __('-- Please Select --'));
            } else {
                $select->setName('options[' . $_option->getid() . '][]');
                $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
            }
            foreach ($_option->getValues() as $_value) {
                $priceStr = $subject->_formatPrice(
                    [
                        'is_percent' => $_value->getPriceType() == 'percent',
                        'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                    ],
                    false
                );
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                    ['price' => $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false)]
                );
            }
            if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            if (!$subject->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            }
            $extraParams .= ' data-selector="' . $select->getName() . '"';
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }

            return $select->getHtml();
        }

        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX
        ) {
            $selectHtml = '<div class="options-list nested" id="options-' . $_option->getId() . '-list">';
            $require = $_option->getIsRequire() ? ' required' : '';
            $arraySign = '';
            switch ($_option->getType()) {
                case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO:
                    $type = 'radio';
                    $class = 'radio admin__control-radio';
                    if (!$_option->getIsRequire()) {
                        $selectHtml .= '<div class="field choice admin__field admin__field-option">' .
                            '<input type="radio" id="options_' .
                            $_option->getId() .
                            '" class="' .
                            $class .
                            ' product-custom-option" name="options[' .
                            $_option->getId() .
                            ']"' .
                            ' data-selector="options[' . $_option->getId() . ']"' .
                            ($subject->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                            ' value="" checked="checked" /><label class="label admin__field-label" for="options_' .
                            $_option->getId() .
                            '"><span>' .
                            __('None') . '</span></label></div>';
                    }
                    break;
                case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $class = 'checkbox admin__control-checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;

            foreach ($_option->getValues() as $_value) {
                $count++;

                $priceStr = $this->_formatPrice($subject,
                    [
                        'is_percent' => $_value->getPriceType() == 'percent',
                        'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                    ]
                );

                $htmlValue = $_value->getOptionTypeId();

                // Call Custom Option Values Methods After this.
                $this->getCustomOptionValues($htmlValue);

                if ($arraySign) {
                    $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
                } else {
                    $checked = $configValue == $htmlValue ? 'checked' : '';
                }

                $dataSelector = 'options[' . $_option->getId() . ']';
                if ($arraySign) {
                    $dataSelector .= '[' . $htmlValue . ']';
                }

                $selectHtml .=  '<div class="field choice admin__field admin__field-option' .
                    (($this->hasSwatch()) ? ' has-swatch' : '') .
                    $require .
                    '">' .
                    '<input type="' .
                    $type .
                    '" class="' .
                    $class .
                    ' ' .
                    $require .
                    ' product-custom-option"' .
                    $this->getImage1() .
                    $this->getImage2() .
                    ($subject->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                    ' name="options[' .
                    $_option->getId() .
                    ']' .
                    $arraySign .
                    '" id="options_' .
                    $_option->getId() .
                    '_' .
                    $count .
                    '" value="' .
                    $htmlValue .
                    '" ' .
                    $checked .
                    ' data-selector="' . $dataSelector . '"' .
                    ' price="' .
                    $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false) .
                    '" />' .
                    '<label class="label admin__field-label" for="options_' .
                    $_option->getId() .
                    '_' .
                    $count .
                    '"><span>' .
                    $_value->getTitle() .
                    '</span> ' .
                    $priceStr .
                    '</label>'.
                    $this->getSwatch();
                $selectHtml .= '</div>';
            }
            $selectHtml .= '</div>';

            return $selectHtml;
        }
    }

    protected function _formatPrice($subject, $value, $flag = true)
    {
        if ($value['pricing_value'] == 0) {
            return '';
        }

        $sign = '+';
        if ($value['pricing_value'] < 0) {
            $sign = '-';
            $value['pricing_value'] = 0 - $value['pricing_value'];
        }

        $priceStr = $sign;

        $customOptionPrice = $subject->getProduct()->getPriceInfo()->getPrice('custom_option_price');
        $context = [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true];
        $optionAmount = $customOptionPrice->getCustomAmount($value['pricing_value'], null, $context);
        $priceStr .= $subject->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $optionAmount,
            $customOptionPrice,
            $subject->getProduct()
        );

        if ($flag) {
            $priceStr = '<span class="price-notice">' . $priceStr . '</span>';
        }

        return $priceStr;
    }
    private function getCustomOptionValues($option_type_id)
    {
        $model = $this->_objectManager->create('Evil\Custom\Model\EvilCustomOptionValues');
        $model->load($option_type_id, Label::CTM_OPTION_TYPE_ID);
        $data = [
            Label::CTM_OPTION_ID      => $model->getOrigData(Label::CTM_OPTION_ID),
            Label::CTM_OPTION_TYPE_ID => $model->getOrigData(Label::CTM_OPTION_TYPE_ID),
            Label::CTM_SWATCH         => $model->getOrigData(Label::CTM_SWATCH),
            Label::CTM_IMAGE1         => $model->getOrigData(Label::CTM_IMAGE1),
            Label::CTM_IMAGE2         => $model->getOrigData(Label::CTM_IMAGE2),
        ];

        return $this->setCustomOptionValues($data);
    }

    private function getCustomOptions($option_id)
    {
        $model = $this->_objectManager->create('Evil\Custom\Model\EvilCustomOptions');
        $model->load($option_id, Label::CTM_OPTION_ID);
        $data = [
            Label::CTM_OPTION_ID      => $model->getOrigData(Label::CTM_OPTION_ID),

            Label::CTM_ICON         => $this->mediaUrl . $model->getOrigData(Label::CTM_ICON),
            Label::CTM_DESCRIPTION  => $model->getOrigData(Label::CTM_DESCRIPTION),
            Label::CTM_PARENT       => $model->getOrigData(Label::CTM_PARENT),
            Label::CTM_PROMO        => $model->getOrigData(Label::CTM_PROMO),
            Label::CTM_SWITCH       => $model->getOrigData(Label::CTM_SWITCH),
        ];

        return $this->setCustomOptions($data);
    }

    private function hasSwatch()
    {
        return !empty($this->customOptionValues[Label::CTM_SWATCH]) ? true : false;
    }

    private function getSwatch()
    {
        return ($this->hasSwatch() === true) ? '<div class="swatch" style="background-image: url(' . $this->mediaUrl . $this->customOptionValues[Label::CTM_SWATCH] . ');"></div>' : '';
    }

    private function getImage1()
    {
        return !empty($this->customOptionValues[Label::CTM_IMAGE1]) ? ' data-front="' . $this->mediaUrl . $this->customOptionValues[Label::CTM_IMAGE1] . '"' : '';
    }

    private function getImage2()
    {
        return !empty($this->customOptionValues[Label::CTM_IMAGE2]) ? ' data-back="' . $this->mediaUrl . $this->customOptionValues[Label::CTM_IMAGE2] . '"' : '';
    }
    private function getIcon()
    {
        return !empty($this->customOptions[Label::CTM_ICON]) ? '<img class="img-responsive" src="' . $this->customOptions[Label::CTM_ICON] . '" />' : '';
    }
}
