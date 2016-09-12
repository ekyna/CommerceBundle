<?php

namespace Ekyna\Bundle\CommerceBundle\Form;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;

/**
 * Class AddSubjectFlow
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddItemFlow extends FormFlow
{
    /**
     * @var SubjectProviderRegistryInterface
     */
    private $providerRegistry;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $providerRegistry
     */
    public function __construct(SubjectProviderRegistryInterface $providerRegistry)
    {
        $this->providerRegistry = $providerRegistry;
    }

    /**
     * @inheritdoc
     */
    protected function loadStepsConfig()
    {
        return [
            [
                'label'        => 'provider',
                'form_type'    => Type\SubjectProviderType::class,
                'form_options' => [
                    'provider_registry' => $this->providerRegistry,
                    'validation_groups' => ['add_item_flow_step_1'],
                ],
            ],
            [
                'label'        => 'choice',
                'form_type'    => Type\SubjectChoiceType::class,
                'form_options' => [
                    'provider_registry' => $this->providerRegistry,
                    'validation_groups' => ['add_item_flow_step_2'],
                ],
                'skip'         => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    if ($estimatedCurrentStepNumber < 2) {
                        return false;
                    }
                    /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
                    $item = $flow->getFormData();

                    return !$this->providerRegistry
                        ->getProvider($item)
                        ->needChoice($item);
                },
            ],
            [
                'label'        => 'configure',
                'form_type'    => Type\SaleItemSubjectType::class,
                'form_options' => [
                    'validation_groups' => ['add_item_flow_step_3'],
                ],
            ],
        ];
    }
}
