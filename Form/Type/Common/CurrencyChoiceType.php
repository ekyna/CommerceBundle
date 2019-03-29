<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CurrencyChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $currencyClass;

    /**
     * @var string
     */
    private $defaultCode;


    /**
     * Constructor.
     *
     * @param string $currencyClass
     * @param string $defaultCode
     */
    public function __construct($currencyClass, $defaultCode)
    {
        $this->currencyClass = $currencyClass;
        $this->defaultCode = $defaultCode;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'             => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return 'ekyna_commerce.currency.label.' . ($options['multiple'] ? 'plural' : 'singular');
            },
            'class'             => $this->currencyClass,
            'choice_value'      => 'code',
            'query_builder'     => function (EntityRepository $er) {
                $qb = $er->createQueryBuilder('o');

                return $qb->andWhere($qb->expr()->eq('o.enabled', true));
            },
            'preferred_choices' => function (CurrencyInterface $currency) {
                return $currency->getCode() === $this->defaultCode;
            },
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
