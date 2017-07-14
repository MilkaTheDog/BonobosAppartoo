<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Bonobo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        /** @var \AppBundle\Entity\Bonobo $user */
        $user = $this->getUser();
        $bonoboRepo = $this->getDoctrine()->getRepository(Bonobo::class);

        if ($request->isMethod('POST') && ($toXFriend = $request->request->get('toXFriend'))
            && ($actionType = $request->request->get('actionType'))) {

            if (!($toXFriend = $bonoboRepo->find($toXFriend)))
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Friend not found");

            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->container->get('fos_user.user_manager');

            if ($actionType === "add") {

                $user->addFriend($toXFriend);
            } else if ($actionType === "remove") {

                $user->removeFriend($toXFriend);
            }

            $userManager->updateUser($user);
        }

        return $this->render('default/main.html.twig', [
            'user' => $user,
            'userList' => $bonoboRepo->findAll()
        ]);
    }
}
