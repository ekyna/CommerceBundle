<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Builder\InvoiceBuilderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
     * @var LockChecker
     */
    private $lockChecker;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;


    /**
     * Constructor.
     *
     * @param InvoiceBuilderInterface       $builder
     * @param LockChecker                   $lockChecker
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $dataClass
     */
    public function __construct(
        InvoiceBuilderInterface $builder,
        LockChecker $lockChecker,
        AuthorizationCheckerInterface $authorizationChecker,
        string $dataClass
    ) {
        parent::__construct($dataClass);

        $this->builder = $builder;
        $this->lockChecker = $lockChecker;
        $this->authorizationChecker = $authorizationChecker;
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
            ->add('comment', Type\TextareaType::class, [
                'label'    => 'ekyna_core.field.comment',
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_commerce.field.description',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
                $invoice = $event->getData();

                if (null === $sale = $invoice->getSale()) {
                    throw new RuntimeException("The invoice must be associated with a sale at this point.");
                }
                if (!$sale instanceof OrderInterface) {
                    throw new RuntimeException("Not yet supported.");
                }

                $locked = $this->lockChecker->isLocked($invoice)
                    || !$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');

                $form->add('createdAt', Type\DateTimeType::class, [
                    'label'      => 'ekyna_core.field.date',
                    'required'   => false,
                    'disabled'   => $locked,
                    'empty_data' => (new \DateTime())->format('d/m/Y H:i') // TODO Use the proper format !
                ]);

                if ($invoice->isCredit()) {
                    $form->add('ignoreStock', Type\CheckboxType::class, [
                        'label'    => 'ekyna_commerce.invoice.field.ignore_stock',
                        'required' => false,
                        'disabled' => $locked,
                        'attr'     => [
                            'align_with_widget' => true,
                            'help_text'         => 'ekyna_commerce.invoice.help.ignore_stock',
                        ],
                    ]);
                }

                $disabledLines = true;
                if (null === $invoice->getShipment()) {
                    $form->add('items', InvoiceItemsType::class, [
                        'entry_type'    => $options['item_type'],
                        'entry_options' => [
                            'invoice' => $invoice,
                        ],
                    ]);

                    $this->builder->build($invoice);

                    $disabledLines = false;
                }

                $form->add('lines', InvoiceTreeType::class, [
                    'invoice'    => $invoice,
                    'entry_type' => $options['line_type'],
                    'disabled'   => $locked || $disabledLines,
                ]);
            });
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $form->getData();

        $view->vars['credit_mode'] = $invoice->isCredit();

        FormUtil::addClass($view, 'invoice');
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired([
                'line_type',
                'item_type',
            ])
            ->setAllowedTypes('line_type', 'string')
            ->setAllowedTypes('item_type', 'string');
    }
}
