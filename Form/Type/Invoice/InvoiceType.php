<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\InvoiceLinesDataTransformer;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Invoice\Builder\InvoiceBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceType extends ResourceFormType
{
    /**
     * @var InvoiceBuilderInterface
     */
    private $builder;


    /**
     * Constructor.
     *
     * @param InvoiceBuilderInterface $builder
     * @param string                  $dataClass
     */
    public function __construct(InvoiceBuilderInterface $builder, $dataClass)
    {
        parent::__construct($dataClass);

        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'required' => false,
                'disabled' => true,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_commerce.field.description',
                'required' => false,
            ])
            ->add('lines', InvoiceLinesType::class, [
                'entry_type' => $options['line_type'],
            ])
            ->addModelTransformer(new InvoiceLinesDataTransformer($this->builder));
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'invoice');
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['line_type'])
            ->setAllowedTypes('line_type', 'string');
    }
}
