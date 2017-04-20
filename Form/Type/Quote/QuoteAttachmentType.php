<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AttachmentType;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes as BTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes as CTypes;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class QuoteAttachmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAttachmentType extends AttachmentType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        if (!$options['admin_mode']) {
            return;
        }

        $builder->add('type', ConstantChoiceType::class, [
            'label'       => t('field.type', [], 'EkynaUi'),
            'class'       => BTypes::class,
            'filter'      => [CTypes::TYPE_VOUCHER],
            'filter_mode' => ConstantsInterface::FILTER_RESTRICT,
            'required'    => false,
        ]);
    }
}
