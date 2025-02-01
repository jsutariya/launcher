<?php

namespace JS\Launcher\Model\Search;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;

class Category extends DataObject
{
    private const CATALOG_CATEGORY_EDIT_URL = 'catalog/category/edit';
    /**
     * @var Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var CategoryListInterface
     */
    protected $categoryRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Initialize dependencies.
     *
     * @param Data $adminhtmlData
     * @param CategoryListInterface $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        Data $adminhtmlData,
        CategoryListInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Get matching categories.
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }

        $this->searchCriteriaBuilder->setCurrentPage($this->getStart());
        $this->searchCriteriaBuilder->setPageSize($this->getLimit());
        $searchFields = ['name'];
        $filters = [];
        foreach ($searchFields as $field) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue($this->getQuery() . '%')
                ->create();
        }
        $this->searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->categoryRepository->getList($searchCriteria);

        foreach ($searchResults->getItems() as $category) {
            $result[] = [
                'id' => 'category/1/' . $category->getId(),
                'type' => __('Category'),
                'name' => __('Category') . ' - ' . $category->getName(),
                'description' => $category->getName(),
                'url' => $this->_adminhtmlData->getUrl(self::CATALOG_CATEGORY_EDIT_URL, ['id' => $category->getId()]),
            ];
        }
        $this->setResults($result);
        return $this;
    }
}
