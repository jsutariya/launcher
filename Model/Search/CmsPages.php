<?php

namespace JS\Launcher\Model\Search;

use Magento\Backend\Helper\Data;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;

class CmsPages extends DataObject
{
    private const CMS_PAGE_EDIT_LINK = 'cms/page/edit';
    /**
     * @var Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

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
     * @param PageRepositoryInterface $pageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        Data $adminhtmlData,
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Get matching CMS pages.
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
        $searchFields = ['title', 'content_heading'];
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
        $searchResults = $this->pageRepository->getList($searchCriteria);

        foreach ($searchResults->getItems() as $page) {
            $result[] = [
                'id' => 'page/1/' . $page->getId(),
                'type' => __('Page'),
                'name' => $page->getTitle(),
                'description' => $page->getTitle(),
                'url' => $this->_adminhtmlData->getUrl(self::CMS_PAGE_EDIT_LINK, ['page_id' => $page->getId()]),
            ];
        }
        $this->setResults($result);
        return $this;
    }
}
