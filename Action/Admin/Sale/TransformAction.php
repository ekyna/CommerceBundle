<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleTransformType;
use Ekyna\Bundle\ResourceBundle\Action\FactoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RegistryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\ResourceBundle\Action\TranslatorTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Type\FormActionsType;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory\AbstractSaleFactory;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\TransformationTargets;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route;

use function Symfony\Component\Translation\t;

/**
 * Class TransformAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TransformAction extends AbstractSaleAction implements RoutingActionInterface
{
    use RegistryTrait;
    use FactoryTrait;
    use HelperTrait;
    use FormTrait;
    use TranslatorTrait;
    use FlashTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    public function __construct(
        private readonly SaleTransformerInterface $saleTransformer
    ) {
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

        // Initialize the transformation
        $event = $this->saleTransformer->initialize($sourceSale, $targetSale);
        if ($event->isPropagationStopped()) {
            if ($event->hasErrors()) {
                $this->addFlashFromEvent($event);
            }

            return $this->redirect($this->generateResourcePath($sourceSale));
        }

        $form = $this->createTransformConfirmForm($sourceSale, $targetSale, $target);

        $form->handleRequest($this->request);

        // If user confirmed
        if ($form->isSubmitted() && $form->isValid()) {
            // Do sale transformation
            if (null === $event = $this->saleTransformer->transform()) {
                // Redirect to target sale
                return $this->redirect($this->generateResourcePath($targetSale));
            }

            $this->addFlashFromEvent($event);
        }

        $this->breadcrumbFromContext($this->context);

        $config = $this->context->getConfig();

        return $this->render($this->options['template'], [
            'context'                   => $this->context,
            $config->getCamelCaseName() => $this->context->getResource(),
            'form'                      => $form->createView(),
            'target'                    => $target,
        ]);
    }

    protected function createTransformConfirmForm(
        SaleInterface $sourceSale,
        SaleInterface $targetSale,
        string        $target
    ): FormInterface {
        $action = $this->generateResourcePath($sourceSale, self::class, ['target' => $target]);

        $message = $this->translator->trans('sale.confirm.transform', [
            '%target%' => $this->translator->trans($target . '.label.singular', [], 'EkynaCommerce'),
        ], 'EkynaCommerce');

        return $this
            ->createForm($this->options['type'], $targetSale, [
                'action'            => $action,
                'attr'              => ['class' => 'form-horizontal'],
                'method'            => 'POST',
                '_redirect_enabled' => true,
                'message'           => $message,
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'transform' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'warning',
                            'label'        => t('button.transform', [], 'EkynaUi'),
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
            'name'       => 'commerce_sale_transform',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_transform',
                'path'     => '/transform/{target}',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'button.transform',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'magic',
            ],
            'options'    => [
                'type'     => SaleTransformType::class,
                'template' => '@EkynaCommerce/Admin/Common/Sale/transform.html.twig',
            ],
        ];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['type', 'template'])
            ->setAllowedTypes('type', 'string')
            ->setAllowedTypes('template', 'string');
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'target' => 'cart|order|quote',
        ]);
    }
}
