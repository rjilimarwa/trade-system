<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller\BackOffice;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class ConnectController extends ContainerAware
{
    protected function getResourceOwnerByName($name)
    {
        foreach ($this->container->getParameter('hwi_oauth.firewall_names') as $firewall) {
            $id = 'hwi_oauth.resource_ownermap.' . $firewall;
            if (!$this->container->has($id)) {
                continue;
            }
            $ownerMap = $this->container->get($id);
            if ($resourceOwner = $ownerMap->getResourceOwnerByName($name)) {
                return $resourceOwner;
            }
        }
        throw new \RuntimeException(sprintf("No resource owner with name '%s'.", $name));
    }

    public function registrationAction(Request $request, $key)
    {
        $connect = $this->container->getParameter('hwi_oauth.connect');
        if (!$connect) {
            throw new NotFoundHttpException();
        }

        $hasUser = $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED');
        if ($hasUser) {
            throw new AccessDeniedException('Cannot connect already registered account.');
        }

        $session = $request->getSession();
        $error = $session->get('_hwi_oauth.registration_error.' . $key);
        $session->remove('_hwi_oauth.registration_error.' . $key);

        if (!($error instanceof AccountNotLinkedException) || (time() - $key > 300)) {
            throw new \Exception('Cannot register an account.');
        }

        $userInformation = $this
            ->getResourceOwnerByName($error->getResourceOwnerName())
            ->getUserInformation($error->getRawToken());

        // enable compatibility with FOSUserBundle 1.3.x and 2.x
        if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
            $form = $this->container->get('hwi_oauth.registration.form.factory')->createForm();
        } else {
            $form = $this->container->get('hwi_oauth.registration.form');
        }

        $formHandler = $this->container->get('hwi_oauth.registration.form.handler');

        if ($formHandler->process($request, $form, $userInformation)) {
            // Connect user
            $this->container->get('hwi_oauth.account.connector')->connect($form->getData(), $userInformation);

            // Authenticate the user
            $this->authenticateUser($request, $form->getData(), $error->getResourceOwnerName(), $error->getRawToken());

            // Getting user
            $user = $this->container->get('security.context')->getToken()->getUser();

            // Getting social network source
            $source = $userInformation->getResourceOwner()->getName();

            // Updating user by source
            switch ($source) {
                case 'facebook':
                    $user = $this->handleFacebookResponse($userInformation, $user);
                    break;
            }

            // Saving User
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();

            // Redirect the user to homepage
            $url = $this->container->get('router')->generate(
                'homepage'
            );
            return new RedirectResponse($url);
        }

        // reset the error in the session
        $key = time();
        $session->set('_hwi_oauth.registration_error.' . $key, $error);

        return $this->container->get('templating')->renderResponse('HWIOAuthBundle:Connect:registration.html.' . $this->getTemplatingEngine(), array(
            'key' => $key,
            'form' => $form->createView(),
            'userInformation' => $userInformation,
        ));
    }

    /**
     * Authenticate a user with Symfony Security.
     *
     * @param Request $request
     * @param UserInterface $user
     * @param string $resourceOwnerName
     * @param string $accessToken
     * @param bool $fakeLogin
     */
    protected function authenticateUser(Request $request, UserInterface $user, $resourceOwnerName, $accessToken, $fakeLogin = true)
    {
        try {
            $this->container->get('hwi_oauth.user_checker')->checkPreAuth($user);
            $this->container->get('hwi_oauth.user_checker')->checkPostAuth($user);
        } catch (AccountStatusException $e) {
            // Don't authenticate locked, disabled or expired users
            return;
        }
        $token = new OAuthToken($accessToken, $user->getRoles());
        $token->setResourceOwnerName($resourceOwnerName);
        $token->setUser($user);
        $token->setAuthenticated(true);
        $this->setToken($token);
        if ($fakeLogin) {
            // Since we're "faking" normal login, we need to throw our INTERACTIVE_LOGIN event manually
            $this->container->get('event_dispatcher')->dispatch(
                SecurityEvents::INTERACTIVE_LOGIN,
                new InteractiveLoginEvent($request, $token)
            );
        }
    }

    /**
     * @param TokenInterface $token
     *
     * Symfony <2.6 BC. Remove it and use only security.token_storage service instead.
     */
    protected function setToken(TokenInterface $token)
    {
        if ($this->has('security.token_storage')) {
            return $this->get('security.token_storage')->setToken($token);
        }
        return $this->get('security.context')->setToken($token);
    }

    /**
     * Returns templating engine name.
     *
     * @return string
     */
    protected function getTemplatingEngine()
    {
        return $this->container->getParameter('hwi_oauth.templating.engine');
    }

    public function handleFacebookResponse($response, $user)
    {
        // User is from Facebook : DO STUFF HERE \o/
        // All data from Facebook
        $data = $response->getResponse();
        // His profile image : file_get_contents('https://graph.facebook.com/' . $response->getUsername() . '/picture?type=large')

        return $user;
    }
}
