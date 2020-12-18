<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface as BaseInterface;

/**
 * Interface SubjectHelperInterface
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectHelperInterface extends BaseInterface
{
    /**
     * Returns the subject 'add to cart' url.
     *
     * @param Reference|Subject $subject
     * @param bool              $path
     *
     * @return null|string
     */
    public function generateAddToCartUrl($subject, bool $path = true): ?string;

    /**
     * Returns the subject 'resupply alert' url.
     *
     * @param Reference|Subject $subject
     * @param bool              $path
     *
     * @return null|string
     */
    public function generateResupplyAlertUrl($subject, bool $path = true): ?string;

    /**
     * Returns the subject public url.
     *
     * @param Reference|Subject $subject
     * @param bool              $path
     *
     * @return null|string
     */
    public function generatePublicUrl($subject, bool $path = true): ?string;

    /**
     * Returns the subject image url.
     *
     * @param Reference|Subject $subject
     * @param bool              $path
     *
     * @return null|string
     */
    public function generateImageUrl($subject, bool $path = true): ?string;

    /**
     * Returns the subject private url.
     *
     * @param Reference|Subject $subject
     * @param bool              $path
     *
     * @return null|string
     */
    public function generatePrivateUrl($subject, bool $path = true): ?string;
}
