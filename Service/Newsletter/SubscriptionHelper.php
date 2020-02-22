<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Newsletter;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter\SubscriptionType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Newsletter\Event\MemberEvents;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayRegistry;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberStatuses;
use Ekyna\Component\Commerce\Newsletter\Model\Subscription;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\MemberRepositoryInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SubscriptionHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Newsletter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionHelper
{
    /**
     * @var AudienceRepositoryInterface
     */
    private $audienceRepository;

    /**
     * @var MemberRepositoryInterface
     */
    private $memberRepository;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var GatewayRegistry
     */
    private $gatewayRegistry;

    /**
     * @var ResourceEventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var FormInterface
     */
    private $form;


    /**
     * Constructor.
     *
     * @param AudienceRepositoryInterface      $audienceRepository
     * @param MemberRepositoryInterface        $memberRepository
     * @param FormFactoryInterface             $formFactory
     * @param GatewayRegistry                  $gatewayRegistry
     * @param ResourceEventDispatcherInterface $dispatcher
     * @param ValidatorInterface               $validator
     * @param EntityManagerInterface           $manager
     * @param EngineInterface                  $engine
     */
    public function __construct(
        AudienceRepositoryInterface $audienceRepository,
        MemberRepositoryInterface $memberRepository,
        FormFactoryInterface $formFactory,
        GatewayRegistry $gatewayRegistry,
        ResourceEventDispatcherInterface $dispatcher,
        ValidatorInterface $validator,
        EntityManagerInterface $manager,
        EngineInterface $engine
    ) {
        $this->audienceRepository = $audienceRepository;
        $this->memberRepository   = $memberRepository;
        $this->formFactory        = $formFactory;
        $this->gatewayRegistry    = $gatewayRegistry;
        $this->dispatcher         = $dispatcher;
        $this->validator          = $validator;
        $this->manager            = $manager;
        $this->engine             = $engine;
    }

    /**
     * Returns the subscription form.
     *
     * @return FormInterface
     */
    public function getSubscriptionForm(): FormInterface
    {
        if ($this->form) {
            return $this->form;
        }

        return $this->form = $this->formFactory->create(SubscriptionType::class);
    }

    /**
     * Handles the subscription request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function handleSubscription(Request $request): bool
    {
        $form = $this->getSubscriptionForm();

        $form->handleRequest($request);

        $success = false;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Subscription $subscription */
            $subscription = $form->getData();

            foreach ($subscription->getAudiences() as $audience) {
                if (!$member = $this->memberRepository->findOneByAudienceAndEmail($audience,
                    $subscription->getEmail())) {
                    /** @var MemberInterface $member */
                    $member = $this->memberRepository->createNew();
                    $member->setAudience($audience);

                    // Initializes the member
                    $event = $this->dispatch(MemberEvents::INITIALIZE, $member);
                    if ($event->isPropagationStopped()) {
                        foreach ($event->getErrors() as $error) {
                            $form->addError(new FormError($error->getMessage()));
                        }

                        continue;
                    }
                }

                $member->setStatus(MemberStatuses::SUBSCRIBED);

                // Create member through gateway
                $gateway = $this->gatewayRegistry->get($audience->getGateway());
                if ($gateway->supports(GatewayInterface::CREATE_MEMBER)) {
                    $gateway->createMember($member, $subscription);
                }

                $this->manager->persist($member);

                $success = true;
            }

            try {
                $this->manager->flush();
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (NewsletterException $e) {
                $form->addError(new FormError('The server encoutered an error.'));

                return false;
            }
        }

        return $success;
    }

    /**
     * Renders the customer newsletter subscriptions.
     *
     * @param array $parameters
     *
     * @return string
     */
    public function renderCustomerSubscription(array $parameters = []): string
    {
        // TODO Only for customers

        $parameters = array_replace([
            'public'   => true,
            'customer' => null,
            'email'    => null,
            'template' => '@EkynaCommerce/Form/newsletter_customer_subscription.html.twig',
        ], $parameters);

        $customer = $parameters['customer'];
        if ($customer instanceof CustomerInterface) {
            $parameters['customer'] = $customer->getKey();
            $parameters['email']    = $customer->getEmail();
        }

        $audiences = [];
        foreach ($this->findAudiences($parameters['public']) as $audience) {
            $datum = [
                'key'        => $audience->getKey(),
                'title'      => $audience->getTitle(),
                'subscribed' => false,
            ];

            if (!empty($parameters['email'])) {
                $member = $this->memberRepository->findOneByAudienceAndEmail($audience, $parameters['email']);

                if ($member && $member->getStatus() === MemberStatuses::SUBSCRIBED) {
                    $datum['subscribed'] = true;
                }
            }

            $audiences[] = $datum;
        }

        $parameters['audiences'] = $audiences;

        return $this->engine->render($parameters['template'], $parameters);
    }

    /**
     * Renders the quick subscription form.
     *
     * @param array $parameters
     *
     * @return string|null
     */
    public function renderQuickSubscription(array $parameters = []): ?string
    {
        try {
            $audience = $this->audienceRepository->findDefault();
        } catch (RuntimeException $e) {
            return null;
        }

        $parameters = array_replace([
            'key'      => $audience->getKey(),
            'template' => '@EkynaCommerce/Form/newsletter_quick_subscription.html.twig',
        ], $parameters);

        return $this->engine->render($parameters['template'], $parameters);
    }

    /**
     * Subscribes the given customer to default audience.
     *
     * @param CustomerInterface $customer
     *
     * @return array
     */
    public function subscribeCustomerToDefaultAudiences(CustomerInterface $customer): array
    {
        try {
            $audience = $this->audienceRepository->findDefault();
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'errors'  => [
                    'global' => 'Audience not found',
                ],
            ];
        }

        return $this->subscribe($audience->getKey(), $customer->getEmail());
    }

    /**
     * Subscribes the given to the given audience (by its key).
     *
     * @param string $key   The audience key
     * @param string $email The email address
     *
     * @return array
     */
    public function subscribe(string $key, string $email): array
    {
        $audience = $this->audienceRepository->findOneByKey($key);

        if (!$audience) {
            return [
                'success' => false,
                'errors'  => [
                    $key => 'Audience not found',
                ],
            ];
        }

        $member = $this->memberRepository->findOneByAudienceAndEmail($audience, $email);

        if ($member && ($member->getStatus() === MemberStatuses::SUBSCRIBED)) {
            return [
                'success' => true,
            ];
        }

        if (!$member) {
            /** @var MemberInterface $member */
            $member = $this->memberRepository->createNew();
            $member
                ->setAudience($audience)
                ->setEmail($email);

            $event = $this->dispatch(MemberEvents::INITIALIZE, $member);

            if ($event->isPropagationStopped()) {
                $error = array_map(function (ResourceMessage $message) {
                    return $message->getMessage();
                }, $event->getErrors());

                if (empty($error)) {
                    $error = 'The server encountered an error.';
                }

                return [
                    'success' => false,
                    'errors'  => [
                        'global' => $error,
                    ],
                ];
            }
        }

        $member->setStatus(MemberStatuses::SUBSCRIBED);

        $list = $this->validator->validate($member);
        if ($list->count()) {
            $errors = [];
            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
            foreach ($list as $violation) {
                $index          = 'email' === $violation->getPropertyPath() ? 'email' : $key;
                $errors[$index] = $violation->getMessage();
            }

            return [
                'success' => false,
                'errors'  => $errors,
            ];
        }

        try {
            $this->manager->persist($member);
            $this->manager->flush();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors'  => [
                    'global' => 'The server encountered an error.',
                ],
            ];
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Subscribes the given to the given audience (by its key).
     *
     * @param string $key   The audience key
     * @param string $email The email address
     *
     * @return array
     */
    public function unsubscribe(string $key, string $email): array
    {
        $audience = $this->audienceRepository->findOneByKey($key);

        if (!$audience) {
            return [
                'success' => true,
            ];
        }

        $member = $this->memberRepository->findOneByAudienceAndEmail($audience, $email);

        if (!$member || ($member->getStatus() === MemberStatuses::UNSUBSCRIBED)) {
            return [
                'success' => true,
            ];
        }

        $member->setStatus(MemberStatuses::UNSUBSCRIBED);

        try {
            $this->manager->persist($member);
            $this->manager->flush();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors'  => [
                    'global' => 'The server encountered an error.',
                ],
            ];
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Returns the audiences.
     *
     * @return array
     */
    private function findAudiences(bool $public): array
    {
        if ($public) {
            return $this->audienceRepository->findPublic();
        }

        return $this->audienceRepository->findAll(); // TODO Sort by title
    }

    /**
     * Dispatches the resource event.
     *
     * @param string            $name
     * @param ResourceInterface $resource
     *
     * @return ResourceEventInterface
     */
    private function dispatch(string $name, ResourceInterface $resource): ResourceEventInterface
    {
        $event = new ResourceEvent();
        $event->setResource($resource);

        $this->dispatcher->dispatch($name, $event);

        return $event;
    }
}
