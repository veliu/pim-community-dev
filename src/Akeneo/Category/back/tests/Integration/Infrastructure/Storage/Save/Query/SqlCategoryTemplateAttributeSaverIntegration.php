<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

class SqlCategoryTemplateAttributeSaverIntegration extends CategoryTestCase
{
    public function testInsertNewCategoryAttributeInDatabase(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplateWithAttributes($templateUuid, $category->getId());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection()
        );

        /** @var Template $insertedTemplate */
        $insertedTemplate = $this->get(GetTemplate::class)->byUuid($templateModel->getUuid());

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateModel->getUuid());
        $insertedTemplate->setAttributeCollection($insertedAttributes);

        $this->assertEqualsCanonicalizing(
            array_keys($templateModel->getAttributeCollection()->getAttributes()),
            array_keys($insertedTemplate->getAttributeCollection()->getAttributes())
        );
    }

    public function testDeleteCategoryAttributeInDatabase(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateUuid = '7769527c-ac46-11ed-afa1-0242ac120002';
        $templateModel = $this->givenTemplateWithAttributes($templateUuid, $category->getId());

        $longDescriptionAttributeTestUuid = '2590f8dc-9aab-446b-9d9b-c0f64aa2d250';
        $longDescriptionAttributeTest = AttributeRichText::create(
            AttributeUuid::fromString($longDescriptionAttributeTestUuid),
            new AttributeCode('long_description_test'),
            AttributeOrder::fromInteger(14),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsScopable::fromBoolean(true),
            AttributeIsLocalizable::fromBoolean(true),
            LabelCollection::fromArray(['en_US' => 'Long description test']),
            TemplateUuid::fromString($templateUuid),
            AttributeAdditionalProperties::fromArray([]),
        );
        $templateModel->getAttributeCollection()->addAttribute($longDescriptionAttributeTest);

        $shortDescriptionAttributeTestUuid = '79da0fb4-ac42-11ed-afa1-0242ac120002';
        $shortDescriptionAttributeTest = AttributeRichText::create(
            AttributeUuid::fromString($shortDescriptionAttributeTestUuid),
            new AttributeCode('short_description_test'),
            AttributeOrder::fromInteger(15),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsScopable::fromBoolean(true),
            AttributeIsLocalizable::fromBoolean(true),
            LabelCollection::fromArray(['en_US' => 'Short description test']),
            TemplateUuid::fromString($templateUuid),
            AttributeAdditionalProperties::fromArray([]),
        );
        $templateModel->getAttributeCollection()->addAttribute($shortDescriptionAttributeTest);

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection()
        );

        /** @var AttributeCollection $insertedAttributes */
        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateModel->getUuid());

        $this->assertCount(15, $insertedAttributes);

        $this->get(CategoryTemplateAttributeSaver::class)->delete([$longDescriptionAttributeTest, $shortDescriptionAttributeTest]);

        /** @var AttributeCollection $insertedAttributes */
        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateModel->getUuid());

        $this->assertCount(13, $insertedAttributes);

        $this->get(CategoryTemplateAttributeSaver::class)->delete($templateModel->getAttributeCollection()->getAttributes());

        /** @var AttributeCollection $insertedAttributes */
        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateModel->getUuid());

        $this->assertCount(0, $insertedAttributes);
    }
}
