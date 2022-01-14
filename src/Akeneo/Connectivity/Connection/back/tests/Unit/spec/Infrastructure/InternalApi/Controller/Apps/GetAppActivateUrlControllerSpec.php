<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller\Apps;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetAppActivateUrlControllerSpec extends ObjectBehavior
{
    public function let(
        GetAppQueryInterface $getAppQuery,
        ClientProviderInterface $clientProvider,
        SecurityFacade $security,
        FeatureFlag $featureFlag,
    ): void {
        $this->beConstructedWith(
            $getAppQuery,
            $clientProvider,
            new AppUrlGenerator(new PimUrl('https://some_pim_url')),
            $security,
            $featureFlag,
        );
    }

    public function it_redirects_on_missing_xmlhttprequest_header(
        FeatureFlag $featureFlag,
        Request $request,
    ): void {
        $this->__invoke($request, 'foo')
            ->shouldBeLike(new RedirectResponse('/'));
    }

    public function it_throws_not_found_exception_with_feature_flag_disabled(
        FeatureFlag $featureFlag,
        Request $request,
    ): void {
        $request->isXmlHttpRequest()->willReturn(true);
        $featureFlag->isEnabled()->willReturn(false);

        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_access_denied_exception_with_missing_acl(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        Request $request,
    ): void {
        $request->isXmlHttpRequest()->willReturn(true);
        $featureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException())
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_not_found_exception_with_wrong_app_identifier(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        GetAppQueryInterface $getAppQuery,
        Request $request,
    ): void {
        $request->isXmlHttpRequest()->willReturn(true);
        $featureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $getAppQuery->execute('foo')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Invalid app identifier'))
            ->during('__invoke', [$request, 'foo']);
    }
}
