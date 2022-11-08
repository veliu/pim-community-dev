<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryCollection
{
    /**
     * @param array<Category> $categories
     */
    private function __construct(private ?array $categories)
    {
        Assert::allIsInstanceOf($categories, Category::class);
    }

    /**
     * @param array<Category> $categories
     */
    public static function fromArray(array $categories): self
    {
        return new self($categories);
    }

    /**
     * @return array<Category>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Retrieve an Category by his identifier.
     *
     * @param string $identifier format expected : 'code|uuid' (example : title|69e251b3-b876-48b5-9c09-92f54bfb528d)
     */
    public function getCategoryByIdentifier(string $identifier): ?Category
    {
        $category = array_filter(
            $this->categories,
            static function ($categorie) use ($identifier) {
                return $categorie->getId()->getValue() === $identifier;
            },
        );
        if (empty($category) || count($category) > 1) {
            return null;
        }

        return reset($category);
    }

    /**
     * Retrieve an Category by his code.
     */
    public function getCategoryByCode(string $code): ?Category
    {
        $category = array_filter(
            $this->categories,
            static function ($category) use ($code) {
                return (string) $category->getCode() === $code;
            },
        );
        if (empty($category) || count($category) > 1) {
            return null;
        }

        return reset($category);
    }

    public function addCategory(Category $category): self
    {
        $this->categories[] = $category;

        return new self($this->categories);
    }

    /**
     * @return array<int, mixed>
     */
    public function normalize(): array
    {
        return array_map(
            static fn (Category $category) => $category->normalize(),
            $this->categories,
        );
    }
}
