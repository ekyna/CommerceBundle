<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\ModalTrait;
use Ekyna\Bundle\CommerceBundle\Action\Admin\StockViewTrait;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\RegistryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;

/**
 * Class UpdateAdjustmentAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateAdjustmentAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use RepositoryTrait;
    use RegistryTrait;
    use FormTrait;
    use HelperTrait;
    use ManagerTrait;
    use StockViewTrait;
    use ModalTrait;

    public function __invoke(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $stockUnit = $this->context->getResource();
        if (!$stockUnit instanceof StockUnitInterface) {
            throw new UnexpectedTypeException($stockUnit, StockUnitInterface::class);
        }

        /** @var StockAdjustmentInterface $stockAdjustment */
        $stockAdjustment = $this
            ->getRepository(StockAdjustmentInterface::class)
            ->find(
                $this->request->attributes->getInt('adjustmentId')
            );

        if (!$stockAdjustment) {
            throw new NotFoundHttpException();
        }

        $type = $this
            ->getResourceRegistry()
            ->find(StockAdjustmentInterface::class)
            ->getData('form');

        $form = $this->createForm($type, $stockAdjustment, [
            'action'            => $this->generateResourcePath($stockUnit, self::class, [
                'adjustmentId' => $stockAdjustment->getId(),
            ]),
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            '_redirect_enabled' => true,
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $event = $this
                    ->getManager(StockAdjustmentInterface::class)
                    ->save($stockAdjustment);

                if (!$event->hasErrors()) {
                    return $this->createStockViewResponse($stockUnit->getSubject());
                }

                FormUtil::addErrorsFromResourceEvent($form, $event);
            } catch (StockLogicException $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        $modal = new Modal('stock_adjustment.header.edit');
        $modal
            ->setDomain('EkynaCommerce')
            ->setForm($form->createView())
            ->setVars([
                'form_template' => $this->options['form_template'],
            ])
            ->addButton(Modal::BTN_SUBMIT)
            ->addButton(Modal::BTN_CLOSE);

        return $this->renderModal($modal);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_stock_unit_adjustment_update',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_stock_unit_adjustment_update',
                'path'     => '/adjustments/{adjustmentId}/update',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.edit',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'success',
                'icon'         => 'plus',
            ],
            'options'    => [
                'form_template' => '@EkynaCommerce/Admin/Stock/stock_adjustment_form.html.twig',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'adjustmentId' => '\d+',
        ]);
    }
}
