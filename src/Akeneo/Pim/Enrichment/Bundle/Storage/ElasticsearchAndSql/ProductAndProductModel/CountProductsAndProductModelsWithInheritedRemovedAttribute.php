<?php

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsAndProductModelsWithInheritedRemovedAttributeInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class CountProductsAndProductModelsWithInheritedRemovedAttribute implements CountProductsAndProductModelsWithInheritedRemovedAttributeInterface
{
    private SearchQueryBuilder $searchQueryBuilder;

    public function __construct(
        private Client $elasticsearchClient
    ) {
        $this->searchQueryBuilder = new SearchQueryBuilder();
    }

    public function count(array $attributesCodes): int
    {
        $this->searchQueryBuilder->addFilter([
            'terms' => [
                'document_type' => [
                    ProductInterface::class,
                    ProductModelInterface::class,
                ],
            ],
        ]);
        $this->searchQueryBuilder->addFilter([
            'exists' => [
                'field' => 'parent',
            ],
        ]);

        $this->searchQueryBuilder->addMustNot([
            'terms' => [
                'attributes_for_this_level' => $attributesCodes,
            ],
        ]);

        foreach ($attributesCodes as $attributeCode) {
            $this->searchQueryBuilder->addShould([
                'exists' => ['field' => sprintf('values.%s-*', $attributeCode)],
            ]);
        }

        $body = $this->searchQueryBuilder->getQuery();
        \unset($body['_source']);
        \unset($body['sort']);

        $result = $this->elasticsearchClient->count($body);

        // Reset query
        $this->searchQueryBuilder = new SearchQueryBuilder();

        return (int)$result['count'];
    }

    public function getQueryBuilder(): SearchQueryBuilder
    {
        return $this->searchQueryBuilder;
    }
}
