<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\BalanceType;
use Ekyna\Component\Commerce\Customer\Balance\Balance;
use Ekyna\Component\Commerce\Customer\Balance\BalanceBuilder;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BalanceController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BalanceController extends AbstractController
{
    /**
     * Balance index action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $balance = new Balance($customer);
        $balance->setPublic(true);

        $form = $this
            ->createForm(BalanceType::class, $balance, [
                'action' => $this->generateUrl('ekyna_commerce_account_balance_index'),
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

                    $data = $this->get('serializer')->normalize($balance, 'csv');

                    return $this->createCsvResponse($data);
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

        return $this->render('@EkynaCommerce/Account/Balance/index.html.twig', [
            'customer' => $customer,
            'balance'  => $data,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * Creates the CSV file download response.
     *
     * @param array $data
     *
     * @return Response
     */
    private function createCsvResponse(array $data): Response
    {
        $path = tempnam(sys_get_temp_dir(), 'balance');

        $handle = fopen($path, 'w');
        foreach ($data['lines'] as $line) {
            fputcsv($handle, $line, ';');
        }
        fclose($handle);

        return $this->file($path, 'balance.csv');
    }
}
