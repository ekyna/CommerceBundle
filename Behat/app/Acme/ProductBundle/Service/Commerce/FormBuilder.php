<?php

namespace Acme\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Builder\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as Sf;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FormBuilder implements FormBuilderInterface
{
    /**
     * @var ProductProvider
     */
    private $provider;


    /**
     * Constructor.
     *
     * @param ProductProvider $provider
     */
    public function __construct(ProductProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Build the product choice form.
     *
     * @param FormInterface $form
     *
     * @deprecated Use SubjectChoiceType
     */
    public function buildChoiceForm(FormInterface $form)
    {
        //throw new \Exception('This method must be removed.');

        $form->add('subject', ResourceSearchType::class, [
            'label'    => false,
            'class'    => $this->provider->getSubjectClass(),
            'required' => false,
        ]);
    }

    /**
     * Build the product item form.
     *
     * @param FormInterface     $form
     * @param SaleItemInterface $item
     */
    public function buildItemForm($form, SaleItemInterface $item)
    {
        if (!$form instanceof FormInterface) {
            throw new \InvalidArgumentException('Expected form as instance of ' . FormInterface::class);
        }

        // Quantity
        $form->add('quantity', Sf\IntegerType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr'  => [
                'min' => 1,
            ],
        ]);
    }
}
