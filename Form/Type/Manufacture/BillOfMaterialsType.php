<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture;

use Ekyna\Bundle\CommerceBundle\Form\Type\Subject\SubjectChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class BillOfMaterialsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterialsType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $data = $event->getData();

            if (!$data instanceof BillOfMaterialsInterface) {
                throw new UnexpectedTypeException($data, BillOfMaterialsInterface::class);
            }

            $disabled = BOMState::DRAFT !== $data->getState();

            $event
                ->getForm()
                ->add('subjectIdentity', SubjectChoiceType::class, [
                    'lock_mode' => true,
                    'context'   => SubjectProviderInterface::CONTEXT_SUPPLIER,
                    'disabled'  => $disabled,
                ])
                ->add('components', CollectionType::class, [
                    'label'         => t('field.components', [], 'EkynaCommerce'),
                    'entry_type'    => BOMComponentType::class,
                    'allow_add'     => !$disabled,
                    'allow_delete'  => !$disabled,
                    'allow_sort'    => !$disabled,
                    'disabled'      => $disabled,
                ]);
        });
    }
}
