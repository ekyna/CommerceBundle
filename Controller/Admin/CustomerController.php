<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\BalanceType;
use Ekyna\Bundle\CommerceBundle\Service\Search\CustomerRepository;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Component\Commerce\Customer\Balance\Balance;
use Ekyna\Component\Commerce\Customer\Balance\BalanceBuilder;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
     * @inheritDoc
     */
    public function searchAction(Request $request)
    {
        //$callback = $request->query->get('callback');
        $limit = intval($request->query->get('limit'));
        $query = trim($request->query->get('query'));
        $parent = intval($request->query->get('parent', 0));

        $repository = $this->get('fos_elastica.manager')->getRepository($this->config->getResourceClass());
        if (!$repository instanceOf CustomerRepository) {
            throw new \RuntimeException('Expected instance of ' . CustomerRepository::class);
        }

        if (0 < $parent) {
            $results = $repository->searchAvailableParents($query, $limit);
        } else {
            $results = $repository->defaultSearch($query, $limit);
        }

        $data = $this->container->get('serializer')->serialize([
            'results'     => $results,
            'total_count' => count($results),
        ], 'json', ['groups' => ['Search']]);

        $response = new Response($data);
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    /**
     * @inheritDoc
     */
    protected function createNew(Context $context)
    {
        /** @var \Ekyna\Bundle\CommerceBundle\Entity\Customer $customer */
        $customer = parent::createNew($context);

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $parent */
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
     * Sale summary action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function summaryAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = $context->getResource();

        $this->isGranted('VIEW', $customer);

        $response = new Response();
        $response->setVary(['Accept', 'Accept-Encoding']);
        $response->setExpires(new \DateTime('+5 min'));

        $html = false;
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
    public function balanceAction(Request $request)
    {
        $context = $this->loadContext($request);
        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
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
            ->add('submit', SubmitType::class, [
                'label' => 'ekyna_core.button.apply',
            ])
            ->add('export', SubmitType::class, [
                'label' => 'ekyna_core.button.export',
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($form->get('export')->isClicked()) {
                    $this->get(BalanceBuilder::class)->build($balance);

                    $lines = $this->get('serializer')->normalize($balance, 'csv');

                    return $this->createCsvResponse($lines);
                }
            } else {
                // TODO Fix data
            }
        }

        $this->get(BalanceBuilder::class)->build($balance);

        $data = $this->get('serializer')->normalize($balance, 'json');

        if ($request->isXmlHttpRequest()) {
            return JsonResponse::create($data);
        }

        return $this->render(
            $this->config->getTemplate('balance.html'),
            $context->getTemplateVars([
                'balance' => $data,
                'form'    => $form->createView(),
            ])
        );
    }

    /**
     * Creates the CSV file download response.
     *
     * @param array $lines
     *
     * @return Response
     */
    private function createCsvResponse(array $lines): Response
    {
        $path = tempnam(sys_get_temp_dir(), 'balance');

        $handle = fopen($path, 'w');
        foreach ($lines as $line) {
            fputcsv($handle, $line, ';');
        }
        fclose($handle);

        return $this->file($path, 'balance.csv');
    }

    /**
     * Create user action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createUserAction(Request $request)
    {
        $context = $this->loadContext($request);
        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
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
    protected function buildShowData(array &$data, Context $context)
    {
        $request = $context->getRequest();
        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
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
}
