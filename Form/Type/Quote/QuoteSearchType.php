<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class QuoteSearchType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteSearchType extends AbstractType
{
    /**
     * @var string
     */
    private $quoteClass;


    /**
     * Constructor.
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->quoteClass = $class;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'    => function (Options $options, $value) {
                    if (null !== $value) {
                        return $value;
                    }

                    return 'ekyna_commerce.quote.label.' . ($options['multiple'] ? 'plural' : 'singular');
                },
                'class'    => $this->quoteClass,
                'route'    => 'ekyna_commerce_quote_admin_search',
                'required' => false,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return EntitySearchType::class;
    }
}
