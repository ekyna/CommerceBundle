<?php

namespace Ekyna\Bundle\CommerceBundle\Form\EventListener;

use Payum\Core\Registry\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class PaymentMethodTypeSubscriber
 * @package Ekyna\Bundle\CommerceBundle\Form\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodTypeSubscriber implements EventSubscriberInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * Constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Builds the config field.
     *
     * @param FormEvent $event
     */
    public function buildConfigField(FormEvent $event)
    {
        /** @var array $data */
        $data = $event->getData();
        if (is_null($data)) {
            return;
        }

        $propertyPath = is_array($data) ? '[factoryName]' : 'factoryName';
        $factoryName = PropertyAccess::createPropertyAccessor()->getValue($data, $propertyPath);
        if (empty($factoryName)) {
            return;
        }

        $paymentFactory = $this->registry->getGatewayFactory($factoryName);
        $config = $paymentFactory->createConfig();

        if (empty($config['payum.default_options'])) {
            return;
        }

        $form = $event->getForm();
        $form->add('config', 'form', [
            'label' => 'ekyna_core.field.config',
        ]);
        $configForm = $form->get('config');

        $propertyPath = is_array($data) ? '[config]' : 'config';
        $firstTime = false == PropertyAccess::createPropertyAccessor()->getValue($data, $propertyPath);
        foreach ($config['payum.default_options'] as $name => $value) {
            $propertyPath = is_array($data) ? "[config][$name]" : "config[$name]";
            if ($firstTime) {
                PropertyAccess::createPropertyAccessor()->setValue($data, $propertyPath, $value);
            }

            $type = 'text';
            $options = array();
            if (is_bool($value)) {
                $type = 'checkbox';
                $options['attr'] = ['align_with_widget' => true];
            } elseif(is_numeric($value)) {
                $type = is_float($value) ? 'number' : 'integer';
            } elseif (is_array($value)) {
                continue;
            }

            $options['required'] = in_array($name, $config['payum.required_options']);

            $configForm->add($name, $type, $options);
        }

        $event->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'buildConfigField',
            FormEvents::PRE_SUBMIT   => 'buildConfigField',
        ];
    }
}
