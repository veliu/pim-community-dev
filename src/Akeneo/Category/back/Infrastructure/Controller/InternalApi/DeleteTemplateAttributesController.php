<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\GetCategoryChildrenIds;
use Akeneo\Category\Application\Query\GetCategoryTreeByCategoryTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteTemplateAttributesController
{
    public function __construct(
        private SecurityFacade $securityFacade,
        private GetAttribute $getAttribute,
        private CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver,
        private GetCategoryTreeByCategoryTemplate $getCategoryTreeByCategoryTemplate,
        private GetCategoryInterface $getCategory,
        private GetCategoryChildrenIds $categoryChildrenIds,
        private CategorySaver $categorySaver,
    ) {
    }

    /**
     * @param string $templateCode
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(Request $request, string $templateUuid): JsonResponse
    {
        if ($this->securityFacade->isGranted('pim_enrich_product_category_template') === false) {
            throw new AccessDeniedException();
        }

        $templateUuidValueObject =  TemplateUuid::fromString($templateUuid);

        $attributeCollection = $this->getAttribute->byTemplateUuid($templateUuidValueObject);
        if($attributeCollection->count() > 0) {
            $this->categoryTemplateAttributeSaver->delete($attributeCollection->getAttributes());
        }

        $categoryTree = ($this->getCategoryTreeByCategoryTemplate)($templateUuidValueObject);
        $category = $this->getCategory->byId($categoryTree->getId()->getValue());
        $this->cleanCategoryAttributes($category->getId()->getValue());

        $childCategoriesIds = ($this->categoryChildrenIds)($category->getId()->getValue());
        if($childCategoriesIds) {
            foreach ($childCategoriesIds as $childCategoryId) {
                $this->cleanCategoryAttributes($childCategoryId);
            }
        }
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function cleanCategoryAttributes(int $categoryId) {
        $category = $this->getCategory->byId($categoryId);
        $category->setAttributes(null);
        $this->categorySaver->save($category);
    }
}
