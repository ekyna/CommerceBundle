<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\CommerceBundle\Form\Type\Subject\SubjectChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\SubjectOrderExport;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SubjectOrderExportType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectOrderExportType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('subjects', CollectionType::class, [
            'label'         => 'ekyna_commerce.subject.label.plural',
            'entry_type'    => SubjectChoiceType::class,
            'entry_options' => [
                'context' => SubjectProviderInterface::CONTEXT_SALE,
            ],
            'allow_add'     => true,
            'allow_delete'  => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', SubjectOrderExport::class);
    }
}
