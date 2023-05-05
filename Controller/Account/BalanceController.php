<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\BalanceType;
use Ekyna\Component\Commerce\Customer\Balance\Balance;
use Ekyna\Component\Commerce\Customer\Balance\BalanceBuilder;
use Ekyna\Component\Resource\Helper\File\Csv;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class BalanceController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BalanceController implements ControllerInterface
{
    use CustomerTrait;

    public function __construct(
        private readonly FormFactoryInterface  $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly BalanceBuilder        $balanceBuilder,
        private readonly SerializerInterface   $serializer,
        private readonly Environment           $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->getCustomer();

        $balance = new Balance($customer);
        $balance->setPublic(true);

        $form = $this
            ->formFactory
            ->create(BalanceType::class, $balance, [
                'action' => $this->urlGenerator->generate('ekyna_commerce_account_balance_index'),
                'method' => 'POST',
            ])
            ->add('submit', SubmitType::class, [
                'label' => t('button.apply', [], 'EkynaUi'),
            ])
            ->add('export', SubmitType::class, [
                'label' => t('button.export', [], 'EkynaUi'),
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($form->get('export')->isClicked()) {
                    $this->balanceBuilder->build($balance);

                    $data = $this->serializer->normalize($balance, 'csv');

                    $csv = Csv::create('balance.csv');
                    $csv->addRows($data);

                    return $csv->download();
                }
            } else {
                // TODO Fix data
            }
        }

        $this->balanceBuilder->build($balance);

        $data = $this->serializer->normalize($balance, 'json');

        if ($request->isXmlHttpRequest()) {
            return JsonResponse::fromJsonString($data);
        }

        $content = $this->twig->render('@EkynaCommerce/Account/Balance/index.html.twig', [
            'customer' => $customer,
            'balance'  => $data,
            'form'     => $form->createView(),
        ]);

        return (new Response($content))->setPrivate();
    }
}
