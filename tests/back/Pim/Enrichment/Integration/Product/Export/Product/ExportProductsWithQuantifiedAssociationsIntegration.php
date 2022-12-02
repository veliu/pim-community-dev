<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group ce
 */
class ExportProductsWithQuantifiedAssociationsIntegration extends AbstractExportTestCase
{
    private array $uuids = [];

    public function testVariantProductExportWithoutUuidColumn(): void
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;parent;groups;QUANTITY-products;QUANTITY-products-quantity;QUANTITY-product_models;QUANTITY-product_models-quantity;color;ean;name-en_US;size;variation_name
apollon_pink_l;round-neck,tshirt;1;clothing;apollon_pink;;apollon_pink_m;10;;;pink;12345679;;l;"my pink tshirt"
apollon_pink_m;round-neck,spring,tshirt;1;clothing;apollon_pink;;;;;;pink;12345678;;m;"my pink tshirt"
apollon_pink_xl;round-neck,summer,tshirt;1;clothing;apollon_pink;;,apollon_pink_m;8|12;;;pink;12345465;;xl;"my pink tshirt"
;;1;;;;;;;;;;;;

CSV;
        $this->assertProductExport($expectedCsv, []);
    }

    public function testVariantProductExportWithtUuidColumn(): void
    {
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;parent;groups;QUANTITY-product_uuids;QUANTITY-products-quantity;QUANTITY-product_models;QUANTITY-product_models-quantity;color;ean;name-en_US;size;variation_name
{$this->uuids['apollon_pink_l']};apollon_pink_l;round-neck,tshirt;1;clothing;apollon_pink;;{$this->uuids['apollon_pink_m']};10;;;pink;12345679;;l;"my pink tshirt"
{$this->uuids['apollon_pink_m']};apollon_pink_m;round-neck,spring,tshirt;1;clothing;apollon_pink;;;;;;pink;12345678;;m;"my pink tshirt"
{$this->uuids['apollon_pink_xl']};apollon_pink_xl;round-neck,summer,tshirt;1;clothing;apollon_pink;;{$this->uuids['product_without_identifier']},{$this->uuids['apollon_pink_m']};8|12;;;pink;12345465;;xl;"my pink tshirt"
{$this->uuids['product_without_identifier']};;;1;;;;;;;;;;;;

CSV;
        $this->assertProductExport($expectedCsv, ['with_uuid' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function loadFixtures(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_textarea',
            'group' => 'attributeGroupA',
            'localizable' => true,
            'scopable' => false,
        ]);
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'group' => 'attributeGroupA',
            'localizable' => false,
            'scopable' => false,
        ]);
        $this->createAttributeOption([
            'code' => 'blue',
            'attribute' => 'color',
        ]);
        $this->createAttributeOption([
            'code' => 'pink',
            'attribute' => 'color',
        ]);
        $this->createAttribute([
            'code' => 'size',
            'type' => 'pim_catalog_simpleselect',
            'group' => 'attributeGroupA',
            'localizable' => false,
            'scopable' => false,
        ]);
        $this->createAttributeOption([
            'code' => 'm',
            'attribute' => 'size',
        ]);
        $this->createAttributeOption([
            'code' => 'l',
            'attribute' => 'size',
        ]);
        $this->createAttributeOption([
            'code' => 'xl',
            'attribute' => 'size',
        ]);
        $this->createAttribute([
            'code' => 'ean',
            'type' => 'pim_catalog_text',
            'group' => 'attributeGroupA',
            'localizable' => false,
            'scopable' => false,
        ]);
        $this->createAttribute([
            'code' => 'variation_name',
            'type' => 'pim_catalog_text',
            'group' => 'attributeGroupA',
            'localizable' => false,
            'scopable' => false,
        ]);
        $this->createFamily([
            'code' => 'clothing',
            'attributes' => ['sku', 'name', 'color', 'variation_name', 'size', 'ean'],
            'attribute_requirements' => [
                'tablet' => ['sku', 'name'],
            ],
        ]);
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_text_area'],
        ]);
        $this->createFamilyVariant([
            'code' => 'clothing_color_size',
            'family' => 'clothing',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => ['color', 'variation_name'],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => ['size', 'ean', 'sku'],
                ],
            ],
        ]);

        // Associations
        $this->createAssociationType(
            [
                'code' => 'QUANTITY',
                'is_quantified'=> true,
            ]
        );

        $this->createCategory(['code' => 'tshirt']);
        $this->createCategory(['code' => 'summer', 'parent' => 'tshirt']);
        $this->createCategory(['code' => 'spring', 'parent' => 'tshirt']);
        $this->createCategory(['code' => 'long-sleeves', 'parent' => 'tshirt']);
        $this->createCategory(['code' => 'v-neck', 'parent' => 'long-sleeves']);
        $this->createCategory(['code' => 'round-neck', 'parent' => 'long-sleeves']);

        $this->createProductModel(
            [
                'code' => 'apollon',
                'family_variant' => 'clothing_color_size',
                'categories' => ['tshirt'],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'apollon_blue',
                'family_variant' => 'clothing_color_size',
                'parent' => 'apollon',
                'categories' => ['v-neck', 'summer'],
                'values' => [
                    'color' => [['data' => 'blue', 'locale' => null, 'scope' => null]],
                    'variation_name' => [['data' => 'my blue tshirt', 'locale' => null, 'scope' => null]],
                ],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'apollon_pink',
                'family_variant' => 'clothing_color_size',
                'parent' => 'apollon',
                'categories' => ['round-neck', 'tshirt'],
                'values' => [
                    'color' => [['data' => 'pink', 'locale' => null, 'scope' => null]],
                    'variation_name' => [['data' => 'my pink tshirt', 'locale' => null, 'scope' => null]],
                ],
            ]
        );

        $this->uuids['product_without_identifier'] = $this->createProductWithUuid(Uuid::uuid4())->getUuid()->toString();
        $this->uuids['apollon_pink_m'] = $this->createProduct('apollon_pink_m', [
            new SetFamily('clothing'),
            new ChangeParent('apollon_pink'),
            new SetCategories(['spring']),
            new SetSimpleSelectValue('size', null, null, 'm'),
            new SetTextValue('ean', null, null, '12345678'),
        ])->getUuid()->toString();
        $this->uuids['apollon_pink_l'] = $this->createProduct(
            'apollon_pink_l',
            [
                new SetFamily('clothing'),
                new ChangeParent('apollon_pink'),
                new SetSimpleSelectValue('size', null, null, 'l'),
                new SetTextValue('ean', null, null, '12345679'),
                new ReplaceAssociatedQuantifiedProductUuids('QUANTITY', [
                    new QuantifiedEntity($this->uuids['apollon_pink_m'], 10)
                ])
            ]
        )->getUuid()->toString();
        $this->uuids['apollon_pink_xl'] = $this->createProduct(
            'apollon_pink_xl',
            [
                new SetFamily('clothing'),
                new ChangeParent('apollon_pink'),
                new SetCategories(['tshirt', 'summer']),
                new SetSimpleSelectValue('size', null, null, 'xl'),
                new SetTextValue('ean', null, null, '12345465'),
                new ReplaceAssociatedQuantifiedProductUuids('QUANTITY', [
                    new QuantifiedEntity($this->uuids['product_without_identifier'], 8)
                ]),
                new AssociateQuantifiedProducts('QUANTITY', [
                    new QuantifiedEntity('apollon_pink_m', 12)
                ])
            ]
        )->getUuid()->toString();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }
}
