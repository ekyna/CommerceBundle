<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class RelativeSubjectType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelativeSubjectDataType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addModelTransformer(new CallbackTransformer(
                // Transform
                function($data) {
                    $data = is_array($data) ? $data : [];

                    return serialize($data);
                },
                // Reverse transform
                function($data) {
                    if (0 < strlen($data)) {
                        $data = unserialize($data);
                    }

                    return $data;
                }
            ));
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return HiddenType::class;
    }
}
