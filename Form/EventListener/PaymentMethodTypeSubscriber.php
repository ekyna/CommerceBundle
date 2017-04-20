<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\EventListener;

use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Payum\Core\Registry\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_null;
use function is_numeric;
use function is_string;
use function Symfony\Component\Translation\t;

/**
 * Class PaymentMethodTypeSubscriber
 * @package Ekyna\Bundle\CommerceBundle\Form\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodTypeSubscriber implements EventSubscriberInterface
{
    private RegistryInterface $registry;


    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function buildConfigField(FormEvent $event): void
    {
        $data = $event->getData();
        if (is_null($data)) {
            return;
        }

        $propertyPath = is_array($data) ? '[factory_name]' : 'factoryName';
        $factoryName = PropertyAccess::createPropertyAccessor()->getValue($data, $propertyPath);
        if (empty($factoryName)) {
            return;
        }

        $gatewayFactory = $this->registry->getGatewayFactory($factoryName);
        $config = $gatewayFactory->createConfig();

        if (empty($config['payum.default_options'])) {
            return;
        }

        $required = $config['payum.required_options'] ?? [];

        $form = $event->getForm();
        $form->add('config', Type\FormType::class, [
            'label' => t('field.config', [], 'EkynaUi'),
        ]);

        $configForm = $form->get('config');

        $isRequired = function ($name) use ($config, $required) {
            if (!in_array($name, $required, true)) {
                return false;
            }
            // If defined value is non empty string
            if (is_string($config[$name])) {
                if (!empty($config[$name])) {
                    return false;
                }
            } // If defined value is not null
            elseif (!is_null($config[$name])) {
                return false;
            }

            return true;
        };

        $propertyPath = is_array($data) ? '[config]' : 'config';
        $firstTime = false == PropertyAccess::createPropertyAccessor()->getValue($data, $propertyPath);
        foreach ($config['payum.default_options'] as $name => $value) {
            $propertyPath = is_array($data) ? "[config][$name]" : "config[$name]";
            if ($firstTime) {
                PropertyAccess::createPropertyAccessor()->setValue($data, $propertyPath, $value);
            }

            $type = Type\TextType::class;
            $options = [
                'required' => $isRequired($name),
            ];
            if (is_bool($value)) {
                $type = Type\CheckboxType::class;
                $options = [
                    'required' => false,
                    'attr'     => ['align_with_widget' => true],
                ];
            } elseif (is_numeric($value)) {
                $type = is_float($value) ? Type\NumberType::class : Type\IntegerType::class;
            } elseif (is_array($value)) {
                continue;
            }

            $configForm->add($name, $type, $options);
        }

        $event->setData($data);
    }

    public function removeEmptyConfig(FormEvent $event): void
    {
        /** @var PaymentMethodInterface $data */
        $data = $event->getData();

        $config = $data->getConfig();
        unset($config['factory']);

        foreach ($config as $key => $value) {
            if (empty($value)) {
                unset($config[$key]);
            }
        }

        $data->setConfig($config);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'buildConfigField',
            FormEvents::PRE_SUBMIT   => 'buildConfigField',
            FormEvents::POST_SUBMIT  => ['removeEmptyConfig', 2048],
        ];
    }
}
