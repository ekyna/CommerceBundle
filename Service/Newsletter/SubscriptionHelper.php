<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Newsletter;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter\SubscriptionType as SubscriptionFormType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Table\Type\SubscriptionType as SubscriptionTableType;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Newsletter\Event\MemberEvents;
use Ekyna\Component\Commerce\Newsletter\Event\SubscriptionEvents;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayRegistry;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\NewsletterSubscription;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\MemberRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\SubscriptionRepositoryInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Table\Extension\Core\Source\ArraySource;
use Ekyna\Component\Table\FactoryInterface;
use Ekyna\Component\Table\View\TableView;
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
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FactoryInterface
     */
    private $tableFactory;

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
     * @param SubscriptionRepositoryInterface  $subscriptionRepository
     * @param FormFactoryInterface             $formFactory
     * @param FactoryInterface                 $tableFactory
     * @param GatewayRegistry                  $gatewayRegistry
     * @param ResourceEventDispatcherInterface $dispatcher
     * @param ValidatorInterface               $validator
     * @param EntityManagerInterface           $manager
     * @param EngineInterface                  $engine
     */
    public function __construct(
        AudienceRepositoryInterface $audienceRepository,
        MemberRepositoryInterface $memberRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        FormFactoryInterface $formFactory,
        FactoryInterface $tableFactory,
        GatewayRegistry $gatewayRegistry,
        ResourceEventDispatcherInterface $dispatcher,
        ValidatorInterface $validator,
        EntityManagerInterface $manager,
        EngineInterface $engine
    ) {
        $this->audienceRepository     = $audienceRepository;
        $this->memberRepository       = $memberRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->formFactory            = $formFactory;
        $this->tableFactory           = $tableFactory;
        $this->gatewayRegistry        = $gatewayRegistry;
        $this->dispatcher             = $dispatcher;
        $this->validator              = $validator;
        $this->manager                = $manager;
        $this->engine                 = $engine;
    }

    /**
     * Returns the subscription form.
     *
     * @return FormInterface|null
     */
    public function getSubscriptionForm(): ?FormInterface
    {
        if ($this->form) {
            return $this->form;
        }

        try {
            $audience = $this->audienceRepository->findDefault();
        } catch (CommerceExceptionInterface $e) {
            return null;
        }

        $data = new NewsletterSubscription();

        $data->addAudience($audience);

        return $this->form = $this->formFactory->create(SubscriptionFormType::class, $data);
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
        if (!$form = $this->getSubscriptionForm()) {
            return false;
        }

        $form->handleRequest($request);

        $success = false;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var NewsletterSubscription $data */
            $data = $form->getData();

            if (!$member = $this->memberRepository->findOneByEmail($data->getEmail())) {
                $member = $this->memberRepository->createNew();
                $member->setEmail($data->getEmail());

                // Pre create the member
                $event = $this->dispatch(MemberEvents::PRE_CREATE, $member);
                if ($event->hasErrors()) {
                    foreach ($event->getErrors() as $error) {
                        $form->addError(new FormError($error->getMessage()));
                    }

                    return false;
                }
            }

            foreach ($data->getAudiences() as $audience) {
                if (!$subscription = $member->getSubscription($audience)) {
                    $subscription = $this->subscriptionRepository->createNew();
                    $subscription
                        ->setAudience($audience)
                        ->setMember($member);
                }

                $subscription->setStatus(SubscriptionStatus::SUBSCRIBED);

                // Create member through gateway
                $gateway = $this->gatewayRegistry->get($audience->getGateway());
                if ($gateway->supports(GatewayInterface::CREATE_SUBSCRIPTION)) {
                    $gateway->createSubscription($subscription, $data);
                }

                $this->manager->persist($subscription);

                $success = true;
            }

            $this->manager->persist($member);

            try {
                $this->manager->flush();
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (NewsletterException $e) {
                $form->addError(new FormError('The server encountered an error.'));

                return false;
            }
        }

        return $success;
    }

    /**
     * Renders the member subscriptions.
     *
     * @param MemberInterface $member
     *
     * @return TableView
     */
    public function renderMemberSubscriptions(MemberInterface $member): TableView
    {
        $table = $this
            ->tableFactory
            ->createTable('member_subscriptions', SubscriptionTableType::class, [
                'source' => new ArraySource($member->getSubscriptions()->toArray()),
            ]);

        $table->handleRequest();

        return $table->createView();
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

        $member = $this->memberRepository->findOneByEmail($parameters['email']);

        $audiences = [];
        foreach ($this->findAudiences($parameters['public']) as $audience) {
            $datum = [
                'key'        => $audience->getKey(),
                'title'      => $audience->getTitle(),
                'subscribed' => false,
            ];

            if (!$member) {
                $audiences[] = $datum;
                continue;
            }

            if (!$subscription = $member->getSubscription($audience)) {
                $audiences[] = $datum;
                continue;
            }

            if ($subscription->getStatus() === SubscriptionStatus::SUBSCRIBED) {
                $datum['subscribed'] = true;
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
        // Find audience
        $audience = $this->audienceRepository->findOneByKey($key);
        if (!$audience) {
            // Audience not found
            return [
                'success' => false,
                'errors'  => [
                    $key => 'Audience not found',
                ],
            ];
        }

        // Find member
        $member = $this->memberRepository->findOneByEmail($email);
        if (!$member) {
            // Member not found -> create member
            $member = $this->memberRepository->createNew();
            $member->setEmail($email);

            $event = $this->dispatch(MemberEvents::PRE_CREATE, $member);

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
                        'global' => implode(' ', $error),
                    ],
                ];
            }
        }

        // Find subscription
        $subscription = $member->getSubscription($audience);
        if (!$subscription) {
            // subscription not found -> create subscription
            $subscription = $this->subscriptionRepository->createNew();
            $subscription
                ->setAudience($audience)
                ->setMember($member);

            $event = $this->dispatch(SubscriptionEvents::PRE_CREATE, $subscription);
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
                        'global' => implode(' ', $error),
                    ],
                ];
            }
        }

        // If subscribed
        if ($subscription->getStatus() === SubscriptionStatus::SUBSCRIBED) {
            return [
                'success' => true,
            ];
        }

        // Set status to subscribed
        $subscription->setStatus(SubscriptionStatus::SUBSCRIBED);

        // Validate member
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
        $success = [
            'success' => true,
        ];

        $audience = $this->audienceRepository->findOneByKey($key);

        if (!$audience) {
            return $success;
        }

        $member = $this->memberRepository->findOneByEmail($email);

        if (!$member) {
            return $success;
        }

        if (!$subscription = $member->getSubscription($audience)) {
            return $success;
        }

        if ($subscription->getStatus() === SubscriptionStatus::UNSUBSCRIBED) {
            return $success;
        }

        $subscription->setStatus(SubscriptionStatus::UNSUBSCRIBED);

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

        return $success;
    }

    /**
     * Returns the audiences.
     *
     * @param bool $public
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
