<?php

namespace Frontend\LayoutBundle\Controller;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse ;

/*
 * A fejléchez szükges függvények controller-e
 */
class HeaderController extends Controller{
    
    // a fejléc renderelése
    public function getHeaderAction(){
        $User = $this->container->get('security.context')->getToken()->getUser();
        
        $User = (is_object($User)) ? $User : NULL;
        
        $noReadMessages = ($User== NULL) ? NULL : $this->getDoctrine()->getRepository('FrontendMessagingBundle:Message')->getNoReadMessagesCountByUser($User);
    
        return $this->render('FrontendLayoutBundle:Header:header.html.twig',
                array(
                    'User' => $User,
                    'noReadMessages' => $noReadMessages
                ));
    }
    
    /*
     * A bejelentkezési/regisztrációs/elfeljetett jelszó ablak renderelése
     */
    public function getAuthRevealAction(){
        try{
            $page = $this->get('request')->request->get('page');
            
            if($page == NULL) throw new Exception('Hiba a page-val');
            
            $Schools = $this->getDoctrine()->getRepository('FrontendAccountBundle:School')
                        ->getAllActiveSchools();
            
            $html = $this->renderView('FrontendAccountBundle:Widget:reveal.authWindow.html.twig',
                    array(
                        'Schools' => $Schools,
                        'page' => $page
                    ));
            
            return new JsonResponse(array(
                'html' => $html
            ));
        } catch (Exception $ex) {
            return new JsonResponse(array('err'=>$ex->getMessage()));
        }
    }
    
}
