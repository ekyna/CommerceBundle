<?php

namespace Ekyna\Bundle\CommerceBundle\Validator\Constraints;

use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Payum\Core\Registry\RegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PaymentMethodValidator
 * @package Ekyna\Bundle\CommerceBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodValidator extends ConstraintValidator
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
     * @inheritDoc
     */
    public function validate($method, Constraint $constraint)
    {
        if (!$method instanceof PaymentMethodInterface) {
            throw new UnexpectedTypeException($method, PaymentMethodInterface::class);
        }
        if (!$constraint instanceof PaymentMethod) {
            throw new UnexpectedTypeException($constraint, PaymentMethod::class);
        }
        
        if (empty($factory = $method->getFactoryName())) {
            return;
        }

        $gatewayFactory = $this->registry->getGatewayFactory($factory);
        $defaults = $gatewayFactory->createConfig();

        if (empty($options = $defaults['payum.default_options'])) {
            return;
        }

        if (empty($required = $defaults['payum.required_options'])) {
            return;
        }

        $config = $method->getConfig();

        $isValid = function(array $data, string $name) {
            if (!array_key_exists($name, $data)) {
                return false;
            }

            // If defined value is non empty string
            if (is_string($data[$name])) {
                if (!empty($data[$name])) {
                    return true;
                }
            }
            // else (not string) if defined value is not null
            elseif (!is_null($data[$name])){
                return true;
            }

            return false;
        };

        foreach ($options as $name => $value) {
            // Skip values added by the getter.
            /** @see PaymentMethodInterface::getConfig() */
            if (in_array($name, ['factory', 'gateway'], true)) {
                continue;
            }

            // Skip non require values.
            if (!in_array($name, $required, true)) {
                continue;
            }

            // If config value is valid
            if ($isValid($config, $name)) {
                continue;
            }

            // If default value is valid
            if ($isValid($defaults, $name)) {
                continue;
            }

            $this
                ->context
                ->buildViolation('This value should not be blank.')
                ->atPath("config[$name]")
                ->addViolation();
        }
    }
}
