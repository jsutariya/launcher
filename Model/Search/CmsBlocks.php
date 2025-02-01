<?php

namespace JS\Launcher\Model\Search;

use Magento\Backend\Helper\Data;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;

class CmsBlocks extends DataObject
{
    private const CMS_BLOCK_EDIT_LINK = 'cms/block/edit';
    /**
     * @var Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

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
     * @param BlockRepositoryInterface $blockRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        Data $adminhtmlData,
        BlockRepositoryInterface $blockRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->blockRepository = $blockRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Get matching CMS blocks.
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
        $searchFields = ['title', 'identifier'];
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
        $searchResults = $this->blockRepository->getList($searchCriteria);

        foreach ($searchResults->getItems() as $block) {
            $result[] = [
                'id' => 'block/1/' . $block->getId(),
                'type' => __('Block'),
                'name' => $block->getTitle(),
                'description' => $block->getTitle(),
                'url' => $this->_adminhtmlData->getUrl(self::CMS_BLOCK_EDIT_LINK, ['block_id' => $block->getId()]),
            ];
        }
        $this->setResults($result);
        return $this;
    }
}
