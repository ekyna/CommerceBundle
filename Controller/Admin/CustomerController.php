<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Search\CustomerRepository;
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
     * {@inheritdoc}
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
        ], 'json', ['groups' => ['Default']]);

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
            'children' => [
                'type' => $this->config->getTableType(),
                'options' => [
                    'parent' => $customer,
                ]
            ],
            'quotes' => [
                'type' => $this->get('ekyna_commerce.quote.configuration')->getTableType(),
                'options' => [
                    'customer' => $customer,
                ]
            ],
            'orders' => [
                'type' => $this->get('ekyna_commerce.order.configuration')->getTableType(),
                'options' => [
                    'customer' => $customer,
                ]
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
