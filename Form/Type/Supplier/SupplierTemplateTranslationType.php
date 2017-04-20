<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierTemplateTranslationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierTemplateTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label'    => t('field.subject', [], 'EkynaUi'),
                'required' => true,
            ])
            ->add('message', TinymceType::class, [
                'label'    => t('field.message', [], 'EkynaUi'),
                'theme'    => 'front',
                'required' => true,
                'attr'     => [
                    'help_text' => t('supplier_template.help.message', [], 'EkynaCommerce'),
                ],
            ]);
    }
}
