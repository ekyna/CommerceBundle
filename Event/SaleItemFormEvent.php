<?php

declare(strict_types=1);

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
    public const BUILD_FORM = 'ekyna_commerce.sale_item.build_form';
    public const BUILD_VIEW = 'ekyna_commerce.sale_item.build_view';

    private FormInterface $form;
    private ?FormView     $view;


    public function __construct(SaleItemInterface $item, FormInterface $form, ?FormView $view)
    {
        parent::__construct($item);

        $this->form = $form;
        $this->view = $view;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getView(): ?FormView
    {
        return $this->view;
    }
}
