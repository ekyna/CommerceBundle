<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Account\InformationType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InformationController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InformationController extends AbstractController
{
    /**
     * Information index action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $customer = $this->getCustomerOrRedirect();

        return $this->render('EkynaCommerceBundle:Account/Information:index.html.twig', [
            'customer' => $customer,
        ]);
    }

    /**
     * Information edit action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $form = $this->createForm(InformationType::class, $customer, [
            'action'      => $this->generateUrl('ekyna_commerce_account_information_edit'),
            'method'      => 'POST',
            'cancel_path' => $this->generateUrl('ekyna_commerce_account_information_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->get('ekyna_commerce.customer.operator')
                ->update($customer);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_commerce.account.information.edit.success', 'success');

                return $this->redirectToRoute('ekyna_commerce_account_information_index');
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        return $this->render('EkynaCommerceBundle:Account/Information:edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
