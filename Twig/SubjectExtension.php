<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelper;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;

/**
 * Class SubjectExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectExtension extends \Twig_Extension
{
    /**
     * @var SubjectHelper
     */
    private $subjectHelper;


    /**
     * Constructor.
     *
     * @param SubjectHelper $subjectHelper
     */
    public function __construct(SubjectHelper $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'subject_admin_link',
                [$this, 'renderSubjectAdminLink'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Renders the subject relative's subject admin link.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return string
     */
    public function renderSubjectAdminLink(SubjectRelativeInterface $relative)
    {
        if (null !== $subject = $this->subjectHelper->resolve($relative, false)) {
            if (null !== $url = $this->subjectHelper->generateSubjectAdminUrl($relative)) {
                return sprintf(
                    '<a href="%s" class="show-entity">%s</a>',
                    $url,
                    (string) $subject
                );
            }

            return (string) $subject;
        }

        return 'Undefined'; // TODO translation
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_subject';
    }
}
