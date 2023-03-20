<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InitializeEvaluationOfAProductSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FeatureFlag                     $dataQualityInsightsFeature,
        private CreateCriteriaEvaluations       $createProductsCriteriaEvaluations,
        private LoggerInterface                 $logger,
        private ProductEntityIdFactoryInterface $idFactory,
        private readonly MessageBusInterface $messageBus,
        private readonly Clock $clock,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Priority greater than zero to ensure that the evaluation is done prior to the re-indexation of the product in ES
            StorageEvents::POST_SAVE => ['onPostSave', 10],
            StorageEvents::POST_SAVE_ALL => 'onPostSaveAll',
        ];
    }

    public function onPostSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof ProductInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $productUuidCollection = ProductUuidCollection::fromProductUuids([
            ProductUuid::fromUuid($subject->getUuid())
        ]);

        $this->launchMessage($productUuidCollection);

        //$this->initializeCriteria($subject->getUuid());
    }

    public function onPostSaveAll(GenericEvent $event): void
    {
        $products = $event->getSubject();
        if (!is_array($products)) {
            return;
        }

        $products = array_filter(
            $products,
            fn ($product) => $product instanceof ProductInterface
        );

        if (empty($products)) {
            return;
        }

        $productUuidCollection = ProductUuidCollection::fromProductUuids(array_map(
            fn (ProductInterface $product) => ProductUuid::fromUuid($product->getUuid()),
            $products
        ));

        $this->launchMessage($productUuidCollection);
    }

    private function launchMessage(ProductUuidCollection $productUuidCollection): void
    {
        $datetime = $this->clock->getCurrentTime();
        try {
            $message = LaunchProductAndProductModelEvaluationsMessage::forProductsOnly(
                $datetime,
                $productUuidCollection,
                [],
            );
            $this->messageBus->dispatch($message);
            $this->logger->debug('DQI - message sent from subscriber');
        } catch (\Throwable $exception) {
            $this->logger->error('DQI - Failed to send message for products evaluations', [
                'error_message' => $exception->getMessage(),
                'product_uuids' => $productUuidCollection->toArrayString(),
                'date_time' => $datetime->format('c')
            ]);
        }
    }

    private function initializeCriteria(UuidInterface $productUuid): void
    {
        try {
            $productIdCollection = $this->idFactory->createCollection([$productUuid->toString()]);
            $this->createProductsCriteriaEvaluations->createAll($productIdCollection);
        } catch (\Throwable $e) {
            $this->logger->error(
                'Unable to create product criteria evaluation',
                [
                    'error_code' => 'unable_to_create_product_criteria_evaluation',
                    'error_message' => $e->getMessage(),
                ]
            );
        }
    }
}
