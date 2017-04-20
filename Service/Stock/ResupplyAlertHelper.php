<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Stock\ResupplyAlertSubscribeType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Stock\ResupplyAlertUnsubscribeType;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Stock\Helper\ResupplyAlertHelper as BaseHelper;
use Ekyna\Component\Commerce\Stock\Repository\ResupplyAlertRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use LogicException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ResupplyAlertHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Stock
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResupplyAlertHelper extends BaseHelper
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FormInterface
     */
    private $subscribeForm;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var SubjectInterface
     */
    private $subject;

    /**
     * @var FormInterface
     */
    private $unsubscribeForm;


    /**
     * Constructor.
     *
     * @param ResupplyAlertRepositoryInterface $repository
     * @param EntityManagerInterface           $manager
     * @param SubjectHelperInterface           $helper
     * @param FormFactoryInterface             $formFactory
     * @param UrlGeneratorInterface            $urlGenerator
     */
    public function __construct(
        ResupplyAlertRepositoryInterface $repository,
        EntityManagerInterface $manager,
        SubjectHelperInterface $helper,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($repository, $manager, $helper);

        $this->formFactory  = $formFactory;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Initialize the helper.
     *
     * @param Request $request
     */
    public function initialize(Request $request): void
    {
        $this->subject = $this->helper->find(
            $request->attributes->get('provider'),
            $request->attributes->get('identifier')
        );
    }

    /**
     * Resets the helper.
     */
    private function reset(): void
    {
        $this->subject         = null;
        $this->subscribeForm   = null;
        $this->unsubscribeForm = null;
    }

    /**
     * Creates the subscribe form.
     *
     * @param array $data
     * @param array $options
     *
     * @return FormInterface
     */
    public function createSubscribeForm(array $data, array $options = []): FormInterface
    {
        if (null === $this->subject) {
            throw new LogicException("Call 'initialize' method first.");
        }

        $options = array_replace([
            'action' => $this->urlGenerator->generate('ekyna_commerce_subject_resupply_alert_subscribe', [
                'provider'   => $this->subject->getProviderName(),
                'identifier' => $this->subject->getIdentifier(),
            ]),
            'method' => 'POST',
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ], $options);

        return $this->subscribeForm = $this
            ->formFactory
            ->create(ResupplyAlertSubscribeType::class, $data, $options);
    }

    /**
     * Handles the subscribe form.
     *
     * @param Request $request
     *
     * @return bool|null NULL if not valid and submitted, TRUE on subscribe, FALSE if already subscribed
     */
    public function handleSubscriptionForm(Request $request): ?bool
    {
        if (null === $this->subscribeForm) {
            throw new LogicException("Call 'createSubscribeForm' method first.");
        }

        $this->subscribeForm->handleRequest($request);

        if (!($this->subscribeForm->isSubmitted() && $this->subscribeForm->isValid())) {
            return null;
        }

        $email = $this->subscribeForm->get('email')->getData();

        $result = $this->subscribe($email, $this->subject);

        $this->reset();

        return $result;
    }

    /**
     * Creates the unsubscribe form.
     *
     * @param array $data
     * @param array $options
     *
     * @return FormInterface
     */
    public function createUnsubscribeForm(array $data, array $options = []): FormInterface
    {
        if (null === $this->subject) {
            throw new LogicException("Call 'initialize' method first.");
        }

        $options = array_replace([
            'action'  => $this->urlGenerator->generate('ekyna_commerce_subject_resupply_alert_unsubscribe', [
                'provider'   => $this->subject->getProviderName(),
                'identifier' => $this->subject->getIdentifier(),
            ]),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'buttons' => false,
        ], $options);

        return $this->unsubscribeForm = $this
            ->formFactory
            ->create(ResupplyAlertUnsubscribeType::class, $data, $options);
    }

    /**
     * Handles the unsubscribe form.
     *
     * @param Request $request
     *
     * @return bool|null NULL if not valid and submitted, TRUE on unsubscribe, FALSE if not subscribed
     */
    public function handleUnsubscriptionForm(Request $request): ?bool
    {
        if (null === $this->unsubscribeForm) {
            throw new LogicException("Call 'createUnsubscribeForm' method first.");
        }

        $this->unsubscribeForm->handleRequest($request);

        if (!($this->unsubscribeForm->isSubmitted() && $this->unsubscribeForm->isValid())) {
            return null;
        }

        $email = $this->unsubscribeForm->get('email')->getData();

        $result = $this->unsubscribe($email, $this->subject);

        $this->reset();

        return $result;
    }
}
