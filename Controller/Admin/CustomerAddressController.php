<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\AddressImportType;
use Ekyna\Component\Commerce\Customer\Import\AddressImport;
use Ekyna\Component\Commerce\Customer\Import\AddressImporter;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CustomerAddressController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressController extends ResourceController
{
    /**
     * Imports addresses.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function importAction(Request $request): Response
    {
        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = $context->getResource($this->getParentConfiguration()->getResourceName());

        $config = new AddressImport($customer);

        $form = $this
            ->createForm(AddressImportType::class, $config, [
                'action'     => $this->generateUrl('ekyna_commerce_customer_address_admin_import', [
                    'customerId' => $customer->getId(),
                ]),
                'attr'       => [
                    'class' => 'form-horizontal',
                ],
                'method'     => 'POST',
                'admin_mode' => true,
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'submit' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label'        => 'ekyna_core.button.import',
                            'attr'         => ['icon' => 'import'],
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
            try {
                $this->prepareImport($config, $form->get('file')->getData());

                $importer = $this->get(AddressImporter::class);
                $count = $importer->import($config);

                if (0 < $count) {
                    $this->addFlash("Importing $count addresses&hellip;", 'success');

                    return $this->redirect($this->generateResourcePath($customer));
                }

                if (!empty($errors = $importer->getErrors())) {
                    $this->addFlash(implode('<br>', $errors), 'danger');
                } else {
                    $this->addFlash("No address found in file.", 'warning');
                }
            } catch (CommerceExceptionInterface $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->appendBreadcrumb(
            sprintf('%s_balance', $this->config->getResourceName()),
            'ekyna_commerce.customer_address.button.import'
        );

        return $this->render(
            $this->config->getTemplate('import.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Moves the uploaded address file.
     *
     * @param AddressImport $config
     * @param UploadedFile  $file
     */
    private function prepareImport(AddressImport $config, UploadedFile $file): void
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate(
            'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
            $originalFilename
        );
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Move the file to the directory where brochures are stored
        try {
            $file = $file->move(sys_get_temp_dir(), $newFilename);
        } catch (FileException $e) {
            throw new RuntimeException($e->getMessage());
        }

        $config->setPath($file->getRealPath());
    }

    /**
     * Invoice default action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invoiceDefaultAction(Request $request): Response
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
        $address = $context->getResource($resourceName);
        $customer = $address->getCustomer();

        $this->isGranted('EDIT', $address);

        if (
            $address->isInvoiceDefault() && !(
                $customer->hasParent() &&
                null !== $customer->getParent()->getDefaultInvoiceAddress(true)
            )
        ) {
            return $this->redirect($this->generateResourcePath($address->getCustomer()));
        }

        $address->setInvoiceDefault(!$address->isInvoiceDefault());

        $event = $this->getOperator()->update($address);
        $event->toFlashes($this->getFlashBag());

        return $this->redirect($this->generateResourcePath($address->getCustomer()));
    }

    /**
     * Delivery default action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deliveryDefaultAction(Request $request): Response
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
        $address = $context->getResource($resourceName);
        $customer = $address->getCustomer();

        $this->isGranted('EDIT', $address);

        if (
            $address->isDeliveryDefault() && !(
                $customer->hasParent() &&
                null !== $customer->getParent()->getDefaultDeliveryAddress(true)
            )
        ) {
            return $this->redirect($this->generateResourcePath($address->getCustomer()));
        }

        $address->setDeliveryDefault(!$address->isDeliveryDefault());

        $event = $this->getOperator()->update($address);
        $event->toFlashes($this->getFlashBag());

        return $this->redirect($this->generateResourcePath($address->getCustomer()));
    }

    /**
     * Choice list action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function choiceListAction(Request $request): Response
    {
        $this->isGranted('VIEW');

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = $this
            ->get('ekyna_commerce.customer.repository')
            ->find($request->attributes->get('customerId'));

        if (!$customer) {
            throw $this->createNotFoundException('Customer not found.');
        }

        $addresses = $this
            ->get('ekyna_commerce.customer_address.repository')
            ->findByCustomerAndParents($customer);

        $data = $this
            ->get('serializer')
            ->serialize(['choices' => $addresses], 'json', ['groups' => ['Default']]);

        return new Response($data, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
