<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AttachmentType;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes as BTypes;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes as CTypes;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class QuoteAttachmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAttachmentType extends AttachmentType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($options['admin_mode']) {
            $builder->add('type', ChoiceType::class, [
                'label'    => 'ekyna_core.field.type',
                'choices'  => BTypes::getChoices([CTypes::TYPE_VOUCHER], 1),
                'required' => false,
            ]);
        }
    }
}
