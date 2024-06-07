<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Report;

use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Service\Menu\MenuBuilder;
use Ekyna\Bundle\CommerceBundle\Entity\ReportRequest;
use Ekyna\Bundle\CommerceBundle\Form\Type\Report\ReportConfigType;
use Ekyna\Bundle\CommerceBundle\Message\SendSalesReport;
use Ekyna\Bundle\CommerceBundle\Repository\ReportRequestRepository;
use Ekyna\Bundle\UiBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class ReportController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\Report
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportController
{
    public function __construct(
        private readonly ReportRequestRepository $requestRepository,
        private readonly ManagerRegistry         $managerRegistry,
        private readonly LocaleProviderInterface $localeProvider,
        private readonly UserProviderInterface   $userProvider,
        private readonly FormFactoryInterface    $formFactory,
        private readonly UrlGeneratorInterface   $urlGenerator,
        private readonly Environment             $twig,
        private readonly MessageBusInterface     $messageBus,
        private readonly MenuBuilder             $menuBuilder,
        private readonly FlashHelper             $flashHelper,
        private readonly int                     $delay = 5
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (null === $this->userProvider->getUser()) {
            throw new AccessDeniedHttpException();
        }

        $this
            ->menuBuilder
            ->breadcrumbAppend([
                'name'         => 'sales_report',
                'label'        => 'report.title',
                'route'        => 'admin_ekyna_commerce_report_index',
                'trans_domain' => 'EkynaCommerce',
            ]);

        $config = $this->createConfig();

        $form = $this->createForm($config);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $redirect = new RedirectResponse(
                    $this->urlGenerator->generate('admin_ekyna_commerce_report_index')
                );

                if (!$this->throttleRequest()) {
                    $message = SendSalesReport::fromConfig($config);

                    $this->messageBus->dispatch($message);

                    $this->logRequest($config);

                    return $redirect;
                }
            }
        }

        $content = $this->twig->render('@EkynaCommerce/Admin/Report/index.html.twig', [
            'form' => $form->createView(),
        ]);

        return new Response($content);
    }

    private function throttleRequest(): bool
    {
        /** @var UserInterface $user */
        $user = $this->userProvider->getUser();

        $reportRequest = $this->requestRepository->findOneByUser($user);

        if (null === $reportRequest) {
            return false;
        }

        if ($this->delay < $past = $reportRequest->getMinutesPast()) {
            return false;
        }

        $this->flashHelper->addFlash(
            t('report.message.throttle', [
                '{minutes}' => max(1, $this->delay - $past),
            ], 'EkynaCommerce'),
            'danger'
        );

        return true;
    }

    private function logRequest(ReportConfig $config): void
    {
        if ($config->test) {
            return;
        }

        /** @var UserInterface $user */
        $user = $this->userProvider->getUser();
        $reportRequest = $this->requestRepository->findOneByUser($user);

        if (null === $reportRequest) {
            $reportRequest = new ReportRequest();
            $reportRequest->setUser($user);
        }

        $reportRequest->setRequestedAt(new DateTime());

        $manager = $this->managerRegistry->getManagerForClass(ReportRequest::class);
        $manager->persist($reportRequest);
        $manager->flush();

        $message = t('report.message.pending', [
            '{email}' => $config->email,
        ], 'EkynaCommerce');

        $this->flashHelper->addFlash($message, 'success');
    }

    private function createConfig(): ReportConfig
    {
        $config = new ReportConfig();
        $config->range->setStart(new DateTime('first day of january'));
        $config->locale = $this->localeProvider->getCurrentLocale();

        $config->email = $this->userProvider->getUser()?->getEmail();

        return $config;
    }

    private function createForm(ReportConfig $config): FormInterface
    {
        $form = $this->formFactory->create(ReportConfigType::class, $config);

        $buttons = [
            'submit' => [
                'type'    => SubmitType::class,
                'options' => [
                    'button_class' => 'primary',
                    'label'        => t('button.generate', [], 'EkynaUi'),
                    'attr'         => ['icon' => 'envelope'],
                ],
            ],
            'cancel' => [
                'type'    => ButtonType::class,
                'options' => [
                    'label'        => t('button.cancel', [], 'EkynaUi'),
                    'button_class' => 'default',
                    'as_link'      => true,
                    'attr'         => [
                        'class' => 'form-cancel-btn',
                        'icon'  => 'remove',
                        'href'  => $this->urlGenerator->generate('admin_dashboard'),
                    ],
                ],
            ],
        ];

        $form->add('actions', FormActionsType::class, [
            'buttons' => $buttons,
        ]);

        return $form;
    }
}
