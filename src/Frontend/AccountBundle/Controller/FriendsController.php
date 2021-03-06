<?php

namespace Frontend\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Frontend\AccountBundle\Entity\Friends;
use Symfony\Component\Security\Acl\Exception\Exception;

/*
 * A barátok kezeléséhez írt controller
 */
class FriendsController extends Controller{
    
    /*
     * Barátok főoldal renderelése
     */
    public function indexAction(){
        if(!$this->get('security.context')->isGranted('ROLE_USER')){
            return $this->redirect($this->generateUrl('frontend_index_homepage'));
        }
        
        return $this->render('FrontendAccountBundle:Friends:index.html.twig');
    }
    
    /*
     * Felhasználó barátainak renderelése
     */
    public function getFriendsAction(){
        try{
            $user = $this->get('security.context')->getToken()->getUser();
            
            $Friends = $this->getDoctrine()->getRepository('FrontendAccountBundle:Friends')
                        ->getUserFriendsByUser($user);
            
            $MySelectedFriends = $this->getDoctrine()->getRepository('FrontendAccountBundle:Friends')
                        ->getMySelectedUsers($user);
            
            $SelectMeToFriend = $this->getDoctrine()->getRepository('FrontendAccountBundle:Friends')
                        ->getSelectMeToFriend($user);
            
            return $this->render('FrontendAccountBundle:Friends:friends.html.twig',array(
                'Friends' => $Friends,
                'MySelectedFriends' => $MySelectedFriends,
                'SelectMeToFriend' => $SelectMeToFriend
            ));
        } catch (Exception $ex) {
            throw new \ErrorException('Hiba a getFriends-ben : '+$ex->getMessage());
        }
    }
    
    /*
     * A megetekintett felhasználó és a megtekintő által visszaadja azt a gombot
     * amit az aktuális kapcsolatuk által kell megjeleníteni a felhasználó felé
     */
    public function getFriendsButtonsAction($viewedUser, $FriendsObject = null){
        try{
            $MyUser = $this->get('security.context')->getToken()->getUser();
            
            if($FriendsObject == null){
                $FriendsObject = $this->getDoctrine()->getRepository('FrontendAccountBundle:Friends')
                        ->getFriendsStatus($MyUser, $viewedUser);
            }
            
            return $this->render('FrontendAccountBundle:Friends:globalFriendsButtons.html.twig',array(
                'FriendsObject' => $FriendsObject,
                'ViewedUser' => $viewedUser
            ));
        } catch (Exception $ex) {
            throw new \ErrorException('Hiba a getFriendsButtonsAction-ben : '+$ex->getMessage());
        }
    }

    /*
     * Barátnak jelölés, barát törlés vagy baráti kapcsolat 
     * megerősítésére szolgáló függvény
     */
    public function friendsAddOrRemoveAction(){
        try{
            $MyUser = $this->get('security.context')->getToken()->getUser();
            $viewedUserId = $this->get('request')->request->get('userID');
            $type =  $this->get('request')->request->get('type');
            
            if(!is_object($MyUser))
                throw new Exception('Nincs bejelentkezve!');
            
            if(!is_numeric($viewedUserId))
                throw new Exception('Nincs megadva a userID!');
            
            if($type == NULL)
                throw new Exception('Nincs megadva a type!');
            
            $em = $this->getDoctrine()->getEntityManager();
            $viewedUser = $em->getReference('FrontendAccountBundle:User', $viewedUserId);
                
            $FriendsObject = $this->getDoctrine()->getRepository('FrontendAccountBundle:Friends')
                        ->getFriendsStatus($MyUser, $viewedUser);
            
            $text = "";
            $isDel = false;
            if($FriendsObject == NULL){
                $FriendsObject = new Friends();
                $FriendsObject->setUserA($MyUser);
                $FriendsObject->setUserB($viewedUser);
                $FriendsObject->setMarkDate(new \DateTime('now'));
                $text = 'Jelölés törlése';
            }else{
                if($type != NULL){
                    switch($type){
                        case 'accept': 
                            if($FriendsObject->getStatus() == 'selected' && $MyUser == $FriendsObject->getUserB()){
                                $FriendsObject->setStatus('active');
                            }else{
                                throw new Exception('Ezt nem csinálhatod mert nem jelölt be téged!');
                            }
                            break;
                        case 'reject': 
                            $FriendsObject->setStatus('rejected');
                            $isDel = true;
                            break;
                        case 'select': 
                            $FriendsObject->setStatus('selected');
                            break;
                        default: 
                            throw new Exception('Nem definiált ez a típus :     '.$type);
                            break;
                    }
                }else{
                    
                    throw new Exception('átugrottuk a cuccot');
                    switch($type){
                        case 'accept':
                            $FriendsObject->setStatus('accept');
                            break;
                        case 'reject':
                            $FriendsObject->setStatus('reject');
                            break;
                    }
                }
                
            }
            if(!$isDel){
                $em->persist($FriendsObject);
            }else{
                $em->remove($FriendsObject);
            }
            $em->flush();
            
            $FriendsObject = $this->getDoctrine()->getRepository('FrontendAccountBundle:Friends')
                        ->getFriendsStatus($MyUser, $viewedUser);
            
            $buttonHtml = $this->renderView('FrontendAccountBundle:Friends:globalFriendsButtons.html.twig',array(
                'FriendsObject' => $FriendsObject,
                'ViewedUser' => $viewedUser
            ));
            
            return new JsonResponse(array(
                'buttonHtml' => $buttonHtml,
                'text' => $text
            ));
        } catch (Exception $ex) {
            return new JsonResponse(array('err'=>$ex->getMessage()));
        }
    }
}
