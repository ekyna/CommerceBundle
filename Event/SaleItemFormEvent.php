<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class SaleItemFormEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemFormEvent extends SaleItemEvent
{
    const BUILD_FORM = 'ekyna_commerce.sale_item.build_form';
    const BUILD_VIEW = 'ekyna_commerce.sale_item.build_view';

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var FormView
     */
    private $view;


    /**
     * Constructor.
     *
     * @param SaleItemInterface $item
     * @param FormInterface     $form
     * @param FormView          $view
     */
    public function __construct(SaleItemInterface $item, FormInterface $form, FormView $view = null)
    {
        parent::__construct($item);

        $this->form = $form;
        $this->view = $view;
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

    /**
     * Returns the view.
     *
     * @return FormView
     */
    public function getView()
    {
        return $this->view;
    }
}
