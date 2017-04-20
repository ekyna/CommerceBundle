<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class GenderChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class GenderChoiceType extends AbstractType
{
    private string $genderClass;


    public function __construct(string $genderClass)
    {
        $this->genderClass = $genderClass;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'inline');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'              => t('field.gender', [], 'EkynaUi'),
            'translation_domain' => false,
            'class'              => $this->genderClass,
            'select2'            => false,
        ]);
    }

    public function getParent(): ?string
    {
        return ConstantChoiceType::class;
    }
}
