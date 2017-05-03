<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Credit;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Credit\Builder\CreditBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class CreditType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Credit
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreditType extends ResourceFormType
{
    /**
     * @var string
     */
    private $itemClass;

    /**
     * @var CreditBuilderInterface
     */
    private $creditBuilder;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $itemClass
     */
    public function __construct($dataClass, $itemClass)
    {
        parent::__construct($dataClass);

        $this->itemClass = $itemClass;
    }

    /**
     * Sets the credit builder.
     *
     * @param CreditBuilderInterface $creditBuilder
     */
    public function setCreditBuilder(CreditBuilderInterface $creditBuilder)
    {
        $this->creditBuilder = $creditBuilder;
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
                'label'    => 'ekyna_core.field.description',
                'required' => false,
            ])
            ->add('items', CreditItemsType::class, [
                'label'         => 'Items', // TODO
                'entry_options' => [
                    'data_class' => $this->itemClass,
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Credit\Model\CreditInterface $credit */
            $credit = $event->getData();

            if (null === $sale = $credit->getSale()) {
                throw new RuntimeException("The credit must be associated with a sale at this point.");
            }
            if (!$sale instanceof OrderInterface) {
                throw new RuntimeException("Not yet supported.");
            }

            if (0 === $credit->getItems()->count()) {
                $this->creditBuilder->build($credit);
            }
        });
    }
}
