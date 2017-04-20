<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PaymentTermTranslationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTermTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => t('field.title', [], 'EkynaUi'),
//                'admin_helper' => 'CMS_PAGE_TITLE',
            ])
            ->add('description', TinymceType::class, [
                'label'    => t('field.description', [], 'EkynaUi'),
//                'admin_helper' => 'CMS_PAGE_CONTENT',
                'theme'    => 'light',
                'required' => false,
            ]);
    }
}
