<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class VatNumberType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VatNumberType extends AbstractType
{
    private UrlGeneratorInterface $urlGenerator;
    private Environment           $templating;


    public function __construct(UrlGeneratorInterface $urlGenerator, Environment $templating)
    {
        $this->urlGenerator = $urlGenerator;
        $this->templating = $templating;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['admin_mode'] || $form->getParent()->getConfig()->getOption('admin_mode')) {
            $config = [
                'path'       => $this->urlGenerator->generate('api_ekyna_commerce_pricing_validate_vat'),
                'checkbox'   => $options['valid_checkbox'],
                'lastNumber' => null,
                'lastResult' => null,
            ];

            /** @var CustomerInterface $customer */
            if (null !== $customer = $form->getParent()->getData()) {
                $config['lastNumber'] = $customer->getVatNumber();

                if ($customer->isVatValid()) {
                    $result = ['valid' => true];
                    if (!empty($details = $customer->getVatDetails())) {
                        $result['content'] = $this->templating->render('@EkynaCommerce/Admin/Common/vat_details.html.twig', [
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'          => t('pricing.field.vat_number', [], 'EkynaCommerce'),
                'required'       => false,
                'valid_checkbox' => '#customer_vatValid',
            ])
            ->setAllowedTypes('valid_checkbox', ['string', 'null']);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_vat_number';
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }
}
