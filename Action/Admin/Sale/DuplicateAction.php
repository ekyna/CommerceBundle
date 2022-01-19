<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Decimal\Decimal;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\ResourceBundle\Action\FactoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\RegistryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\ResourceBundle\Action\TranslatorTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Type\FormActionsType;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Common\Model\TransformationTargets;
use Ekyna\Component\Commerce\Common\Transformer\SaleCopierFactoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\Translation\t;

/**
 * Class DuplicateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DuplicateAction extends AbstractSaleAction implements RoutingActionInterface
{
    use RegistryTrait;
    use FactoryTrait;
    use ManagerTrait;
    use HelperTrait;
    use FormTrait;
    use TranslatorTrait;
    use FlashTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    private SaleCopierFactoryInterface $saleCopierFactory;

    public function __construct(SaleCopierFactoryInterface $saleCopierFactory)
    {
        $this->saleCopierFactory = $saleCopierFactory;
    }

    public function __invoke(): Response
    {
        if (!$sourceSale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $target = $this->request->attributes->get('target');
        if (!TransformationTargets::isValidTargetForSale($target, $sourceSale, true)) {
            throw new InvalidArgumentException('Invalid target.');
        }

        $targetConfig = $this
            ->getResourceRegistry()
            ->find('ekyna_commerce.' . $target);

        $factory = $this->getFactory($targetConfig->getEntityInterface());
        if (!$factory instanceof SaleFactoryInterface) {
            throw new UnexpectedTypeException($factory, SaleFactoryInterface::class);
        }

        /** @var SaleInterface $targetSale */
        $targetSale = $factory->create(false);

        // Copies source to target
        $this
            ->saleCopierFactory
            ->create($sourceSale, $targetSale)
            ->copyData()
            ->copyItems();

        $targetSale
            ->setSameAddress(true)
            ->setCustomerGroup(null)
            ->setPaymentTerm(null)
            ->setOutstandingLimit(new Decimal(0))
            ->setDepositTotal(new Decimal(0))
            ->setSource(SaleSources::SOURCE_COMMERCIAL)
            ->setExchangeRate(null)
            ->setExchangeDate(null)
            ->setAcceptedAt(null);

        $factory->initialize($targetSale);

        $form = $this->createDuplicateConfirmForm(
            $sourceSale, $targetSale, $targetConfig->getData('form'), $target
        );

        $form->handleRequest($this->request);

        // If user confirmed
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->getManager($targetConfig->getEntityInterface())
                ->create($targetSale);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            } else {
                $this->addFlashFromEvent($event);

                return $this->redirect($this->generateResourcePath($targetSale));
            }
        }

        $this->breadcrumbFromContext($this->context);

        $config = $this->context->getConfig();

        return $this->render($this->options['template'], [
            'context'                   => $this->context,
            $config->getCamelCaseName() => $this->context->getResource(),
            'form'                      => $form->createView(),
            'form_template'             => $this->options['form_templates'][$target],
            'target'                    => $target,
        ]);
    }

    protected function createDuplicateConfirmForm(
        SaleInterface $sourceSale,
        SaleInterface $targetSale,
        string        $type,
        string        $target
    ): FormInterface {
        $action = $this->generateResourcePath($sourceSale, self::class, ['target' => $target]);

        $form = $this->createForm($type, $targetSale, [
            'action'            => $action,
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            '_redirect_enabled' => true,
        ]);

        $message = $this->translator->trans('sale.confirm.duplicate', [], 'EkynaCommerce');

        return $form
            ->add('confirm', Type\CheckboxType::class, [
                'label'       => $message,
                'attr'        => ['align_with_widget' => true],
                'mapped'      => false,
                'required'    => true,
                'constraints' => [
                    new Assert\IsTrue(),
                ],
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'duplicate' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'warning',
                            'label'        => t('button.duplicate', [], 'EkynaUi'),
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                    'cancel'    => [
                        'type'    => Type\ButtonType::class,
                        'options' => [
                            'label'        => t('button.cancel', [], 'EkynaUi'),
                            'button_class' => 'default',
                            'as_link'      => true,
                            'attr'         => [
                                'class' => 'form-cancel-btn',
                                'icon'  => 'remove',
                                'href'  => $this->generateResourcePath($sourceSale),
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_duplicate',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_duplicate',
                'path'     => '/duplicate/{target}',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'button.duplicate',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'duplicate',
            ],
            'options'    => [
                'template'       => '@EkynaCommerce/Admin/Common/Sale/duplicate.html.twig',
                'form_templates' => [
                    'cart'  => '@EkynaCommerce/Admin/Cart/_form.html.twig',
                    'order' => '@EkynaCommerce/Admin/Order/_form.html.twig',
                    'quote' => '@EkynaCommerce/Admin/Quote/_form.html.twig',
                ],
            ],
        ];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('template')
            ->setAllowedTypes('template', 'string');
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'target' => 'cart|order|quote',
        ]);
    }
}
