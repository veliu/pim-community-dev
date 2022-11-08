<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryCollection;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategorySql implements GetCategoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function byId(int $categoryId): ?Category
    {
        $condition['sqlWhere'] = 'category.id = :category_id';
        $condition['params'] = ['category_id' => $categoryId];
        $condition['types'] = ['category_id' => \PDO::PARAM_INT];

        return $this->execute($condition);
    }

    public function byCode(string $categoryCode): ?Category
    {
        $condition['sqlWhere'] = 'category.code = :category_code';
        $condition['params'] = ['category_code' => $categoryCode];
        $condition['types'] = ['category_code' => \PDO::PARAM_STR];

        return $this->execute($condition);
    }

    public function byParentId(CategoryId $parentId): CategoryCollection
    {
        $sqlQuery = <<<SQL
            WITH translation as (
                SELECT category.id, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE category.parent_id = :parent_id
                GROUP BY category.id
            )
            SELECT
                category.id,
                category.code,
                category.parent_id,
                category.root as root_id,
                translation.translations,
                category.value_collection
            FROM
                pim_catalog_category category
                LEFT JOIN translation ON translation.id = category.id
            WHERE category.parent_id = :parent_id
        SQL;

        $results = $this->connection->executeQuery(
            $sqlQuery,
            ['parent_id' => $parentId->getValue()],
            ['parent_id' => \PDO::PARAM_INT],
        )->fetchAllAssociative();

        return CategoryCollection::fromArray(array_map(static function ($results) {
            return Category::fromDatabase($results);
        }, $results));
    }

    public function isAncestor(CategoryId $parentId, CategoryId $childId): bool {
        $sqlQuery = <<<SQL
            WITH parent_node as (
                select *
                FROM pim_catalog_category
                WHERE root = :parent_id 
            )
            SELECT child_node.id
            FROM pim_catalog_category child_node, parent_node
            WHERE child_node.id = :child_id
            AND parent_node.id = :parent_id
            and parent_node.root = child_node.root
            and child_node.lft > parent_node.lft
            and child_node.rgt < parent_node.rgt;
        SQL;

        $result = $this->connection->executeQuery(
            $sqlQuery,
            [
                'parent_id' => $parentId->getValue(),
                'child_id' => $childId->getValue(),
            ],
            [
                'parent_id' => \PDO::PARAM_INT,
                'child_id' => \PDO::PARAM_INT,
            ],
        )->rowCount();

        return $result > 0;
    }

    private function execute(array $condition): ?Category
    {
        $sqlWhere = $condition['sqlWhere'];

        $sqlQuery = <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE $sqlWhere
            )
            SELECT
                category.id,
                category.code, 
                category.parent_id,
                category.root as root_id,
                translation.translations,
                category.value_collection
            FROM 
                pim_catalog_category category
                LEFT JOIN translation ON translation.code = category.code
            WHERE $sqlWhere
        SQL;

        $result = $this->connection->executeQuery(
            $sqlQuery,
            $condition['params'],
            $condition['types']
        )->fetchAssociative();

        if (!$result) {
            return null;
        }

        return Category::fromDatabase($result);
    }
}
