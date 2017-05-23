<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class VatNumberType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VatNumberType extends AbstractType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var EngineInterface
     */
    private $templating;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param EngineInterface       $templating
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, EngineInterface $templating)
    {
        $this->urlGenerator = $urlGenerator;
        $this->templating = $templating;
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['admin_mode'] || $form->getParent()->getConfig()->getOption('admin_mode')) {
            $config = [
                'path'       => $this->urlGenerator->generate('ekyna_commerce_api_pricing_validate_vat'),
                'checkbox'   => $options['valid_checkbox'],
                'lastNumber' => null,
                'lastResult' => null,
            ];

            /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
            if (null !== $customer = $form->getParent()->getData()) {
                $config['lastNumber'] = $customer->getVatNumber();

                if ($customer->isVatValid()) {
                    $result = ['valid' => true];
                    if (!empty($details = $customer->getVatDetails())) {
                        $result['content'] = $this->templating->render('EkynaCommerceBundle:Admin/Common:vat_details.html.twig', [
                            'details' => $details,
                        ]);
                    }
                    $config['lastResult'] = $result;
                }
            }

            // default result
            $view->vars['config'] = $config;
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('valid_checkbox', '#customer_vatValid')
            ->setAllowedTypes('valid_checkbox', ['string', 'null']);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_vat_number';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return TextType::class;
    }
}
