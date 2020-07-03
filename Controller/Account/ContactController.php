<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerContactType;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface;
use Ekyna\Component\Commerce\Features;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ContactController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContactController extends AbstractController
{
    /**
     * Contact index action.
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        $this->checkFeature();

        $customer = $this->getCustomerOrRedirect();

        $parameters = [];

        $parameters['contacts'] = $this
            ->get('ekyna_commerce.customer_contact.repository')
            ->findByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Contact/index.html.twig', $parameters);
    }

    /**
     * Add contact action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addAction(Request $request): Response
    {
        $this->checkFeature();

        $customer = $this->getCustomerOrRedirect();

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface $contact */
        $contact = $this
            ->get('ekyna_commerce.customer_contact.repository')
            ->createNew();

        $contact->setCustomer($customer);

        $form = $this->createForm(CustomerContactType::class, $contact, [
            'method' => 'POST',
            'action' => $this->generateUrl('ekyna_commerce_account_contact_add'),
        ]);

        $this->createFormFooter($form, [
            'cancel_path' => $this->generateUrl('ekyna_commerce_account_contact_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->get('ekyna_commerce.customer_contact.operator')
                ->update($contact);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_commerce.account.contact.edit.success', 'success');

                return $this->redirectToRoute('ekyna_commerce_account_contact_index');
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $contacts = $this
            ->get('ekyna_commerce.customer_contact.repository')
            ->findByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Contact/add.html.twig', [
            'form'      => $form->createView(),
            'contacts' => $contacts,
        ]);
    }

    /**
     * Edit contact action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request): Response
    {
        $this->checkFeature();

        $customer = $this->getCustomerOrRedirect();

        if (null === $contact = $this->findContactByRequest($request)) {
            return $this->redirectToRoute('ekyna_commerce_account_contact_index');
        }

        $form = $this
            ->createForm(CustomerContactType::class, $contact, [
                'method' => 'POST',
                'action' => $this->generateUrl('ekyna_commerce_account_contact_edit', [
                    'contactId' => $contact->getId(),
                ]),
            ]);

        $this->createFormFooter($form, [
            'cancel_path' => $this->generateUrl('ekyna_commerce_account_contact_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->get('ekyna_commerce.customer_contact.operator')
                ->update($contact);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_commerce.account.contact.edit.success', 'success');

                return $this->redirectToRoute('ekyna_commerce_account_contact_index');
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $contacts = $this
            ->get('ekyna_commerce.customer_contact.repository')
            ->findByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Contact/edit.html.twig', [
            'form' => $form->createView(),
            'contacts' => $contacts,
        ]);
    }

    /**
     * Remove contact action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function removeAction(Request $request): Response
    {
        $this->checkFeature();

        $customer = $this->getCustomerOrRedirect();

        if (null === $contact = $this->findContactByRequest($request)) {
            return $this->redirectToRoute('ekyna_commerce_account_contact_index');
        }

        $form = $this
            ->createForm(ConfirmType::class, null, [
                'method'      => 'POST',
                'action'      => $this->generateUrl('ekyna_commerce_account_contact_remove', [
                    'contactId' => $contact->getId(),
                ]),
                'message'     => 'ekyna_commerce.account.contact.remove.confirm',
                'cancel_path' => $this->generateUrl('ekyna_commerce_account_contact_index'),
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->get('ekyna_commerce.customer_contact.operator')
                ->delete($contact);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_commerce.account.contact.remove.success', 'success');

                return $this->redirectToRoute('ekyna_commerce_account_contact_index');
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $contacts = $this
            ->get('ekyna_commerce.customer_contact.repository')
            ->findByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Contact/remove.html.twig', [
            'contact' => $contact,
            'form'    => $form->createView(),
            'contacts' => $contacts,
        ]);
    }

    /**
     * Finds the contact by request.
     *
     * @param Request $request
     *
     * @return CustomerContactInterface
     */
    protected function findContactByRequest(Request $request): ?CustomerContactInterface
    {
        $this->checkFeature();

        $customer = $this->getCustomerOrRedirect();

        $id = intval($request->attributes->get('contactId'));

        if (0 < $id) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this
                ->get('ekyna_commerce.customer_contact.repository')
                ->findOneBy([
                    'id'       => $id,
                    'customer' => $customer,
                ]);
        }

        return null;
    }

    /**
     * Checks whether the customer contact feature is enabled in customer account.
     */
    private function checkFeature(): void
    {
        if ($this->get(Features::class)->getConfig(Features::CUSTOMER_CONTACT . '.account')) {
            return;
        }

        throw $this->createNotFoundException();
    }
}
