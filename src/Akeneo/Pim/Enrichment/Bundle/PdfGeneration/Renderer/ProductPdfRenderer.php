<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPdfRenderer implements RendererInterface
{
    /** @var string */
    const PDF_FORMAT = 'pdf';

    const THUMBNAIL_FILTER = 'pdf_thumbnail';

    /** @var EngineInterface */
    protected $templating;

    /** @var PdfBuilderInterface */
    protected $pdfBuilder;

    /** @var DataManager */
    protected $dataManager;

    /** @var CacheManager */
    protected $cacheManager;

    /** @var FilterManager */
    protected $filterManager;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var string */
    protected $template;

    /** @var string */
    protected $uploadDirectory;

    /** @var string */
    protected $customFont;

    public function __construct(
        EngineInterface $templating,
        PdfBuilderInterface $pdfBuilder,
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        string $template,
        string $uploadDirectory,
        ?string $customFont = null
    ) {
        $this->templating = $templating;
        $this->pdfBuilder = $pdfBuilder;
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->attributeRepository = $attributeRepository;
        $this->template = $template;
        $this->uploadDirectory = $uploadDirectory;
        $this->customFont = $customFont;
    }

    /**
     * {@inheritdoc}
     */
    public function render($object, $format, array $context = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $imagePaths = $this->getImagePaths($object, $context['locale'], $context['scope']);
        $params = array_merge(
            $context,
            [
                'product'           => $object,
                'groupedAttributes' => $this->getGroupedAttributes($object, $context['locale']),
                'imagePaths'        => $imagePaths,
                'customFont'        => $this->customFont
            ]
        );

        $params = $resolver->resolve($params);

        $this->generateThumbnailsCache($imagePaths, $params['filter']);

        return $this->pdfBuilder->buildPdfOutput(
            $this->templating->render($this->template, $params)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $format)
    {
        return $object instanceof ProductInterface && $format === static::PDF_FORMAT;
    }

    /**
     * Get attributes codes to display
     *
     * @param ProductInterface $product
     * @param string           $locale
     *
     * @return
     */
    protected function getAttributeCodes(ProductInterface $product, $locale)
    {
        return $product->getUsedAttributeCodes();
    }

    /**
     * get attributes grouped by attribute group
     *
     * @param ProductInterface $product
     * @param string           $locale
     *
     * @return AttributeInterface[]
     */
    protected function getGroupedAttributes(ProductInterface $product, $locale)
    {
        $groups = [];

        foreach ($this->getAttributeCodes($product, $locale) as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null !== $attribute) {
                $groupLabel = $attribute->getGroup()->getLabel();
                if (!isset($groups[$groupLabel])) {
                    $groups[$groupLabel] = [];
                }

                $groups[$groupLabel][$attribute->getCode()] = $attribute;
            }
        }

        return $groups;
    }

    /**
     * Get all image paths
     *
     * @param ProductInterface $product
     * @param string           $locale
     * @param string           $scope
     *
     * @return string[]
     */
    protected function getImagePaths(ProductInterface $product, $locale, $scope)
    {
        $imagePaths = [];

        foreach ($this->getAttributeCodes($product, $locale) as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null !== $attribute && AttributeTypes::IMAGE === $attribute->getType()) {
                $mediaValue = $product->getValue(
                    $attribute->getCode(),
                    $attribute->isLocalizable() ? $locale : null,
                    $attribute->isScopable() ? $scope : null
                );

                if (null !== $mediaValue) {
                    $media = $mediaValue->getData();
                    if (null !== $media && null !== $media->getKey()) {
                        $imagePaths[] = $media->getKey();
                    }
                }
            }
        }

        return $imagePaths;
    }

    /**
     * Generate media thumbnails cache used by the PDF document
     *
     * @param string[] $imagePaths
     * @param string   $filter
     */
    protected function generateThumbnailsCache(array $imagePaths, $filter)
    {
        foreach ($imagePaths as $path) {
            if (!$this->cacheManager->isStored($path, $filter)) {
                $binary = $this->dataManager->find($filter, $path);
                $this->cacheManager->store(
                    $this->filterManager->applyFilter($binary, $filter),
                    $path,
                    $filter
                );
            }
        }
    }

    /**
     * Options configuration (for the option resolver)
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['locale', 'scope', 'product'])
            ->setDefaults(
                [
                    'renderingDate' => new \DateTime(),
                    'filter'        => static::THUMBNAIL_FILTER,
                ]
            )
            ->setDefined(['groupedAttributes', 'imagePaths', 'customFont'])
        ;
    }
}
