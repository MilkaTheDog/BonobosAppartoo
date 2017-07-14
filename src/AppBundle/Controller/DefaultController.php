<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Bonobo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    private function addOrRemoveFriend($actionType, $user, $friend)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');

        if ($actionType === "add") {

            $user->addFriend($friend);
        } else if ($actionType === "remove") {

            $user->removeFriend($friend);
        }

        $userManager->updateUser($user);
    }

    private function checkAndExecuteFriendForm($request, $user, $actionType)
    {
        if (($toXFriend = $request->request->get('toXFriend'))) {

            if (!($toXFriend = $this->getDoctrine()->getRepository(Bonobo::class)->find($toXFriend)))
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Friend not found");

            $this->addOrRemoveFriend($actionType, $user, $toXFriend);
        }
    }

    private function checkAndEditProfle($request, $user)
    {
        if (($username = $request->request->get('_username')) && $username !== $user->getUsername())
        {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->container->get('fos_user.user_manager');

            $user->setUsername($username);

            $userManager->updateUser($user);
        }
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        /** @var \AppBundle\Entity\Bonobo $user */
        $user = $this->getUser();

        if ($request->isMethod('POST') && ($actionType = $request->request->get('actionType'))
            && in_array($actionType, array("add", "remove")))
        {
            $this->checkAndExecuteFriendForm($request, $user, $actionType);
        }


        return $this->render('default/main.html.twig', [
            'user' => $user,
            'userList' => $this->getDoctrine()->getRepository(Bonobo::class)->findAll()
        ]);
    }

    /**
     * @Route("/show/{id}", name="showUser")
     */
    public function showAction(Request $request, $id)
    {
        /** @var \AppBundle\Entity\Bonobo $user */
        $user = $this->getUser();

        if (!($toShow = $this->getDoctrine()->getRepository(Bonobo::class)->find($id)))
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Bonobo not found");


        if ($request->isMethod('POST') && ($actionType = $request->request->get('actionType')))
        {
            if (in_array($actionType, array("add", "remove")))
            {
                $this->checkAndExecuteFriendForm($request, $user, $actionType);
            }
            else if ($actionType === "editProfile")
            {
                $this->checkAndEditProfle($request, $user);
            }
        }

        return $this->render('default/show.html.twig', [
            'user' => $user,
            'toShow' => $toShow,
            'isFriend' => $user->getFriends()->contains($toShow),
            'commonFriends' => array_intersect($user->getFriends()->toArray(), $toShow->getFriends()->toArray())
        ]);
    }
}
