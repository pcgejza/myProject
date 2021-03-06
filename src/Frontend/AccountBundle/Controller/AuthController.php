<?php
namespace Frontend\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;

use Frontend\LayoutBundle\Form\Type\RegistrationFormType;
use Frontend\AccountBundle\Entity\User;
use Frontend\AccountBundle\Entity\UserSettings;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Component\HttpFoundation\Response;

use FOS\UserBundle\Controller\SecurityController as BaseController;

/*
 * Ez a controller a bejelentkezés-regisztráció-elfelejtett jelszó funkciónkhoz készült
 */
class AuthController extends Controller{
  
    /*
     * Felhasználó regisztrál az oldalra
     */
    public function registrationAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();

            $header = "HIBA";
            
            if ($request->getMethod() == 'POST') {
                $form = $request->request->get('fos_user_registration_form');

                $name = $form['name'];
                $email = $form['email'];
                $nickname = $form['nickname'];
                $password = $form['password']['first'];
                $school = $form['school'];
                
                /*
                 * Nick név vizsgálat
                 */
                if(strlen($nickname)>0){
                    if($this->checkNicknameAction($nickname)){// ellenőrizni, hogy létezik-e a nicknév
                        // ha létezik akkor generálás
                        $username = $this->nicknameGenerator($email);
                    }else{
                        // ha nem létezik akkor ez lesz a nicknév
                        $username = $nickname;
                    }
                }else{
                    // ha nem adott meg nick nevet akkor generálás
                     $username = $this->nicknameGenerator($email);
                }
                
                $user = new User();
                $user->setUsername($username);
                $user->setPlainPassword($password);
                $user->setEmail($email);
                $em->persist($user);

                $UserSettings = new UserSettings();
                $UserSettings->setUser($user);
                $UserSettings->setName($name);
                
                if($school != NULL && strlen($school)>0){
                    $School = $this->getDoctrine()->getRepository('FrontendAccountBundle:School')
                                ->getSchoolOrNullByName($school);
                    if($School === NULL){
                        $School = new \Frontend\AccountBundle\Entity\School();
                        $School->setCreatorUser($user);
                        $School->setCreatedAt(new \DateTime('now'));
                        $School->setName($school);
                        $School->setStatus(0);
                        $em->persist($School);
                    }
                    $UserSettings->setSchool($School);
                }
                
                
                
                $em->persist($UserSettings);
                $em->flush();
                
                
            return $this->loginSet($user);
                $header = $this->loginSet($user);
            }

            return new JsonResponse(array('header' => $header));
        }catch(Exception $ex){
            return new JsonResponse(array('err' => $ex->getMessage()));
        }
    }
      
    /*
     * Ez a függvény egy email címből (string) generál egy 
     * egyedi nicknevet (usernevet)
     */
    private function nicknameGenerator($email){
        $generatednick = substr($email, 0, strpos($email, '@'));
        
        $qb =  $this->getDoctrine()->getRepository('FrontendAccountBundle:User')
                    ->createQueryBuilder('u');
        
        $alreadyNicks = $qb
                    ->where($qb->expr()->like('u.username', ':n'))
                    ->select('u.username')
                    ->setParameter('n', '%'.$generatednick.'%')
                    ->getQuery()->getScalarResult();
        
        $ex = false;
        $i = 0;
        $originalNick = $generatednick;
        while($ex === false){
            $jo = true;
            foreach($alreadyNicks as $n){
                if($n['username'] == $generatednick){
                    $jo = false;
                }
            }
            if($jo){
                $ex = true;
            }else{
                $generatednick = $originalNick.(++$i);
            }
        }
        
        return $generatednick;
    }
    
    /*
     * Felhasználó bejelentkezik az oldalra
     */
    public function loginAction(Request $request){
        try{
            
            $userManager = $this->get('fos_user.user_manager');
            
            $name = $request->request->get('_username');
            $pass = $request->request->get('_password');
            
            
            $u = $this->container->get('security.context')->getToken()->getUser();
            
            if(is_object($u))
                throw new Exception('Már be vagy lépve \''.$u->getUserName().'\' userrel!');
            
            $User = $userManager->findUserByUsernameOrEmail($name);
            
            //felhasználónév ellenőrzés
            if($User === null)
                 throw new Exception('Hibás felhasználónév vagy email cím!');
             
            $encoder_service = $this->get('security.encoder_factory');
            $encoder = $encoder_service->getEncoder($User);
            $encoded_pass = $encoder->encodePassword($pass, $User->getSalt());
             
            // jelszó ellenőrzés
            if($User->getPassword() !== $encoded_pass){
                throw new Exception('Rossz a beírt jelszó!');
            }
            
            return $this->loginSet($User);
            
            $header = $this->loginSet($User);
            
            return new JsonResponse(array('header' => $header));
        } catch (Exception $ex) {
            return new JsonResponse(array('err' => $ex->getMessage()));
        }
    }
    
    /*
     * A felhasználó 'bejelentkeztetése' az oldalra, a megfelelő adatok beállítása
     * sikeres bejelentkezés esetén
     */
    private function loginSet($User){
        $token = new UsernamePasswordToken($User, $User->getPassword(), 'main', $User->getRoles());
        $context = $this->get('security.context');
        $context->setToken($token);
        return $this->redirect($this->generateUrl('frontend_index_homepage'));
    }
    
    /*
     * Email cím vizsgálatára szolgáló függvény
     * AJAXból kell hívni és visszaadja, hogy az email cím létezik-e vagy sem az adatbázisban
     */
    public function checkEmailAction(){
        try{
            $email = $this->get('request')->request->get('email');
            
            $cnt = $this->getDoctrine()->getRepository('FrontendAccountBundle:User')
                    ->createQueryBuilder('c')
                    ->select('count(c.id)')
                    ->where('c.email = :email')
                    ->setParameter('email', $email)
                    ->getQuery()
                    ->getSingleScalarResult();
            
            return new JsonResponse(array(
                'exist'=> ($cnt>0) ? true : false 
                ));
        } catch (Exception $ex) {
            return new JsonResponse(array('err' => $ex->getMessage()));
        }
    }
    
    /*
     * Nicknév (felhasználónév) vizsgálatára szolgáló függvény
     * AJAXból és controllerből is hívható és visszaadja, hogy az felhasználónév létezik-e vagy sem az adatbázisban
     */
    public function checkNicknameAction($nickname = null){
        try{
            $normalReturn = false;
            if($nickname == null){
                $nickname = $this->get('request')->request->get('nickname');
            }else{
                $normalReturn = true;
            }
            $cnt = $this->getDoctrine()->getRepository('FrontendAccountBundle:User')
                    ->createQueryBuilder('c')
                    ->select('count(c.id)')
                    ->where('c.username = :nickname')
                    ->setParameter('nickname', $nickname)
                    ->getQuery()
                    ->getSingleScalarResult();
            
            if($normalReturn) return ($cnt>0) ? true : false;
            
            return new JsonResponse(array(
                'exist'=> ($cnt>0) ? true : false 
                ));
        } catch (Exception $ex) {
            return new JsonResponse(array('err' => $ex->getMessage()));
        }
    }
    
    /*
     * Elfelejtett jelszó funkcióhoz
     */
    public function forgotPassAction($code = NULL){
         $request = $this->get('request');
        if($code != NULL){
            try{
                $User = $this->getDoctrine()->getRepository('FrontendAccountBundle:User')
                            ->createQueryBuilder('u')
                            ->select('u')
                            ->where('u.forgotPassCode = :code')
                            ->setParameter('code', $code)
                            ->getQuery()
                            ->getOneOrNullResult();

                if($User === NULL)
                    throw new Exception('Nem létezik ilyen elfelejtett jelszó azonosító!');
                    
                if($request->isMethod('POST')){
                    // jelszó megváltozatása után 
                    
                    $pass = $request->request->get('pass1');
                    
                    if(strlen($pass)<6 || $pass == NULL)
                        throw new Exception('Hiba a jelszó változtatás során : a jelszó null vagy 6 karakternél rövidebb!');
                    
                    
                    $User->setPlainPassword($pass);
                    $User->setForgotPassCode(NULL);
                    $User->setPasswordRequestedAt(NULL);
                    $userManager = $this->container->get('fos_user.user_manager');
                    $userManager->updateUser($User);
                   
                    $message = \Swift_Message::newInstance()
                      ->setSubject('Sikeres jelszó változtatás!')
                      ->setFrom('szerkesztoseg@jegyzetbazis.hu')
                      ->setTo($User->getEmail())
                      ->setBody(
                          $this->renderView(
                              'FrontendAccountBundle:Email:forgotPassEmail.html.twig',
                              array('User' => $User, 'acc' => true)
                          )
                      );
                    $this->get('mailer')->send($message);
                    
                    return $this->render('FrontendAccountBundle:Widget:forgotPass.html.twig',array(
                       'acc' => true
                    ));
                }else{
                    // jelszó változtató oldal
                    return $this->render('FrontendAccountBundle:Widget:forgotPass.html.twig',array(
                        'User' => $User
                    ));
                }
            } catch (Exception $ex) {
                    return $this->render('FrontendAccountBundle:Widget:forgotPass.html.twig',array(
                        'err' => $ex->getMessage()
                    ));
            }
        }else{
            try{
                if($request->isMethod('POST')){
                    $email = $request->request->get('email');
                    if($email == NULL || strlen($email)==0){
                        throw new Exception('Hibás email vagy nick!');
                    }
                    $userManager = $this->get('fos_user.user_manager');
                    $User = $userManager->findUserByUsernameOrEmail($email);
                    
                    if($User === NULL)
                        throw new Exception('Hibás nicknév vagy email cím!');
                    
                    $code = $this->generateRandomString(40);
                    $User->setForgotPassCode($code);
                    $em = $this->getDoctrine()->getEntityManager();
                    $em->persist($User);
                    $em->flush();
                    
                    $message = \Swift_Message::newInstance()
                      ->setSubject('jelszó változtatás')
                      ->setFrom('szerkesztoseg@jegyzetbazis.hu')
                      ->setTo($User->getEmail())
                      ->setBody(
                          $this->renderView(
                              'FrontendAccountBundle:Email:forgotPassEmail.html.twig',
                              array('User' => $User)
                          )
                      );
                    $this->get('mailer')->send($message);
                    
                    
                    return new JsonResponse(array('ok' => true));
                }else{
                    throw new Exception('Nem post!');
                }
            } catch (Exception $ex) {
                return new JsonResponse(array('err' => $ex->getMessage()));
            }
        }
    }
    
    /*
     * ezzel a függvénnyel véletlenszerűen generált karakterláncot lehet létrehozni
     * a paraméterben meg kell adni a karakterlánc hosszát
     */
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}
