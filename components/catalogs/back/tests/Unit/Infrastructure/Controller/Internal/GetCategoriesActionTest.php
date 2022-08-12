<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\GetCategoriesByCodeQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetCategoriesAction;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoriesActionTest extends TestCase
{
    private ?GetCategoriesAction $getCategoriesAction;
    private ?GetCategoriesByCodeQueryInterface $getCategoriesByCodeQuery;

    protected function setUp(): void
    {
        $this->getCategoriesByCodeQuery = $this->createMock(GetCategoriesByCodeQueryInterface::class);
        $this->getCategoriesAction = new GetCategoriesAction(
            $this->getCategoriesByCodeQuery,
        );
    }

    public function testItRedirectsWhenRequestIsNotAXmlHttpRequest(): void
    {
        self::assertInstanceOf(RedirectResponse::class, ($this->getCategoriesAction)(new Request()));
    }

    public function testItAnswersABadRequestIfTheQueryIsInvalid(): void
    {
        $this->expectException(BadRequestHttpException::class);

        ($this->getCategoriesAction)(
            new Request(
                query: ['codes' => 123],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );
    }

    public function testItReturnsCategoriesFromTheQuery(): void
    {
        $this->getCategoriesByCodeQuery
            ->method('execute')
            ->with(['codeA', 'codeB', 'codeC'])
            ->willReturn(['categoryA', 'categoryB', 'categoryC']);

        $response = ($this->getCategoriesAction)(
            new Request(
                query: ['codes' => 'codeA,codeB,codeC'],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertJsonStringEqualsJsonString(
            \json_encode(['categoryA', 'categoryB', 'categoryC'], JSON_THROW_ON_ERROR),
            $response->getContent()
        );
    }
}
