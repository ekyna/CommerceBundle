<?php

declare(strict_types=1);

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
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        $view->vars['subject'] = $options['subject'];
        $view->vars['quote'] = $options['quote'];
        $view->vars['base'] = $options['base'];
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('subject')
            ->setDefaults([
                'label_trans_domain' => 'EkynaCommerce',
                'quote'              => true,
                'base'               => true,
            ])
            ->setAllowedTypes('subject', [ExchangeSubjectInterface::class, CurrencySubjectInterface::class])
            ->setAllowedTypes('quote', 'bool')
            ->setAllowedTypes('base', 'bool');
    }

    public static function getName(): string
    {
        return 'commerce_amount';
    }
}
