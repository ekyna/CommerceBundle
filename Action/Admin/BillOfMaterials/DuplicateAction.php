<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\BillOfMaterials;

use Ekyna\Bundle\AdminBundle\Action\DuplicateAction as BaseAction;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;
use function Symfony\Component\Translation\t;

/**
 * Class DuplicateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\BillOfMaterials
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DuplicateAction extends BaseAction
{
    protected function onInit(): ?Response
    {
        $source = $this->context->getResource();

        if (!$source instanceof BillOfMaterialsInterface) {
            throw new UnexpectedTypeException($source, BillOfMaterialsInterface::class);
        }

        if (BOMState::VALIDATED !== $source->getState()) {
            $this->addFlash('Unexpected state', ResourceMessage::TYPE_ERROR);

            return $this->redirect($this->generateResourcePath($source));
        }

        $response = parent::onInit();

        /** @var BillOfMaterialsInterface $copy */
        $copy = $this->getCopy();

        $copy->setState(BOMState::DRAFT);
        $copy->setVersion($source->getVersion() + 1);

        return $response;
    }

    protected function onPrePersist(): ?Response
    {
        /** @var BillOfMaterialsInterface $source */
        $source = $this->context->getResource();

        $source->setState(BOMState::ARCHIVED);

        $this->getManager()->persist($source);

        return parent::onPrePersist();
    }

    protected function getFormData(): ?object
    {
        return null;
    }

    protected function getFormOptions(): array
    {
        return [
            'attr' => ['class' => 'form-horizontal'],
            'message' => t('bill_of_materials.message.duplicate', [], 'EkynaCommerce')
        ];
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'options' => [
                'type'     => ConfirmType::class,
                'template' => '@EkynaCommerce/Admin/BillOfMaterials/duplicate.html.twig',
            ],
        ]);
    }
}
