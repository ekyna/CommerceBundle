<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Form\Type\Subject\SubjectChoiceType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleItemSubjectType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemSubjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('subjectIdentity', SubjectChoiceType::class, [
            'context'   => $options['admin_mode']
                ? SubjectProviderInterface::CONTEXT_ITEM
                : SubjectProviderInterface::CONTEXT_ACCOUNT,
            'lock_mode' => true,
            'required'  => $options['required'],
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['admin_mode']) {
            FormUtil::addClass($view, 'commerce-sale-item-subject');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SaleItemInterface::class,
            'required'   => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_sale_item_subject';
    }
}
