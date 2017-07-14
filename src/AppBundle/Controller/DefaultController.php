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

    private function checkAndExecuteForm($request, $user)
    {
        if ($request->isMethod('POST') && ($toXFriend = $request->request->get('toXFriend'))
            && ($actionType = $request->request->get('actionType'))) {

            if (!($toXFriend = $this->getDoctrine()->getRepository(Bonobo::class)->find($toXFriend)))
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Friend not found");

            $this->addOrRemoveFriend($actionType, $user, $toXFriend);
        }
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        /** @var \AppBundle\Entity\Bonobo $user */
        $user = $this->getUser();

        $this->checkAndExecuteForm($request, $user);

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

        $this->checkAndExecuteForm($request, $user);

        return $this->render('default/show.html.twig', [
            'user' => $user,
            'toShow' => $toShow,
            'isFriend' => $user->getFriends()->contains($toShow),
            'commonFriends' => array_intersect($user->getFriends()->toArray(), $toShow->getFriends()->toArray())
        ]);
    }
}
