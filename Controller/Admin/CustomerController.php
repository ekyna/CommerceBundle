<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\BalanceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerExportType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Component\Commerce\Customer\Balance\Balance;
use Ekyna\Component\Commerce\Customer\Balance\BalanceBuilder;
use Ekyna\Component\Commerce\Customer\Export\CustomerExport;
use Ekyna\Component\Commerce\Customer\Export\CustomerExporter;
use Ekyna\Component\Resource\Search\Request as SearchRequest;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CustomerController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerController extends ResourceController
{
    /**
     * Sale summary action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function summaryAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $context = $this->loadContext($request);
        /** @var CustomerInterface $customer */
        $customer = $context->getResource();

        $this->isGranted('VIEW', $customer);

        $response = new Response();
        $response->setVary(['Accept', 'Accept-Encoding']);
        $response->setExpires(new \DateTime('+5 min'));

        $html   = false;
        $accept = $request->getAcceptableContentTypes();

        if (in_array('application/json', $accept, true)) {
            $response->headers->add(['Content-Type' => 'application/json']);
        } elseif (in_array('text/html', $accept, true)) {
            $html = true;
        } else {
            throw $this->createNotFoundException("Unsupported content type.");
        }

        if ($html) {
            $content = $this->get('serializer')->normalize($customer, 'json', ['groups' => ['Summary']]);
            $content = $this->renderView('@EkynaCommerce/Admin/Customer/summary.html.twig', $content);
        } else {
            $content = $this->get('serializer')->serialize($customer, 'json', ['groups' => ['Summary']]);
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * Balance action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function balanceAction(Request $request): Response
    {
        $context = $this->loadContext($request);
        /** @var CustomerInterface $customer */
        $customer = $context->getResource();

        $balance = new Balance($customer);
        $balance->setPublic(false);

        $form = $this
            ->createForm(BalanceType::class, $balance, [
                'action' => $this->generateUrl('ekyna_commerce_customer_admin_balance', [
                    'customerId' => $customer->getId(),
                ]),
                'method' => 'POST',
            ])
            ->add('submit', Type\SubmitType::class, [
                'label' => 'ekyna_core.button.apply',
            ])
            ->add('notify', Type\SubmitType::class, [
                'label' => 'ekyna_core.button.notify',
            ])
            ->add('export', Type\SubmitType::class, [
                'label' => 'ekyna_core.button.export',
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\Form\ClickableInterface $export */
            $export = $form->get('export');
            /** @var \Symfony\Component\Form\ClickableInterface $notify */
            $notify = $form->get('notify');
            if ($export->isClicked() || $notify->isClicked()) {
                $this->get(BalanceBuilder::class)->build($balance);

                $lines = $this->get('serializer')->normalize($balance, 'csv');
                $path  = $this->createCsv($lines, 'balance');

                if ($export->isClicked()) {
                    return $this->file($path, 'balance.csv');
                }

                if ($notify->isClicked()) {
                    $balance->setPublic(false);

                    $data = $this->get('serializer')->normalize($balance, 'json');

                    $this->get('ekyna_commerce.mailer')->sendCustomerBalance($customer, $data, $path);

                    $this->addFlash('ekyna_commerce.notify.message.sent', 'success');

                    return $this->redirectToRoute('ekyna_commerce_customer_admin_balance', [
                        'customerId' => $customer->getId(),
                    ]);
                }
            }
        }

        $this->get(BalanceBuilder::class)->build($balance);

        $data = $this->get('serializer')->normalize($balance, 'json');

        if ($request->isXmlHttpRequest()) {
            return JsonResponse::create($data);
        }

        $this->appendBreadcrumb(
            sprintf('%s_balance', $this->config->getResourceName()),
            'ekyna_commerce.customer.button.balance'
        );

        return $this->render(
            $this->config->getTemplate('balance.html'),
            $context->getTemplateVars([
                'balance' => $data,
                'form'    => $form->createView(),
            ])
        );
    }

    /**
     * Export action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exportAction(Request $request): Response
    {
        $context = $this->loadContext($request);

        $data = new CustomerExport();
        $data
            ->setFrom(new \DateTime('first day of january'))
            ->setTo(new \DateTime());

        $form = $this
            ->createForm(CustomerExportType::class, $data, [
                'action' => $this->generateUrl('ekyna_commerce_customer_admin_export'),
                'method' => 'POST',
                'attr'   => ['class' => 'form-horizontal'],
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'submit' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label'        => 'ekyna_core.button.export',
                            'attr'         => ['icon' => 'download'],
                        ],
                    ],
                    'cancel' => [
                        'type'    => Type\ButtonType::class,
                        'options' => [
                            'label'        => 'ekyna_core.button.cancel',
                            'button_class' => 'default',
                            'as_link'      => true,
                            'attr'         => [
                                'class' => 'form-cancel-btn',
                                'icon'  => 'remove',
                                'href'  => $this->generateUrl('ekyna_commerce_customer_admin_list'),
                            ],
                        ],
                    ],
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->file($this->get(CustomerExporter::class)->export($data), 'customers.csv');
        }

        $this->appendBreadcrumb(
            sprintf('%s_export', $this->config->getResourceName()),
            'ekyna_commerce.customer.button.export'
        );

        return $this->render(
            $this->config->getTemplate('export.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Create user action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createUserAction(Request $request): Response
    {
        $context = $this->loadContext($request);
        /** @var CustomerInterface $customer */
        $customer = $context->getResource();

        $this->isGranted('EDIT', $customer);

        $cancelPath = $this->generateResourcePath($customer);

        if ($customer->getUser()) {
            $this->addFlash('ekyna_commerce.customer.message.user_exists');

            return $this->redirect($cancelPath);
        }

        $form = $this->createForm(ConfirmType::class, null, [
            'action'       => $this->generateResourcePath($customer, 'create_user'),
            'method'       => 'POST',
            'message'      => 'ekyna_commerce.customer.message.create_user_confirm',
            'cancel_path'  => $cancelPath,
            'submit_class' => 'success',
            'submit_icon'  => 'ok',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fosManager = $this->get('fos_user.user_manager');

            /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
            $user = $fosManager->createUser();
            $user
                ->setSendCreationEmail(true)
                ->setEnabled(true)
                ->setEmail($customer->getEmail());

            $customer->setUser($user);
            $this->getManager()->persist($customer);
            // TODO Validation ?

            // TODO use ResourceManager
            $event = $this->get('ekyna_user.user.operator')->create($user);

            $event->toFlashes($this->getFlashBag());

            return $this->redirect($cancelPath);
        }

        return $this->render(
            $this->config->getTemplate('create_user.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * @inheritDoc
     */
    protected function createSearchRequest(Request $request): SearchRequest
    {
        $searchRequest = parent::createSearchRequest($request);

        $searchRequest->setParameter('parent', (bool)intval($request->query->get('parent', 0)));

        return $searchRequest;
    }

    /**
     * @inheritDoc
     */
    protected function createNew(Context $context): CustomerInterface
    {
        /** @var CustomerInterface $customer */
        $customer = parent::createNew($context);

        /** @var CustomerInterface $parent */
        $parent = $this->getRepository()->find($context->getRequest()->query->get('parent'));
        if (null !== $parent) {
            $customer
                ->setParent($parent)
                ->setCustomerGroup($parent->getCustomerGroup());
        } else {
            $customer->setCustomerGroup(
                $this->get('ekyna_commerce.customer_group.repository')->findDefault()
            );
        }

        return $customer;
    }

    /**
     * @inheritDoc
     */
    protected function buildShowData(array &$data, Context $context)
    {
        $request = $context->getRequest();
        /** @var CustomerInterface $customer */
        $customer = $context->getResource();

        if (!$customer->hasParent()) {
            if (null === $customer->getDefaultInvoiceAddress()) {
                $this->addFlash('ekyna_commerce.customer.alert.no_invoice_address', 'warning');
            }
        }

        $tables = [
            'customer_children'  => [
                'type'    => $this->config->getTableType(),
                'options' => [
                    'parent' => $customer,
                ],
            ],
            'customer_quotes'    => [
                'type'    => $this->get('ekyna_commerce.quote.configuration')->getTableType(),
                'options' => [
                    'customer' => $customer,
                ],
            ],
            'customer_orders'    => [
                'type'    => $this->get('ekyna_commerce.order.configuration')->getTableType(),
                'options' => [
                    'customer' => $customer,
                ],
            ],
            'customer_invoices'  => [
                'type'    => $this->get('ekyna_commerce.order_invoice.configuration')->getTableType(),
                'options' => [
                    'customer' => $customer,
                ],
            ],
            'customer_shipments' => [
                'type'    => $this->get('ekyna_commerce.order_shipment.configuration')->getTableType(),
                'options' => [
                    'customer' => $customer,
                ],
            ],
        ];

        foreach ($tables as $name => $config) {
            $table = $this
                ->getTableFactory()
                ->createTable($name, $config['type'], $config['options']);

            if (null !== $response = $table->handleRequest($request)) {
                return $response;
            }

            $data[$name] = $table->createView();
        }

        return null;
    }

    /**
     * Creates the CSV file.
     *
     * @param array  $lines
     * @param string $prefix
     *
     * @return string
     */
    private function createCsv(array $lines, string $prefix): string
    {
        $path = tempnam(sys_get_temp_dir(), $prefix);

        $handle = fopen($path, 'w');
        foreach ($lines as $line) {
            fputcsv($handle, $line, ';');
        }
        fclose($handle);

        return $path;
    }
}
