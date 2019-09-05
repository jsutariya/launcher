<?php

namespace JS\Launcher\Block\Adminhtml;

use Magento\Backend\Block\Menu;
use Magento\Backend\Block\AnchorRenderer;
use Magento\Backend\Model\Menu\Config as MenuConfig;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\Json;

class JsNav extends \Magento\Backend\Block\Template
{
    /**
     * @var Builder
     */
    private $menuBuilder;
    /**
     * @var AnchorRenderer
     */
    private $anchorRenderer;
    /**
     * @var MenuConfig
     */
    private $menuConfig;
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $url;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var \Magento\Backend\Model\Menu\Filter\IteratorFactory
     */
    private $iteratorFactory;
    /**
     * @var Json
     */
    private $jsonSerializer;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var \Magento\Config\Block\System\Config\Tabs
     */
    private $configTabs;

    /**
     * ActionLayoutRenderBefore constructor.
     * @param Menu $menuBuilder
     * @param AnchorRenderer $anchorRenderer
     * @param MenuConfig $menuConfig
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param Escaper $escaper
     * @param \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory
     * @param \Magento\Framework\App\Route\ConfigInterface|null $routeConfig
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Menu $menuBuilder,
        AnchorRenderer $anchorRenderer,
        MenuConfig $menuConfig,
        \Magento\Backend\Model\UrlInterface $url,
        Escaper $escaper,
        \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Config\Model\Config\Structure $configTabs,
        \Magento\Framework\App\Route\ConfigInterface $routeConfig = null, array $data = []
    ) {
        parent::__construct($context, $data);
        $this->menuBuilder = $menuBuilder;
        $this->anchorRenderer = $anchorRenderer;
        $this->menuConfig = $menuConfig;
        $this->routeConfig = $routeConfig ?:
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\App\Route\ConfigInterface::class);
        $this->url = $url;
        $this->escaper = $escaper;
        $this->iteratorFactory = $iteratorFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->configTabs = $configTabs;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function getJson()
    {
        $menuItems = $this->renderMenu($this->menuConfig->getMenu(), 0);
        $configItems = $this->renderConfig();
        $allItems = array_merge($menuItems, $configItems);
        $menuItemsJson = $this->jsonSerializer->serialize($allItems);
        return $menuItemsJson;
    }

    /**
     * Replace Callback Secret Key
     *
     * @param string[] $match
     * @return string
     */
    protected function _callbackSecretKey($match)
    {
        $routeId = $this->routeConfig->getRouteByFrontName($match[1]);
        return \Magento\Backend\Model\UrlInterface::SECRET_KEY_PARAM_NAME . '/' . $this->url->getSecretKey(
                $routeId ?: $match[1],
                $match[2],
                $match[3]
            );
    }

    public function renderMenu($menu, $level = 0)
    {
        $parentArray = [];
        /** @var $menuItem \Magento\Backend\Model\Menu\Item */
        foreach ($this->_getMenuIterator($menu) as $menuItem) {
            $menuArray = [];
            $menuArray['label'] = $this->escaper->escapeHtml(__($menuItem->getTitle()));
            $menuArray['url'] = preg_replace_callback(
                '#' . \Magento\Backend\Model\UrlInterface::SECRET_KEY_PARAM_NAME . '/\$([^\/].*)/([^\/].*)/([^\$].*)\$#U',
                [$this, '_callbackSecretKey'],
                $menuItem->getUrl()
            );
            if ($menuItem->hasChildren()) {
                $menuArray['children'] = $this->renderMenu($menuItem->getChildren(), $level + 1);
            }
            $parentArray[$menuItem->getTitle()] = $menuArray;
        }
        return $parentArray;
    }

    public function renderConfig()
    {
        $parentArray = [];
        foreach ($this->configTabs->getTabs() as $tab) {
            $menuArray = [];
            $sections = $tab->getChildren();
            foreach($sections as $section)
            {
                $menuArray['label'] = 'System Configuration - ' . $section->getLabel();
                $code = $section->getId();
                $menuArray['url'] = $this->url->getUrl("adminhtml/system_config/edit/section/$code");
                $parentArray[$section->getId()] = $menuArray;
            }
        }
        return $parentArray;
    }

    protected function _getMenuIterator($menu)
    {
        return $this->iteratorFactory->create(['iterator' => $menu->getIterator()]);
    }

    public function getCombinedCodes()
    {
        $first = $this->_scopeConfig->getValue('js_launcher/options/shortcut_code_first');
        $second  = $this->_scopeConfig->getValue('js_launcher/options/shortcut_code_second');

        $code = '17_77';        //default to ctrl-m
        if(is_numeric($first) && is_numeric($second))
        {
            $code = $first . '_' . $second;
        }

        return $code;
    }

    public function getSearchUrl()
    {
        return $this->url->getUrl("adminhtml/index/globalSearch");
    }
}


