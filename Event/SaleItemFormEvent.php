<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class SaleItemFormEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemFormEvent extends SaleItemEvent
{
    const BUILD_FORM = 'ekyna_commerce.sale_item.build_form';

    /**
     * @var FormInterface
     */
    private $form;


    /**
     * Constructor.
     *
     * @param SaleItemInterface $item
     * @param FormInterface     $form
     */
    public function __construct(SaleItemInterface $item, FormInterface $form)
    {
        parent::__construct($item);

        $this->form = $form;
    }

    /**
     * Returns the form.
     *
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
