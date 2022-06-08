<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\ModalTrait;
use Ekyna\Bundle\CommerceBundle\Action\Admin\StockViewTrait;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FactoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\RegistryTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CreateAdjustmentAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAdjustmentAction extends AbstractAction implements AdminActionInterface
{
    use RegistryTrait;
    use FactoryTrait;
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
            throw new UnexpectedValueException($stockUnit, StockUnitInterface::class);
        }

        /** @var StockAdjustmentInterface $stockAdjustment */
        $stockAdjustment = $this->getFactory(StockAdjustmentInterface::class)->create();
        $stockAdjustment->setStockUnit($stockUnit);

        $type = $this
            ->getResourceRegistry()
            ->find(StockAdjustmentInterface::class)
            ->getData('form');

        $form = $this->createForm($type, $stockAdjustment, [
            'action'            => $this->generateResourcePath($stockUnit, self::class),
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            '_redirect_enabled' => true,
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $event = $this
                    ->getManager(StockAdjustmentInterface::class)
                    ->create($stockAdjustment);

                if (!$event->hasErrors()) {
                    return $this->createStockViewResponse($stockUnit->getSubject());
                }

                FormUtil::addErrorsFromResourceEvent($form, $event);
            } catch (StockLogicException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $modal = new Modal('stock_adjustment.header.new');
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
            'name'       => 'commerce_stock_unit_adjustment_create',
            'permission' => Permission::CREATE,
            'route'      => [
                'name'     => 'admin_%s_stock_unit_adjustment_create',
                'path'     => '/adjustments/create',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.new',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'success',
                'icon'         => 'plus',
            ],
            'options'    => [
                'form_template' => '@EkynaCommerce/Admin/Stock/stock_adjustment_form.html.twig',
            ],
        ];
    }
}
