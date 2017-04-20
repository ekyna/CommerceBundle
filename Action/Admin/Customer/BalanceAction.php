<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Customer;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\BalanceType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Mailer\Mailer;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\SerializerTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Customer\Balance\Balance;
use Ekyna\Component\Commerce\Customer\Balance\BalanceBuilder;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Helper\File\Csv;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class BalanceAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Customer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BalanceAction extends AbstractAction implements AdminActionInterface
{
    use FormTrait;
    use HelperTrait;
    use SerializerTrait;
    use FlashTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    private BalanceBuilder $balanceBuilder;
    private Mailer         $mailer;

    public function __construct(BalanceBuilder $balanceBuilder, Mailer $mailer)
    {
        $this->balanceBuilder = $balanceBuilder;
        $this->mailer = $mailer;
    }

    public function __invoke(): Response
    {
        $customer = $this->context->getResource();

        if (!$customer instanceof CustomerInterface) {
            throw new UnexpectedTypeException($customer, CustomerInterface::class);
        }

        $balance = new Balance($customer);
        $balance->setPublic(false);

        $form = $this
            ->createForm(BalanceType::class, $balance, [
                'action' => $this->generateResourcePath($customer, self::class),
                'method' => 'POST',
            ])
            ->add('submit', Type\SubmitType::class, [
                'label' => t('button.apply', [], 'EkynaUi'),
            ])
            ->add('notify', Type\SubmitType::class, [
                'label' => t('button.notify', [], 'EkynaUi'),
            ])
            ->add('export', Type\SubmitType::class, [
                'label' => t('button.export', [], 'EkynaUi'),
            ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ClickableInterface $export */
            $export = $form->get('export');

            /** @var ClickableInterface $notify */
            $notify = $form->get('notify');

            if ($export->isClicked() || $notify->isClicked()) {
                $this->balanceBuilder->build($balance);

                $lines = $this->serializer->normalize($balance, 'csv');

                $file = Csv::create('balance.csv');
                $file->addRows($lines);

                if ($export->isClicked()) {
                    return $file->download();
                }

                if ($notify->isClicked()) {
                    $balance->setPublic(false);

                    $data = $this->serializer->normalize($balance, 'json');

                    $this->mailer->sendCustomerBalance($customer, $data, $file->close());

                    $this->addFlash(t('notify.message.sent', [], 'EkynaCommerce'), 'success');

                    return $this->redirect($this->generateResourcePath($customer, self::class));
                }
            }
        }

        $this->balanceBuilder->build($balance);

        $data = $this->serializer->normalize($balance, 'json');

        if ($this->request->isXmlHttpRequest()) {
            return new JsonResponse($data);
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'balance' => $data,
            'form'    => $form->createView(),
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_customer_balance',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_balance',
                'path'     => '/balance',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'customer.button.balance',
                'trans_domain' => 'EkynaCommerce',
                'icon'         => 'fa fa-balance-scale',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/Customer/balance.html.twig',
            ],
        ];
    }
}
