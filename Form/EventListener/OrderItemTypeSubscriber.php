<?php

namespace Ekyna\Bundle\CommerceBundle\Form\EventListener;

use Ekyna\Bundle\CommerceBundle\Helper\SubjectHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class OrderItemTypeSubscriber
 * @package Ekyna\Bundle\CommerceBundle\Form\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemTypeSubscriber implements EventSubscriberInterface
{
    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;

    /**
     * @var array
     */
    private $fields;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     * @param array                  $fields
     */
    public function __construct(SubjectHelperInterface $subjectHelper, array $fields)
    {
        $this->subjectHelper = $subjectHelper;
        $this->fields = $fields;
    }

    /**
     * Form pre set data event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $item = $event->getData();

        foreach ($this->fields as $field) {
            list ($name, $type, $options) = $field;
            if (null !== $item) {
                $options = array_replace($options, $this->subjectHelper->getFormOptions($item, $name));
            }
            $form->add($name, $type, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData', 0],
        ];
    }
}
