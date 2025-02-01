<?php

namespace JS\Launcher\Block\Adminhtml\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class KeyCode extends Field
{
    /**
     * Add Key code field. This field will fetch the key code of the pressed key.
     * We are escaping key code 8 (Backspace), 9 (Tab) and 46 (Delete)
     *
     * @param AbstractElement $element
     * @return string script
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script type="text/javascript">
            require(["jquery"], function ($) {
                $(document).ready(function () {
                    $("#js_launcher_options_shortcut_code_first, #js_launcher_options_shortcut_code_second").keydown(
                        function(event){
                            if(event.which !== 8 && event.which !== 46 && event.which !== 9) {
                                event.preventDefault();
                                $(this).val(event.which);
                            }
                        }
					);
                });
            });
            </script>';

        return $html;
    }
}
