<?php

namespace JS\Launcher\Block\Adminhtml;

use Exception;
use Magento\Backend\Block\AnchorRenderer;
use Magento\Backend\Block\Menu;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Menu\Config as MenuConfig;
use Magento\Backend\Model\Menu\Filter\Iterator;
use Magento\Backend\Model\Menu\Filter\IteratorFactory;
use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\UrlInterface;
use Magento\Config\Block\System\Config\Tabs;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\Json;

class JsNav extends Template
{
    private const JS_LAUNCHER_OPTIONS_SHORTCUT_CODE_FIRST = 'js_launcher/options/shortcut_code_first';
    private const JS_LAUNCHER_OPTIONS_SHORTCUT_CODE_SECOND = 'js_launcher/options/shortcut_code_second';
    private const ADMINHTML_SYSTEM_CONFIG_EDIT_SECTION_PATH = "adminhtml/system_config/edit/section/";
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
     * @var UrlInterface
     */
    private $url;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var IteratorFactory
     */
    private $iteratorFactory;
    /**
     * @var Json
     */
    private $jsonSerializer;
    /**
     * @var Tabs
     */
    private $configTabs;

    /**
     * JsNav constructor.
     * @param Context $context
     * @param Menu $menuBuilder
     * @param AnchorRenderer $anchorRenderer
     * @param MenuConfig $menuConfig
     * @param UrlInterface $url
     * @param Escaper $escaper
     * @param IteratorFactory $iteratorFactory
     * @param Json $jsonSerializer
     * @param Structure $configTabs
     * @param ConfigInterface|null $routeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Menu $menuBuilder,
        AnchorRenderer $anchorRenderer,
        MenuConfig $menuConfig,
        UrlInterface $url,
        Escaper $escaper,
        IteratorFactory $iteratorFactory,
        Json $jsonSerializer,
        Structure $configTabs,
        ConfigInterface $routeConfig = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->menuBuilder = $menuBuilder;
        $this->anchorRenderer = $anchorRenderer;
        $this->menuConfig = $menuConfig;
        $this->routeConfig = $routeConfig ?:
            ObjectManager::getInstance()
                ->get(ConfigInterface::class);
        $this->url = $url;
        $this->escaper = $escaper;
        $this->iteratorFactory = $iteratorFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->configTabs = $configTabs;
    }

    /**
     * Get all menu options and Configuration tab, group and field data and return in a json format
     *
     * @return bool|false|string
     * @throws Exception
     */
    public function getJson()
    {
        $menuItems = $this->renderMenu($this->menuConfig->getMenu(), 0);
        $configItems = $this->renderConfig();
        $allItems = array_merge($menuItems, $configItems);
        return $this->jsonSerializer->serialize($allItems);
    }

    /**
     * Get all available menu nodes.
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @param int $level
     * @return array
     */
    public function renderMenu($menu, $level = 0)
    {
        $parentArray = [];
        /** @var $menuItem Item */
        foreach ($this->_getMenuIterator($menu) as $menuItem) {
            $menuArray = [];
            $menuArray['label'] = $this->escaper->escapeHtml(__($menuItem->getTitle()));
            $menuArray['url'] = preg_replace_callback(
                '#' . UrlInterface::SECRET_KEY_PARAM_NAME . '/\$([^\/].*)/([^\/].*)/([^\$].*)\$#U',
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

    /**
     * Get menu iterator.
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @return Iterator
     */
    protected function _getMenuIterator($menu)
    {
        return $this->iteratorFactory->create(['iterator' => $menu->getIterator()]);
    }

    /**
     * Get all configuration nodes.
     *
     * @return array
     */
    public function renderConfig()
    {
        $parentArray = [];
        foreach ($this->configTabs->getTabs() as $tab) {
            $menuArray = [];
            $sections = $tab->getChildren();
            foreach ($sections as $section) {
                $menuArray['label'] = __('System Configuration') . ' - ' . $section->getLabel();
                $code = $section->getId();
                $menuArray['url'] = $this->url->getUrl(self::ADMINHTML_SYSTEM_CONFIG_EDIT_SECTION_PATH . $code);
                $parentArray[$section->getId()] = $menuArray;
            }
        }
        return $parentArray;
    }

    /**
     * Get key code combination to initiate launcher.
     *
     * @return string
     */
    public function getCombinedCodes()
    {
        $first = $this->_scopeConfig->getValue(self::JS_LAUNCHER_OPTIONS_SHORTCUT_CODE_FIRST);
        $second = $this->_scopeConfig->getValue(self::JS_LAUNCHER_OPTIONS_SHORTCUT_CODE_SECOND);

        $code = '17_77';        //default to ctrl-m
        if (is_numeric($first) && is_numeric($second)) {
            $code = $first . '_' . $second;
        }

        return $code;
    }

    /**
     * Get global search URL path.
     *
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->url->getUrl("adminhtml/index/globalSearch");
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
        return UrlInterface::SECRET_KEY_PARAM_NAME . '/' . $this->url->getSecretKey(
            $routeId ?: $match[1],
            $match[2],
            $match[3]
        );
    }
}
