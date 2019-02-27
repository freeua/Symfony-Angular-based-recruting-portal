<?php
/**
 * Created by PhpStorm.
 * Date: 05.02.18
 * Time: 17:04
 */

namespace AppBundle\Controller\Api;

use AppBundle\Entity\CompanyDetails;
use AppBundle\Entity\NotificationCandidate;
use AppBundle\Entity\NotificationClient;
use AppBundle\Entity\ProfileDetails;
use AppBundle\Entity\User;
use AppBundle\Helper\HelpersClass;
use AppBundle\Helper\SendEmail;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiController
 * @package AppBundle\Controller\Api
 * @Route("/user")
 */
class UserController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     *
     * @Rest\Post("/")
     * @SWG\Post(path="/api/user/",
     *   tags={"User"},
     *   security=false,
     *   summary="User Registration",
     *   description="The method for registering the client and the candidate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="string",
     *              property="role",
     *              example="ROLE_CLIENT | ROLE_CANDIDATE",
     *              description="required for both",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="firstName",
     *              example="firstName",
     *              description="required for both",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="lastName",
     *              example="lastName",
     *              description="required for both",
     *          ),
     *          @SWG\Property(
     *              type="email",
     *              property="email",
     *              example="example@example.com",
     *              description="required for both",
     *          ),
     *          @SWG\Property(
     *              type="phone",
     *              property="phone",
     *              example="123456789",
     *              description="required for both",
     *          ),
     *          @SWG\Property(
     *              type="password",
     *              property="password",
     *              example="password",
     *              description="required for both",
     *          ),
     *          @SWG\Property(
     *              type="password",
     *              property="verifyPassword",
     *              example="password",
     *              description="required for both",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="jobTitle",
     *              example="Job Title",
     *              description="required for business",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="companyName",
     *              example="Company Name",
     *              description="required for business",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="saicaStatus",
     *              example=1,
     *              description="required for candidate.
     *                  1 = Registered CA (Show saicaNumber and saicaNumber is required)
     *                  2 = Eligible to register as a CA
     *                  3 = Completing articles whereafter I will register
     *                  4 = None of the above (NOT SUBMIT and show popup)
     *              ",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="saicaNumber",
     *              example="SAICA Number",
     *              description="only candidate, required when saicaStatus = 1",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="articlesFirm",
     *              example="BDO",
     *              description="required for candidate, Dropdown",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="articlesFirmName",
     *              example="TEST",
     *              description="required for candidate when articlesFirm = Other",
     *          ),
     *          @SWG\Property(
     *              property="boards",
     *              type="integer",
     *              example=1,
     *              description="required for candidate, 1 = Passed Both Board Exams First Time, 2 = Passed Both Board Exams, 3 = ITC passed, APC Outstanding, 4 = ITC Outstanding"
     *          ),
     *          @SWG\Property(
     *              property="dateArticlesCompleted",
     *              type="datetime",
     *              example="2018-09-09",
     *              description="required for candidate"
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=201,
     *      description="Success. User Created",
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *  )
     * )
     */
    public function registerAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if($request->request->has('role') && in_array($request->request->get('role'), ['ROLE_CLIENT', 'ROLE_CANDIDATE'])) {
            $validator = $this->get('validator');
            $user = new User();
            if(empty($request->request->get('verifyPassword')) || $request->request->get('password') != $request->request->get('verifyPassword')){
                $view = $this->view(['error'=>'password and verifyPassword should not be blank and should be the same'], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
            $user->setRegisterDetails($request->request->get('role'), $request->request->get('firstName'), $request->request->get('lastName'), $request->request->get('email'), $request->request->get('phone'), $request->request->get('password'), $request->request->get('jobTitle'));
            if ($user->hasRole('ROLE_CLIENT')) {
                $errorsCheck = $validator->validate($user, null, array('registerClient'));
                if(count($errorsCheck) === 0){
                    $companyDetails = new CompanyDetails($user, $request->request->get('companyName'));
                    $errorsCheckCompany = $validator->validate($companyDetails, null, array('registerClient'));
                    if(count($errorsCheckCompany) === 0){
                        $em->persist($user);
                        $em->persist($companyDetails);
                        $notifyClient = new NotificationClient($user);
                        $em->persist($notifyClient);
                        $em->flush();

                        $emailData = [
                            'role' => 'client',
                            'link' => $request->getSchemeAndHttpHost().'/admin/new_clients'
                        ];
                        $message = (new \Swift_Message('A new client is awaiting your approval'))
                            ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                            ->setBody(
                                $this->renderView('emails/admin/new_user_registration.html.twig',
                                    $emailData
                                ),
                                'text/html'
                            );
                        SendEmail::sendEmailForAdmin($em, $message, $this->get('mailer'), $emailData, SendEmail::TYPE_ADMIN_CLIENT_SIGN);
                        $view = $this->view([], Response::HTTP_CREATED);
                        return $this->handleView($view);
                    }
                    else {
                        $error_description = [];
                        foreach ($errorsCheckCompany as $er) {
                            $error_description[] = $er->getMessage();
                        }
                        $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else {
                    $error_description = [];
                    foreach ($errorsCheck as $er) {
                        $error_description[] = $er->getMessage();
                    }
                    $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else {
                $errorsCheck = $validator->validate($user, null, array('registerCandidate'));
                if(count($errorsCheck) === 0){
                    $em->persist($user);
                    if(
                        $request->request->has('articlesFirm') && !empty($request->request->get('articlesFirm'))
                        && $request->request->has('saicaStatus') && $request->request->getInt('saicaStatus')>0 && $request->request->getInt('saicaStatus')<4
                        && $request->request->has('dateArticlesCompleted') && !empty($request->request->get('dateArticlesCompleted'))
                    ){
                        $profileDetails = new ProfileDetails($user, $request->request->get('articlesFirm'), $request->request->get('saicaStatus'));
                        if($profileDetails->getArticlesFirm() == 'Other'){
                            if($request->request->has('articlesFirmName') && !empty($request->request->get('articlesFirmName'))){
                                $profileDetails->setArticlesFirmName($request->request->get('articlesFirmName'));
                            }
                            else{
                                $view = $this->view(['error'=>['Enter Articles Firm Name']], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        if($profileDetails->getSaicaStatus() == 1){
                            if($request->request->has('saicaNumber') && !empty($request->request->get('saicaNumber'))){
                                $profileDetails->setSaicaNumber($request->request->get('saicaNumber'));
                            }
                            else{
                                $view = $this->view(['error'=>['Enter SAICA Number']], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        $dateArticlesCompleted = null;
                        if($request->request->get('dateArticlesCompleted') instanceof \DateTime){
                            $dateArticlesCompleted = $request->request->get('dateArticlesCompleted');
                        }
                        else{
                            $dateArticlesCompleted = new \DateTime($request->request->get('dateArticlesCompleted'));
                        }
                        if($dateArticlesCompleted instanceof \DateTime){
                            $profileDetails->setDateArticlesCompleted($dateArticlesCompleted);
                        }
                        else{
                            $view = $this->view(['error'=>['Enter Date Articles Complete']], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }

                        $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);

                        $em->persist($profileDetails);
                        $notifyCandidate = new NotificationCandidate($user);
                        $em->persist($notifyCandidate);
                        $em->flush();

                        $emailData = [
                            'role' => 'candidate',
                            'link' => $request->getSchemeAndHttpHost().'/admin/new_candidates'
                        ];
                        $message = (new \Swift_Message('A new candidate is awaiting your approval'))
                            ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                            ->setBody(
                                $this->renderView('emails/admin/new_user_registration.html.twig',
                                    $emailData
                                ),
                                'text/html'
                            );

                        SendEmail::sendEmailForAdmin($em, $message, $this->get('mailer'), $emailData, SendEmail::TYPE_ADMIN_CANDIDATE_SIGN);


                        $view = $this->view([], Response::HTTP_CREATED);
                        return $this->handleView($view);
                    }
                    else{
                        $errorObj = [];
                        if(!$request->request->has('articlesFirm') || empty($request->request->get('articlesFirm'))){
                            $errorObj[] = 'Articles Firm is required and should be not empty';
                        }
                        if(!$request->request->has('saicaStatus') || $request->request->getInt('saicaStatus')<=0 || $request->request->getInt('saicaStatus')>3){
                            $errorObj[] = 'SAICA Status is required and should be not empty';
                        }
                        if(!$request->request->has('dateArticlesCompleted') || empty($request->request->get('dateArticlesCompleted'))){
                            $errorObj[] = 'Date Articles Complete is required and should be not empty';
                        }

                        $view = $this->view(['error'=>$errorObj], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }

                }
                else {
                    $error_description = [];
                    foreach ($errorsCheck as $er) {
                        $error_description[] = $er->getMessage();
                    }
                    $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
        }

        $view = $this->view(['error'=>'role should not be blank or role has incorrect value'], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/reset_password")
     * @SWG\Get(path="/api/user/reset_password",
     *   tags={"User"},
     *   security=false,
     *   summary="Check token",
     *   description="The method check token for resetting password",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="token",
     *      in="query",
     *      required=true,
     *      type="string"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success. Valid Token",
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   )
     * )
     */
    public function resetPasswordCheckTokenAction(Request $request){
        if(!empty($request->query->get('token'))){
            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->findUserByConfirmationToken($request->query->get('token'));
            if ($user instanceof User) {
                $view = $this->view([], Response::HTTP_OK);
                return $this->handleView($view);
            }
            $view = $this->view(["error"=>"invalid token"], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
        $view = $this->view(["error"=>"token should not be blank"], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Post("/reset_password")
     * @SWG\Post(path="/api/user/reset_password",
     *   tags={"User"},
     *   security=false,
     *   summary="Request on Resetting Password",
     *   description="The method send request on resetting password",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="email",
     *              property="email",
     *              example="example@example.com",
     *              description="required",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success. Request Send",
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="Not Found User",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=500,
     *      description="Server Error",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     * )
     */
    public function resetPasswordRequestAction(Request $request)
    {
        if(!empty($request->request->get('email'))){
            $email = $request->request->get('email');
            $user = $this->get('fos_user.user_manager')->findUserByEmail($email);
            if (null === $user) {
                $view = $this->view(["error"=>"user not found"], Response::HTTP_NOT_FOUND);
                return $this->handleView($view);
            }
            if (!$user->isEnabled()) {
                $view = $this->view(["error"=>"user not enabled"], Response::HTTP_NOT_FOUND);
                return $this->handleView($view);
            }
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());

            $url = $request->getSchemeAndHttpHost()."/resetPassword?token=".$user->getConfirmationToken();
            $message = (new \Swift_Message('CAs Online Password Reset'))
                ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView('emails/user/reset_password.html.twig', [
                        'user' => $user,
                        'url' => $url
                    ]),
                    'text/html'
                );
            try{
                $this->get('mailer')->send($message);
            }catch(\Swift_TransportException $e){
                $view = $this->view(["error"=>"Invalid Email Sender"], Response::HTTP_INTERNAL_SERVER_ERROR);
                return $this->handleView($view);
            }

            $user->setPasswordRequestedAt(new \DateTime());
            $this->get('fos_user.user_manager')->updateUser($user);
            $view = $this->view([], Response::HTTP_OK);
            return $this->handleView($view);
        }

        $view = $this->view(["error"=>"email should not be blank"], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Put("/reset_password")
     * @SWG\Put(path="/api/user/reset_password",
     *   tags={"User"},
     *   security=false,
     *   summary="Resetting Password",
     *   description="The method reset password",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="string",
     *              property="token",
     *              example="TOKEN",
     *              description="required",
     *          ),
     *          @SWG\Property(
     *              type="password",
     *              property="password",
     *              example="password",
     *              description="required",
     *          ),
     *          @SWG\Property(
     *              type="password",
     *              property="verifyPassword",
     *              example="password",
     *              description="required",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success. Password Changed",
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   )
     * )
     */
    public function resetPasswordAction(Request $request){
        if(!empty($request->request->get('token'))){
            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->findUserByConfirmationToken($request->request->get('token'));
            if ($user instanceof User) {
                if(empty($request->request->get('password')) || $request->request->get('password') != $request->request->get('verifyPassword')){
                    $view = $this->view(["error"=>"password and verifyPassword should not be blank and should be the same"], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
                $user->setPlainPassword($request->request->get('password'));
                $user->setConfirmationToken(NULL);
                $user->setPasswordRequestedAt(NULL);
                $userManager->updateUser($user);
                $view = $this->view([], Response::HTTP_OK);
                return $this->handleView($view);
            }
            $view = $this->view(["error"=>"invalid token"], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
        $view = $this->view(["error"=>"token should not be blank"], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     *
     * @Rest\Post("/candidate_reactivate")
     * @SWG\Post(path="/api/user/candidate_reactivate",
     *   tags={"User"},
     *   security=false,
     *   summary="Request on Candidate reactivate",
     *   description="The method send request on Candidate reactivate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="email",
     *              property="email",
     *              example="example@example.com",
     *              description="required",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success. Request Send",
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="Not Found User",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=500,
     *      description="Server Error",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     * )
     */
    public function candidateReactivateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if($request->request->has('email') && !empty($request->request->get('email'))){
            $candidate = $em->getRepository("AppBundle:User")->findOneBy(['email'=>$request->request->get('email')]);
            if($candidate instanceof User){
                $emailData = [
                    'link' => $request->getSchemeAndHttpHost().'/admin/edit_candidate?candidateId='.$candidate->getId()
                ];
                $message = (new \Swift_Message('A Candidate wants to Reactivate their account'))
                    ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                    ->setBody(
                        $this->renderView('emails/admin/candidate_reactivate.html.twig',
                            $emailData
                        ),
                        'text/html'
                    );
                SendEmail::sendEmailForAdmin($em, $message, $this->get('mailer'), $emailData);
                $view = $this->view([], Response::HTTP_NO_CONTENT);
            }
            else{
                $view = $this->view(["error"=>"User Not Found"], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(["error"=>"email is required and should be not empty"], Response::HTTP_BAD_REQUEST);
        }
        return $this->handleView($view);
    }
}