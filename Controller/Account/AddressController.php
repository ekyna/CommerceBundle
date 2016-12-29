<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\CustomerAddressType;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddressController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressController extends AbstractController
{
    /**
     * Address index action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $customer = $this->getCustomer();

        $addresses = $this
            ->get('ekyna_commerce.customer_address.repository')
            ->findByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Address:index.html.twig', [
            'addresses' => $addresses,
        ]);
    }

    /**
     * Add address action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
        $address = $this
            ->get('ekyna_commerce.customer_address.repository')
            ->createNew();

        $address->setCustomer($this->getCustomer());

        $form = $this->createForm(CustomerAddressType::class, $address, [
            'method' => 'POST',
            'action' => $this->generateUrl('ekyna_commerce_account_address_add'),
        ]);

        $this->createFormFooter($form, [
            'cancel_path' => $this->generateUrl('ekyna_commerce_account_address_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->get('ekyna_commerce.customer_address.operator')
                ->update($address);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_commerce.account.address.edit.success', 'success');

                return $this->redirectToRoute('ekyna_commerce_account_address_index');
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        return $this->render('EkynaCommerceBundle:Account/Address:add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit address action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        if (null === $address = $this->findAddressByRequest($request)) {
            return $this->redirectToRoute('ekyna_commerce_account_address_index');
        }

        $form = $this
            ->createForm(CustomerAddressType::class, $address, [
                'method' => 'POST',
                'action' => $this->generateUrl('ekyna_commerce_account_address_edit', [
                    'addressId' => $address->getId(),
                ]),
            ]);

        $this->createFormFooter($form, [
            'cancel_path' => $this->generateUrl('ekyna_commerce_account_address_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->get('ekyna_commerce.customer_address.operator')
                ->update($address);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_commerce.account.address.edit.success', 'success');

                return $this->redirectToRoute('ekyna_commerce_account_address_index');
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        return $this->render('EkynaCommerceBundle:Account/Address:edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Remove address action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request)
    {
        if (null === $address = $this->findAddressByRequest($request)) {
            return $this->redirectToRoute('ekyna_commerce_account_address_index');
        }

        $form = $this
            ->createForm(ConfirmType::class, null, [
                'method'      => 'POST',
                'action'      => $this->generateUrl('ekyna_commerce_account_address_remove', [
                    'addressId' => $address->getId(),
                ]),
                'message'     => 'ekyna_commerce.account.address.remove.confirm',
                'cancel_path' => $this->generateUrl('ekyna_commerce_account_address_index'),
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->get('ekyna_commerce.customer_address.operator')
                ->delete($address);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_commerce.account.address.remove.success', 'success');

                return $this->redirectToRoute('ekyna_commerce_account_address_index');
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        return $this->render('EkynaCommerceBundle:Account/Address:remove.html.twig', [
            'address' => $address,
            'form'    => $form->createView(),
        ]);
    }

    /**
     * Finds the address by request.
     *
     * @param Request $request
     *
     * @return \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface
     */
    protected function findAddressByRequest(Request $request)
    {
        $id = intval($request->attributes->get('addressId'));

        if (0 < $id) {
            return $this
                ->get('ekyna_commerce.customer_address.repository')
                ->findOneBy([
                    'id'       => $id,
                    'customer' => $this->getCustomer(),
                ]);
        }

        return null;
    }
}
