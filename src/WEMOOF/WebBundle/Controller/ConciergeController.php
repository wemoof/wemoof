<?php

namespace WEMOOF\WebBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\Bus\EventExecutionFailed;
use PhpOption\None;
use PhpOption\Some;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use WEMOOF\BackendBundle\Command\ClearLoginKeyCommand;
use WEMOOF\BackendBundle\Command\SendLoginLinkCommand;
use WEMOOF\BackendBundle\Command\VerifyUserCommand;
use WEMOOF\BackendBundle\Command\EditProfileCommand;
use WEMOOF\BackendBundle\Entity\Event;
use WEMOOF\BackendBundle\Repository\EventRepositoryInterface;
use WEMOOF\BackendBundle\Repository\TalkRepositoryInterface;
use WEMOOF\BackendBundle\Repository\UserRepositoryInterface;
use WEMOOF\BackendBundle\Repository\RegistrationRepositoryInterface;
use WEMOOF\BackendBundle\Command\RegisterUserCommand;
use WEMOOF\BackendBundle\Command\RegisterEventCommand;
use WEMOOF\BackendBundle\Command\UnregisterEventCommand;
use WEMOOF\BackendBundle\Service\UrlShortenerInterface;
use WEMOOF\BackendBundle\Value\BooleanValue;
use WEMOOF\BackendBundle\Value\IdValue;
use WEMOOF\BackendBundle\Value\MarkdownTextValue;
use WEMOOF\BackendBundle\Value\NameValue;
use WEMOOF\BackendBundle\Value\PlainTextValue;
use WEMOOF\BackendBundle\Value\SchemeAndHostValue;
use WEMOOF\BackendBundle\Value\TwitterHandleValue;
use WEMOOF\BackendBundle\Value\URLValue;
use WEMOOF\WebBundle\Form\EditProfileType;
use WEMOOF\WebBundle\Form\RegisterType;
use WEMOOF\WebBundle\Form\RegisterEventType;
use WEMOOF\WebBundle\Form\UnregisterEventType;
use WEMOOF\BackendBundle\Entity\User;
use WEMOOF\BackendBundle\Entity\Registration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Util\StringUtils;
use Symfony\Component\HttpFoundation\Session\Session;
use WEMOOF\WebBundle\Model\EditProfileModel;
use WEMOOF\WebBundle\Slugger;

/**
 * @Route(service="wemoof.web.controller.concierge")
 *
 * FIXME: Tests
 */
class ConciergeController
{
    /**
     * @var \WEMOOF\BackendBundle\Repository\RegistrationRepositoryInterface
     */
    private $registrationRepository;

    /**
     * @var \WEMOOF\BackendBundle\Repository\EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var \WEMOOF\BackendBundle\Service\UrlShortenerInterface
     */
    private $shortener;

    public function __construct(
        RegistrationRepositoryInterface $registrationRepository,
        EventRepositoryInterface $eventRepository,
        UrlShortenerInterface $shortener
    )
    {
        $this->registrationRepository = $registrationRepository;
        $this->eventRepository = $eventRepository;
        $this->shortener = $shortener;
    }

    /**
     * @Route("/{id}/nametags")
     * @Template()
     */
    public function nametagsAction($id)
    {
        $event = $this->eventRepository->getEvent($id)->getOrThrow(new NotFoundHttpException(sprintf("Unkown event: %d", $id)));
        $registrations = $this->registrationRepository->getRegistrationsForEvent($event);
        $numcols = 2;
        $numrows = 3;

        $pages = array();
        $shorturls = new ArrayCollection();
        $page = 0;
        $row = 0;
        foreach ($registrations as $registration) {
            if (!isset($pages[$page])) $pages[$page] = array();
            if (!isset($pages[$page][$row])) $pages[$page][$row] = array();
            $user = $registration->getUser();
            if ($user->getFirstname() === null) continue;
            if ($user->isPublic()) $shorturls->set($user->getId(), $this->shortener->shortenRoute('wemoof_user_short', array('id' => $user->getId())));
            $pages[$page][$row][] = $registration;
            if (count($pages[$page][$row]) % $numcols === 0) {
                $row++;
                if (count($pages[$page]) % $numrows === 0) $page++;
            }
        }

        return array(
            'event' => $event,
            'pages' => $pages,
            'shorturls' => $shorturls,
        );
    }
}