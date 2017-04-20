<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\CommerceBundle\Form\Type\Subject\SubjectChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\SubjectOrderExport;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SubjectOrderExportType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectOrderExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('subjects', CollectionType::class, [
            'label'         => t('subject.label.plural', [], 'EkynaCommerce'),
            'entry_type'    => SubjectChoiceType::class,
            'entry_options' => [
                'context' => SubjectProviderInterface::CONTEXT_SALE,
            ],
            'allow_add'     => true,
            'allow_delete'  => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', SubjectOrderExport::class);
    }
}
