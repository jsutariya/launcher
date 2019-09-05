<?php

namespace JS\Launcher\Block\Adminhtml\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;

class KeyCode extends Field
{
	/**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, array $data = []
    ) 
    {
        parent::__construct($context, $data);
    }

    /**
     * add color picker in admin configuration fields
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string script
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script type="text/javascript">
            require(["jquery"], function ($) {
                $(document).ready(function () {
                    $("#js_launcher_options_shortcut_code_first, #js_launcher_options_shortcut_code_second").keydown(function(event){
						if(event.which !== 8 && event.which !== 46 && event.which !== 9) {
							event.preventDefault();
							$(this).val(event.which);
						}
					});
                });
            });
            </script>';

        return $html;
    }
}