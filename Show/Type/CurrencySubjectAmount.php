<?php

namespace Ekyna\Bundle\CommerceBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Component\Commerce\Common\Model\CurrencySubjectInterface;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CurrencySubjectAmount
 * @package Ekyna\Bundle\CommerceBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencySubjectAmount extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        parent::build($view, $value, $options);

        $view->vars['subject'] = $options['subject'];
        $view->vars['quote'] = $options['quote'];
        $view->vars['base'] = $options['base'];
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('subject')
            ->setDefault('quote', true)
            ->setDefault('base', true)
            ->setAllowedTypes('subject', [ExchangeSubjectInterface::class, CurrencySubjectInterface::class])
            ->setAllowedTypes('quote', 'bool')
            ->setAllowedTypes('base', 'bool');
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'commerce_currency_subject_amount';
    }
}
