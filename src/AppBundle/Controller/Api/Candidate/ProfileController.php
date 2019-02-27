<?php
/**
 * Created by PhpStorm.
 * Date: 17.04.18
 * Time: 14:14
 */

namespace AppBundle\Controller\Api\Candidate;


use AppBundle\Entity\CandidateAchievements;
use AppBundle\Entity\CandidateReferences;
use AppBundle\Entity\ProfileDetails;
use AppBundle\Entity\Settings;
use AppBundle\Helper\HelpersClass;
use AppBundle\Helper\SendEmail;
use Aws\Credentials\Credentials;
use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Aws\S3\S3Client;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class MainController
 * @package AppBundle\Controller\Api\Candidate
 * @Rest\Route("profile")
 * @Security("has_role('ROLE_CANDIDATE')")
 */
class ProfileController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/api/candidate/profile/",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Get Candidate Profile Details",
     *   description="The method for getting profile details for candidate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="user",
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="phone",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="email",
     *                  type="string"
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="profile",
     *              type="object",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="saicaStatus",
     *                  example=1,
     *                  description="required for candidate.
     *                      1 = Registered CA (Show saicaNumber and saicaNumber is required)
     *                      2 = Eligible to register as a CA
     *                      3 = Completing articles whereafter I will register
     *                      4 = None of the above (NOT SUBMIT and show popup)
     *                  ",
     *              ),
     *              @SWG\Property(
     *                  property="saicaNumber",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="mostRole",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="mostEmployer",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="articlesFirm",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  type="string",
     *                  property="articlesFirmName",
     *                  example="TEST",
     *                  description="required for candidate when articlesFirm = Other",
     *              ),
     *              @SWG\Property(
     *                  property="nationality",
     *                  type="integer",
     *                  description="1=South African, 2=Other"
     *              ),
     *              @SWG\Property(
     *                  property="idNumber",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="ethnicity",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="gender",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="dateOfBirth",
     *                  type="date"
     *              ),
     *              @SWG\Property(
     *                  property="dateArticlesCompleted",
     *                  type="date"
     *              ),
     *              @SWG\Property(
     *                  property="costToCompany",
     *                  type="integer",
     *                  description="0 = Newly Qualified, 1 = 700K, 2 = 700K-1 million,3 = >1 million"
     *              ),
     *              @SWG\Property(
     *                  property="criminal",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="criminalDescription",
     *                  type="string",
     *                  description="show when criminal true"
     *              ),
     *              @SWG\Property(
     *                  property="credit",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="creditDescription",
     *                  type="string",
     *                  description="show when credit true"
     *              ),
     *              @SWG\Property(
     *                  property="boards",
     *                  type="integer",
     *                  description="1 = Passed Both Board Exams First Time, 2 = Passed Both Board Exams, 3 = ITC passed, APC Outstanding, 4 = ITC Outstanding"
     *              ),
     *              @SWG\Property(
     *                  property="otherQualifications",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="homeAddress",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="addressCountry",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="addressState",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="addressZipCode",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="addressCity",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="addressSuburb",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="addressStreet",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="addressStreetNumber",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="addressUnit",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="availability",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="availabilityPeriod",
     *                  type="integer",
     *                  description="
     *                      1=30 Day notice period
     *                      2=60 Day notice period
     *                      3=90 Day notice period
     *                      4=I can provide a specific date"
     *              ),
     *              @SWG\Property(
     *                  property="dateAvailability",
     *                  type="date"
     *              ),
     *              @SWG\Property(
     *                  property="citiesWorking",
     *                  type="array",
     *                  @SWG\Items(type="string"),
     *              ),
     *              @SWG\Property(
     *                  property="picture",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      @SWG\Property(
     *                          property="url",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="size",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="approved",
     *                          type="boolean"
     *                      ),
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="matricCertificate",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      @SWG\Property(
     *                          property="url",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="size",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="approved",
     *                          type="boolean"
     *                      ),
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="tertiaryCertificate",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      @SWG\Property(
     *                          property="url",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="size",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="approved",
     *                          type="boolean"
     *                      ),
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="universityManuscript",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      @SWG\Property(
     *                          property="url",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="size",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="approved",
     *                          type="boolean"
     *                      ),
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="creditCheck",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      @SWG\Property(
     *                          property="url",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="size",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="approved",
     *                          type="boolean"
     *                      ),
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="cvFiles",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      @SWG\Property(
     *                          property="url",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="size",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="approved",
     *                          type="boolean"
     *                      ),
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="linkedinCheck",
     *                  type="boolean",
     *                  example=false,
     *                  description="if true show linkedinUrl"
     *              ),
     *              @SWG\Property(
     *                  property="linkedinUrl",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="video",
     *                  type="object",
     *                  @SWG\Property(
     *                      property="url",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="approved",
     *                      type="boolean"
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="percentage",
     *                  type="integer",
     *                  example=50
     *              ),
     *              @SWG\Property(
     *                  property="looking",
     *                  type="boolean",
     *                  example=false
     *              ),
     *              @SWG\Property(
     *                  property="visible",
     *                  type="boolean",
     *                  example=false
     *              )
     *          ),
     *          @SWG\Property(
     *              property="allowVideo",
     *              type="boolean",
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *     @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
    public function getProfileAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $userDetails = $em->getRepository("AppBundle:User")->getCandidateProfile($this->getUser()->getId());
        $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->getCandidateDetails($this->getUser()->getId());
        $settings = $em->getRepository('AppBundle:Settings')->findOneBy([]);
        if(!$settings instanceof Settings){
            $settings = new Settings(false);
            $em->persist($settings);
            $em->flush();
        }
        $view = $this->view(['user'=>$userDetails,'profile'=>$profileDetails,'allowVideo'=>$settings->getAllowVideo()], Response::HTTP_OK);
        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Put("/")
     * @SWG\Put(path="/api/candidate/profile/",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Edit Candidate Profile Details Without Files",
     *   description="The method for Edit profile details for candidate",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
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
     *              property="user",
     *              type="object",
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="string",
     *                  example="firstName",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="string",
     *                  example="lastName",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="phone",
     *                  type="string",
     *                  example="123213123",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="email",
     *                  type="string",
     *                  example="email@gmail.com",
     *                  description="required"
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="profile",
     *              type="object",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="saicaStatus",
     *                  example=1,
     *                  description="required for candidate.
     *                      1 = Registered CA (Show saicaNumber and saicaNumber is required)
     *                      2 = Eligible to register as a CA
     *                      3 = Completing articles whereafter I will register
     *                      4 = None of the above (NOT SUBMIT and show popup)
     *                  ",
     *              ),
     *              @SWG\Property(
     *                  property="saicaNumber",
     *                  type="string",
     *                  example="saicaNumber"
     *              ),
     *              @SWG\Property(
     *                  property="mostRole",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="mostEmployer",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="articlesFirm",
     *                  type="string",
     *                  example="Other"
     *              ),
     *              @SWG\Property(
     *                  type="string",
     *                  property="articlesFirmName",
     *                  example="TEST",
     *                  description="required for candidate when articlesFirm = Other",
     *              ),
     *              @SWG\Property(
     *                  property="nationality",
     *                  type="integer",
     *                  description="1=South African, 2=Other"
     *              ),
     *              @SWG\Property(
     *                  property="idNumber",
     *                  type="string",
     *                  example="idNumber",
     *              ),
     *              @SWG\Property(
     *                  property="ethnicity",
     *                  type="string",
     *                  example="ethnicity",
     *              ),
     *              @SWG\Property(
     *                  property="gender",
     *                  type="string",
     *                  example="gender",
     *              ),
     *              @SWG\Property(
     *                  property="dateOfBirth",
     *                  type="date",
     *                  example="2018-05-16",
     *              ),
     *              @SWG\Property(
     *                  property="dateArticlesCompleted",
     *                  type="date",
     *                  example="2018-05-16",
     *              ),
     *              @SWG\Property(
     *                  property="costToCompany",
     *                  type="integer",
     *                  example=0,
     *                  description="0 = Newly Qualified, 1 = 700K, 2 = 700K-1 million,3 = >1 million"
     *              ),
     *              @SWG\Property(
     *                  property="criminal",
     *                  type="boolean",
     *                  example="false",
     *              ),
     *              @SWG\Property(
     *                  property="criminalDescription",
     *                  type="string",
     *                  example="criminalDescription"
     *              ),
     *              @SWG\Property(
     *                  property="credit",
     *                  type="boolean",
     *                  example=false
     *              ),
     *              @SWG\Property(
     *                  property="creditDescription",
     *                  type="string",
     *                  example="creditDescription"
     *              ),
     *              @SWG\Property(
     *                  property="boards",
     *                  type="integer",
     *                  example=1,
     *                  description="1 = Passed Both Board Exams First Time, 2 = Passed Both Board Exams, 3 = ITC passed, APC Outstanding, 4 = ITC Outstanding"
     *              ),
     *              @SWG\Property(
     *                  property="otherQualifications",
     *                  type="string",
     *                  example="otherQualifications",
     *              ),
     *              @SWG\Property(
     *                  property="homeAddress",
     *                  type="string",
     *                  example="homeAddress",
     *              ),
     *              @SWG\Property(
     *                  property="addressCountry",
     *                  type="string",
     *                  example="addressCountry",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="addressState",
     *                  type="string",
     *                  example="addressState",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="addressZipCode",
     *                  type="string",
     *                  example="addressZipCode",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="addressCity",
     *                  type="string",
     *                  example="addressCity",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="addressSuburb",
     *                  type="string",
     *                  example="addressSuburb",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="addressStreet",
     *                  type="string",
     *                  example="addressStreet",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="addressStreetNumber",
     *                  type="string",
     *                  example="addressStreetNumber",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="addressUnit",
     *                  type="string",
     *                  example="addressUnit",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  property="availability",
     *                  type="boolean",
     *                  example="false",
     *              ),
     *              @SWG\Property(
     *                  property="availabilityPeriod",
     *                  type="integer",
     *                  description="
     *                      1=30 Day notice period
     *                      2=60 Day notice period
     *                      3=90 Day notice period
     *                      4=I can provide a specific date"
     *              ),
     *              @SWG\Property(
     *                  property="dateAvailability",
     *                  type="date",
     *                  example="2018-05-16",
     *                  description="Required if availability=true"
     *              ),
     *              @SWG\Property(
     *                  property="citiesWorking",
     *                  type="array",
     *                  @SWG\Items(type="string"),
     *                  description="Gauteng or Western Cape or Eastern Cape or KZN or Other"
     *              ),
     *              @SWG\Property(
     *                  property="linkedinCheck",
     *                  type="boolean",
     *                  example=false,
     *              ),
     *              @SWG\Property(
     *                  property="linkedinUrl",
     *                  type="string",
     *                  example="https://linkedin.com/in/name"
     *              )
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad Request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
    public function editProfileAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if($request->request->has('user') && !empty(($request->request->get('user')))){
            $userData = $request->request->get('user');

            if(isset($userData['firstName']) && isset($userData['lastName']) && isset($userData['email']) && isset($userData['phone'])){
                $user->setFirstName($userData['firstName']);
                $user->setLastName($userData['lastName']);
                $user->setEmail($userData['email']);
                $user->setUsername($userData['email']);
                $user->setPhone($userData['phone']);
                $errors = $this->get('validator')->validate($user, null, array('updateCandidate'));
                if(count($errors) === 0){
                    $em->persist($user);
                    if($request->request->has('profile') && !empty($request->request->get('profile'))){
                        $dataProfile = $request->request->get('profile');
                        if(isset($dataProfile['articlesFirm']) && isset($dataProfile['saicaStatus'])){
                            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
                            if(!$profileDetails instanceof ProfileDetails){
                                $profileDetails = new ProfileDetails($user, $dataProfile['articlesFirm'], $dataProfile['saicaStatus']);
                            }
                            $profileDetails->update($dataProfile);
                            $errorsDetails = $this->get('validator')->validate($profileDetails, null, array('updateDetails'));
                            if(count($errorsDetails) === 0){
                                if($profileDetails->getArticlesFirm() == 'Other' && empty($profileDetails->getArticlesFirmName())){
                                    $view = $this->view(['error'=>['Enter Articles Firm Name']], Response::HTTP_BAD_REQUEST);
                                    return $this->handleView($view);
                                }
                                if($profileDetails->getSaicaStatus() == 1 && empty($profileDetails->getSaicaNumber())){
                                    $view = $this->view(['error'=>['Enter SAICA Number']], Response::HTTP_BAD_REQUEST);
                                    return $this->handleView($view);
                                }
                            }
                            else {
                                $error_description = [];
                                foreach ($errorsDetails as $er) {
                                    $error_description[] = $er->getMessage();
                                }
                                $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                            $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                            $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                            $em->persist($profileDetails);
                            $em->flush();

                            $view = $this->view([
                                'percentage'=>$profileDetails->getPercentage(),
                                'looking' => $profileDetails->getLooking(),
                                'visible' => $profileDetails->getVisible()
                            ], Response::HTTP_OK);
                        }
                        else{
                            if(!isset($dataProfile['articlesFirm'])){
                                $view = $this->view(['error'=>['Enter Articles Firm']], Response::HTTP_BAD_REQUEST);
                            }
                            else{
                                $view = $this->view(['error'=>['Enter SAICA Status']], Response::HTTP_BAD_REQUEST);
                            }
                        }
                    }
                    else{
                        $view = $this->view(['error'=>['profile us required']], Response::HTTP_BAD_REQUEST);
                    }
                }
                else {
                    $error_description = [];
                    foreach ($errors as $er) {
                        $error_description[] = $er->getMessage();
                    }
                    $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $view = $this->view(['error'=>['all user field required']], Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            $view = $this->view(['error'=>['user field required']], Response::HTTP_BAD_REQUEST);
        }

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     *
     * @Rest\Patch("/")
     * @SWG\Patch(path="/api/candidate/profile/",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Change Status Candidate",
     *   description="The method for change status for candidate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
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
     *              type="boolean",
     *              property="looking",
     *              example=true,
     *              description="one of two field"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="visible",
     *              example=true,
     *              description="one of two field"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad Request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
     *      description="Not Found",
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
    public function updateStatusProfileAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
        if($profileDetails instanceof $profileDetails){
            if($request->request->has('looking') && is_bool($request->request->get('looking'))){
                $profileDetails->setLooking($request->request->get('looking'));

                if($request->request->get('looking') == false){
                    $profileDetails->setVisible(false);
                    $profileDetails->setLastDeactivated(new \DateTime());
                    $emailData = [
                        'candidate' => [
                            'firstName' => $user->getFirstName(),
                            'lastName' => $user->getLastName(),
                            'email' => $user->getEmail(),
                            'phone' => $user->getPhone()
                        ] ,
                        'link' => $request->getSchemeAndHttpHost().'/admin/edit_candidate?candidateId='.$user->getId()
                    ];
                    $message = (new \Swift_Message('A candidate has just deactivated their profile'))
                        ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                        ->setBody(
                            $this->renderView('emails/admin/candidate_deactivated.html.twig',
                                $emailData
                            ),
                            'text/html'
                        );

                    SendEmail::sendEmailForAdmin($em, $message, $this->get('mailer'), $emailData, SendEmail::TYPE_ADMIN_CANDIDATE_DEACTIVATE);
                }
                $em->persist($profileDetails);
                $em->flush();
                $view = $this->view([
                    'looking' => $profileDetails->getLooking(),
                    'visible' => $profileDetails->getVisible()
                ], Response::HTTP_OK);
            }
            elseif ($request->request->has('visible') && is_bool($request->request->get('visible'))){
                $profileDetails->setVisible($request->request->get('visible'));
                $em->persist($profileDetails);
                $em->flush();
                $view = $this->view([
                    'looking' => $profileDetails->getLooking(),
                    'visible' => $profileDetails->getVisible()
                ], Response::HTTP_OK);
            }
            else{
                $view = $this->view(['error'=>'Field looking Or visible is required, and should be boolean type'], Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            $view = $this->view(['error'=>'Profile Not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     *
     * @Rest\Post("/file")
     * @SWG\Post(path="/api/candidate/profile/file",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Upload candidate file Candidate",
     *   description="The method for upload file for candidate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="multipart/form-data",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="array",
     *              property="fieldName",
     *              @SWG\Items(
     *                  type="string",
     *              ),
     *              example={"file1","file2"}
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *           @SWG\Property(
     *              property="percentage",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="files",
     *              type="object",
     *              @SWG\Property(
     *                  property="fieldName",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      @SWG\Property(
     *                          property="url",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="size",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="approved",
     *                          type="boolean"
     *                      ),
     *                  )
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad Request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
     *      description="Not Found",
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
    public function uploadFileAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
        if($profileDetails instanceof $profileDetails){
            $files = [];
            if(!empty($request->files->all())){
                foreach ($request->files->all() as $key=>$fileArray){
                    $methodName = 'set'.ucfirst($key);
                    $methodNameGet = 'get'.ucfirst($key);
                    if(property_exists(ProfileDetails::class,$key) && method_exists(ProfileDetails::class,$methodName) && method_exists(ProfileDetails::class,$methodNameGet)){
                        $files[$key] = [];
                        if(is_array($fileArray)){
                            foreach ($fileArray as $fileUpload){
                                if($fileUpload instanceof UploadedFile){
                                    $fileName = md5(uniqid()).'.'.$fileUpload->getClientOriginalExtension();
                                    if($fileUpload->move("uploads/candidate/".$user->getId(),$fileName)){
                                        $files[$key][] = [
                                            'url'=>$request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName,
                                            'name'=>$fileUpload->getClientOriginalName(),
                                            'size'=>$fileUpload->getClientSize(),
                                            'time'=>time(),
                                            'approved'=>false
                                        ];
                                        if($key != 'picture'){
                                            $emailData = array(
                                                'link' => $request->getSchemeAndHttpHost().'/admin/dashboard'
                                            );
                                            $message = (new \Swift_Message('A new video or document is awaiting your approval'))
                                                ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                                                ->setBody(
                                                    $this->renderView('emails/admin/new_file_uploaded.html.twig',
                                                        $emailData
                                                    ),
                                                    'text/html'
                                                );
                                            SendEmail::sendEmailForAdmin($em, $message, $this->get('mailer'), $emailData, SendEmail::TYPE_ADMIN_CANDIDATE_FILE);
                                        }
                                    }
                                }
                            }
                        }
                        else{
                            if($fileArray instanceof UploadedFile){
                                $fileName = md5(uniqid()).'.'.$fileArray->getClientOriginalExtension();
                                if($fileArray->move("uploads/candidate/".$user->getId(),$fileName)){
                                    $files[$key][] = [
                                        'url'=>$request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName,
                                        'name'=>$fileArray->getClientOriginalName(),
                                        'size'=>$fileArray->getClientSize(),
                                        'time'=>time(),
                                        'approved'=>false
                                    ];
                                    if($key != 'picture'){
                                        $emailData = array(
                                            'link' => $request->getSchemeAndHttpHost().'/admin/dashboard'
                                        );
                                        $message = (new \Swift_Message('A new video or document is awaiting your approval'))
                                            ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                                            ->setBody(
                                                $this->renderView('emails/admin/new_file_uploaded.html.twig',
                                                    $emailData
                                                ),
                                                'text/html'
                                            );
                                        SendEmail::sendEmailForAdmin($em, $message, $this->get('mailer'), $emailData, SendEmail::TYPE_ADMIN_CANDIDATE_FILE);
                                    }
                                }
                            }
                        }
                        $issetFiles = $profileDetails->$methodNameGet();
                        if(!empty($issetFiles) && $key != 'picture' && $key != 'cvFiles'){
                            $files[$key] = array_merge($issetFiles, $files[$key]);
                        }
                        $profileDetails->$methodName($files[$key]);
                    }
                    else{
                        $view = $this->view(['error'=>'field '.$key.' not found'], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }

                }
                $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                $em->persist($profileDetails);
                $em->flush();
                $view = $this->view([
                    'percentage'=>$profileDetails->getPercentage(),
                    'looking' => $profileDetails->getLooking(),
                    'visible' => $profileDetails->getVisible(),
                    'files'=>$files
                ], Response::HTTP_OK);
            }
            else{
                $view = $this->view(['error'=>'Files is empty'], Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            $view = $this->view(['error'=>'Profile Not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Patch("/file")
     * @SWG\Patch(path="/api/candidate/profile/file",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Remove Candidate File",
     *   description="The method for remove File for candidate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
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
     *              type="object",
     *              property="fieldName",
     *              @SWG\Property(
     *                  type="string",
     *                  property="url",
     *                  example="fileURL",
     *                  description="required"
     *              )
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="fieldName",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="url",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="size",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="approved",
     *                      type="boolean"
     *                  ),
     *              )
     *          )
     *     )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad Request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
     *      description="Not Found",
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
    public function removeFileAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
        if($profileDetails instanceof ProfileDetails){
            $data = $request->request->all();
            if(!empty($data)){
                foreach ($data as $key=>$item){
                    if(in_array($key,['picture','matricCertificate','tertiaryCertificate','universityManuscript','creditCheck','cvFiles'])){
                        if(isset($item['url']) && !empty($item['url'])){
                            $methodNameSet = 'set'.ucfirst($key);
                            $methodNameGet = 'get'.ucfirst($key);
                            if(property_exists(ProfileDetails::class,$key) && method_exists(ProfileDetails::class,$methodNameSet) && method_exists(ProfileDetails::class,$methodNameGet)){
                                $files = $profileDetails->$methodNameGet();
                                $fileSystem = new Filesystem();
                                $checkFile = false;
                                foreach ($files as $k=>$file){
                                    if(isset($file['url']) && $file['url'] == $item['url']){
                                        $parse = parse_url($file['url']);
                                        if(isset($parse['path']) && !empty($parse['path'])){
                                            $parse['path'] = ltrim($parse['path'], '/');
                                            if($fileSystem->exists($parse['path'])){
                                                $fileSystem->remove($parse['path']);
                                            }
                                            unset($files[$k]);
                                            $checkFile = true;
                                        }
                                    }
                                }
                                if($checkFile == true) {
                                    $newFiles = [];
                                    if (!empty($files)) {
                                        foreach ($files as $f) {
                                            $newFiles[] = $f;
                                        }
                                    }
                                    $profileDetails->$methodNameSet($newFiles);
                                    $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                                    $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                                    $em->persist($profileDetails);
                                    $em->flush();
                                    $view = $this->view([
                                        'percentage'=>$profileDetails->getPercentage(),
                                        'looking' => $profileDetails->getLooking(),
                                        'visible' => $profileDetails->getVisible(),
                                        $key => $profileDetails->$methodNameGet()
                                    ], Response::HTTP_OK);
                                    return $this->handleView($view);
                                }
                                else{
                                    $view = $this->view(['error'=>'File Not found'], Response::HTTP_BAD_REQUEST);
                                    return $this->handleView($view);
                                }
                            }
                            else{
                                $view = $this->view(['error'=>'field '.$key.' not found'], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        else{
                            $view = $this->view(['error'=>'property url is required'], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    else{
                        $view = $this->view(['error'=>'body should be not empty'], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
            }
            else{
                $view = $this->view(['error'=>'body should be not empty'], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view(['error'=>'Profile Not Found'], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
        $view = $this->view(['error'=>'error'], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/video")
     * @SWG\Get(path="/api/candidate/profile/video",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Get Candidate Video",
     *   description="The method for getting video for candidate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="video",
     *              type="object",
     *              @SWG\Property(
     *                  property="url",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string"
     *              )
     *          )
     *     )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
    public function getVideoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
        if($profileDetails instanceof ProfileDetails){
            $view = $this->view(['video'=>$profileDetails->getVideo()], Response::HTTP_OK);
        }
        else{
            $view = $this->view(['video'=>null], Response::HTTP_OK);

        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     *
     * @Rest\Post("/video")
     * @SWG\Post(path="/api/candidate/profile/video",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Upload Candidate Video",
     *   description="The method for uploading video for candidate",
     *   produces={"application/json"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="multipart/form-data",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="video",
     *              type="file",
     *              example="picture1.mp4"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success. Video Uploaded",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="video",
     *              type="object",
     *              @SWG\Property(
     *                  property="url",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string"
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad Request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
    public function uploadVideoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
        if($request->files->has('video')){
            $fileUpload = $request->files->get('video');
            if($fileUpload instanceof UploadedFile){
                $ext = $fileUpload->getClientOriginalExtension();
                if(empty($ext)){
                    $ext = 'webm';
                }
                $fileName = $user->getFirstName()."_".$user->getId().".".$ext;
                try {
                    $fileUpload->move("uploads/candidate/".$user->getId(),$fileName);
                } catch (\Exception $e) {
                    $view = $this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                    return $this->handleView($view);
                }

                $credentials = new Credentials($this->container->getParameter('aws_key'), $this->container->getParameter('aws_secret'));
                $s3Client = new S3Client([
                    'version'     => 'latest',
                    'region'      => $this->container->getParameter('aws_region'),
                    'credentials' => $credentials
                ]);

                try {
                    $result = $s3Client->putObject(array(
                        'Bucket' => $this->container->getParameter('aws_bucket'),
                        'Key'    => $fileName,
                        'SourceFile' => "uploads/candidate/".$user->getId()."/".$fileName,
                        'ACL' => 'public-read'
                    ));
                } catch (\Exception $e) {

                }
                if(isset($result) && isset($result['ObjectURL'])){
                    $filePath = $result['ObjectURL'];
                    $fileSystem = new Filesystem();
                    if($fileSystem->exists("uploads/candidate/".$user->getId()."/".$fileName)){
                        try{
                            $fileSystem->remove("uploads/candidate/".$user->getId()."/".$fileName);
                        }
                        catch (\Exception $e){}
                    }
                    $elasticTranscoderClient = new ElasticTranscoderClient(array(
                        'credentials' => $credentials,
                        'version' => 'latest',
                        'region'  => 'us-east-1'
                    ));
                    try {
                        $fileComposeName = $user->getFirstName()."_".$user->getId()."_".uniqid($user->getId()).".mp4";
                        $resultComposed = $elasticTranscoderClient->createJob([
                            'PipelineId' => '1535028122956-3rhicx',
                            'Input' => array(
                                'Key' => $fileName
                            ),
                            'Output' => array(
                                'Key' => $fileComposeName,
                                'PresetId' => '1351620000001-000010'
                            )
                        ]);
                    } catch (\Exception $e) {
                    }
                    if(isset($fileComposeName) && isset($resultComposed) && !empty($resultComposed->get('Job'))){
                        $filePathNew = 'https://s3.us-east-2.amazonaws.com/casonlinevideos/'.$fileComposeName;
                    }
                }
                else{
                    $filePath = $request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName;
                }
                $videoResponse = [
                    'url'=>$filePath,
                    'name'=>$fileName,
                    'time'=>time(),
                    'approved'=>false
                ];
                $video = [
                    'url'=>(isset($filePathNew)) ? $filePathNew : $filePath,
                    'name'=>$fileName,
                    'time'=>time(),
                    'approved'=>false
                ];
                $profileDetails->setVideo($video);
                $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                $em->persist($profileDetails);
                $em->flush();
                $emailData = array(
                    'link' => $request->getSchemeAndHttpHost().'/admin/dashboard'
                );
                $message = (new \Swift_Message('A new video or document is awaiting your approval'))
                    ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                    ->setBody(
                        $this->renderView('emails/admin/new_file_uploaded.html.twig',
                            $emailData
                        ),
                        'text/html'
                    );
                SendEmail::sendEmailForAdmin($em, $message, $this->get('mailer'), $emailData, SendEmail::TYPE_ADMIN_CANDIDATE_FILE);
                $view = $this->view([
                    'percentage'=>$profileDetails->getPercentage(),
                    'looking' => $profileDetails->getLooking(),
                    'visible' => $profileDetails->getVisible(),
                    'video'=>$videoResponse
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view(['error'=>'field video is not file'], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }

        $view = $this->view(['error'=>'field video is required'], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Delete("/video")
     * @SWG\Delete(path="/api/candidate/profile/video",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="DELETE Candidate Video",
     *   description="The method for DELETE video for candidate",
     *   produces={"application/json"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="multipart/form-data",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success. Video Deleted",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad Request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
    public function removeVideoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
        if($profileDetails instanceof ProfileDetails){
            $video = $profileDetails->getVideo();

            if(isset($video['name'])){
                $credentials = new Credentials($this->container->getParameter('aws_key'), $this->container->getParameter('aws_secret'));
                $s3Client = new S3Client([
                    'version'     => 'latest',
                    'region'      => $this->container->getParameter('aws_region'),
                    'credentials' => $credentials
                ]);

                try {
                    $result = $s3Client->deleteObject(array(
                        'Bucket' => $this->container->getParameter('aws_bucket'),
                        'Key'    => $video['name']
                    ));
                    $fileSystem = new Filesystem();
                    if($fileSystem->exists("uploads/candidate/".$user->getId()."/".$video['name'])){
                        try{
                            $fileSystem->remove("uploads/candidate/".$user->getId()."/".$video['name']);
                        }
                        catch (\Exception $e){}
                    }
                } catch (\Exception $e) {

                }
            }
            $profileDetails->setVideo(NULL);
            $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
            $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
            $em->persist($profileDetails);
            $em->flush();
            $view = $this->view([
                'percentage'=>$profileDetails->getPercentage(),
                'looking' => $profileDetails->getLooking(),
                'visible' => $profileDetails->getVisible(),
            ], Response::HTTP_OK);
            return $this->handleView($view);
        }
        else{
            $view = $this->view(['error'=>'Video not Found'], Response::HTTP_BAD_REQUEST);

        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     *
     * @Rest\Post("/video/request")
     * @SWG\Post(path="/api/candidate/profile/video/request",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Request create Candidate Video",
     *   description="The method for creating request to create video for candidate",
     *   produces={"application/json"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success. Request send",
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
    public function requestForVideoAction(Request $request){
        $candidate = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $emailData = [
            'candidate' => [
                'firstName' => $candidate->getFirstName(),
                'lastName' => $candidate->getLastName(),
                'email' => $candidate->getEmail(),
                'phone' => $candidate->getPhone()
            ] ,
            'link' => $request->getSchemeAndHttpHost().'/admin/edit_candidate?candidateId='.$candidate->getId()
        ];
        $message = (new \Swift_Message('A candidate has requested to get their video done professionally'))
            ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
            ->setBody(
                $this->renderView('emails/admin/request_to_video.html.twig',
                    $emailData
                ),
                'text/html'
            );

        SendEmail::sendEmailForAdmin($em, $message, $this->get('mailer'), $emailData, SendEmail::TYPE_ADMIN_CANDIDATE_REQUEST_VIDEO);

        $view = $this->view([], Response::HTTP_NO_CONTENT);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/achievement")
     * @SWG\Get(path="/api/candidate/profile/achievement",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Get Candidate Achievements",
     *   description="The method for getting candidate Achievements",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="description",
     *                  type="string"
     *              ),
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
    public function getAchievementsAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $achievements = $em->getRepository("AppBundle:CandidateAchievements")->getAchievementsCandidate($this->getUser()->getId());

        $view = $this->view($achievements, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/achievement")
     * @SWG\Post(path="/api/candidate/profile/achievement",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Create Candidate Achievements",
     *   description="The method for create candidate achievement. MAX 5 Achievements",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
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
     *              property="description",
     *              example="description",
     *              description="Required. Max 50 Characters",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="object",
     *              property="achievement",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  type="string",
     *                  property="description"
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          ),
     *     )
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
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
    public function createAchievementsAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if($request->request->has('description')){
            $achievements = $em->getRepository("AppBundle:CandidateAchievements")->findBy(['user'=>$this->getUser()]);
            if(count($achievements)<5){
                $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$this->getUser()]);
                if($profileDetails instanceof ProfileDetails){
                    $achievement = new CandidateAchievements($this->getUser(), $request->request->get('description'));
                    $validator = $this->get('validator');
                    $errors = $validator->validate($achievement, null, array('validateAchievements'));
                    if(count($errors) === 0){
                        $em->persist($achievement);
                        $em->flush();

                        $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                        $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                        $em->persist($profileDetails);
                        $em->flush();

                        $view = $this->view([
                            'achievement' => [
                                'id'=> $achievement->getId(),
                                'description' => $achievement->getDescription()
                            ],
                            'percentage'=>$profileDetails->getPercentage(),
                            'looking' => $profileDetails->getLooking(),
                            'visible' => $profileDetails->getVisible()
                        ], Response::HTTP_OK);
                    }
                    else {
                        $error_description = [];
                        foreach ($errors as $er) {
                            $error_description[] = $er->getMessage();
                        }
                        $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                    }
                }
                else{
                    $view = $this->view(['error'=>'Profile Details Not Found'], Response::HTTP_NOT_FOUND);
                }
            }
            else{
                $view = $this->view(['error'=>'limit achievements. Max 5 achievements.'], Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            $view = $this->view(['error'=>'field description is required'], Response::HTTP_BAD_REQUEST);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Put("/achievement/{id}",requirements={"id"="\d+"})
     * @SWG\Put(path="/api/candidate/profile/achievement/{id}",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Edit Candidate Achievements",
     *   description="The method for edit candidate achievement",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="Achievement ID"
     *   ),
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
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
     *              property="description",
     *              example="description",
     *              description="Required. Max 50 Characters",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          ),
     *      )
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
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
     *      description="Not Found",
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
    public function editAchievementAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();

        $achievement = $em->getRepository("AppBundle:CandidateAchievements")->findOneBy(['id'=>$id,'user'=>$this->getUser()]);
        if($achievement instanceof CandidateAchievements){
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$this->getUser()]);
            if($profileDetails instanceof ProfileDetails){
                if($request->request->has('description')){
                    $achievement->setDescription($request->request->get('description'));
                    $validator = $this->get('validator');
                    $errors = $validator->validate($achievement, null, array('validateAchievements'));
                    if(count($errors) === 0){
                        $em->persist($achievement);
                        $em->flush();

                        $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                        $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                        $em->persist($profileDetails);
                        $em->flush();
                        $view = $this->view([
                            'percentage'=>$profileDetails->getPercentage(),
                            'looking' => $profileDetails->getLooking(),
                            'visible' => $profileDetails->getVisible()
                        ], Response::HTTP_OK);
                    }
                    else {
                        $error_description = [];
                        foreach ($errors as $er) {
                            $error_description[] = $er->getMessage();
                        }
                        $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                    }
                }
                else{
                    $view = $this->view(['error'=>'field description is required'], Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $view = $this->view(['error'=>'Profile Details Not Found'], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(['error'=>'Achievement not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Delete("/achievement/{id}",requirements={"id"="\d+"})
     * @SWG\Delete(path="/api/candidate/profile/achievement/{id}",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Delete Candidate Achievements",
     *   description="The method for Delete candidate achievement",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="Achievement ID"
     *   ),
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
     *      description="Not Found",
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
    public function deleteAchievementAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();

        $achievement = $em->getRepository("AppBundle:CandidateAchievements")->findOneBy(['id'=>$id,'user'=>$this->getUser()]);
        if($achievement instanceof CandidateAchievements){
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$this->getUser()]);
            if($profileDetails instanceof ProfileDetails){
                $em->remove($achievement);
                $em->flush();

                $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                $em->persist($profileDetails);
                $em->flush();
                $view = $this->view([
                    'percentage'=>$profileDetails->getPercentage(),
                    'looking' => $profileDetails->getLooking(),
                    'visible' => $profileDetails->getVisible()
                ], Response::HTTP_OK);
            }
            else{
                $view = $this->view(['error'=>'Profile Details Not Found'], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(['error'=>'Achievement not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/references")
     * @SWG\Get(path="/api/candidate/profile/references",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Get Candidate References",
     *   description="The method for getting candidate References",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="company",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="role",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="email",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="comment",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="permission",
     *                  type="boolean"
     *              ),
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
    public function getReferencesAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $references = $em->getRepository("AppBundle:CandidateReferences")->getReferencesCandidate($this->getUser()->getId(), false);

        $view = $this->view($references, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/references")
     * @SWG\Post(path="/api/candidate/profile/references",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Create Candidate Reference",
     *   description="The method for create candidate Reference. MAX 5 References",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
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
     *              property="firstName",
     *              example="firstName",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="lastName",
     *              example="lastName",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="company",
     *              example="company",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="role",
     *              example="role",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="email",
     *              example="email@gmail.com",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="comment",
     *              example="comment",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="permission",
     *              example=false,
     *              description="Required",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="object",
     *              property="reference",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="company",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="role",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="email",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="comment",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="permission",
     *                  type="boolean"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          ),
     *     )
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
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
    public function createReferencesAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if($request->request->has('firstName') && $request->request->has('lastName') && $request->request->has('company')
            && $request->request->has('role') && $request->request->has('email') && $request->request->has('comment') && $request->request->has('permission')){
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$this->getUser()]);
            if($profileDetails instanceof ProfileDetails){
                $references = $em->getRepository("AppBundle:CandidateReferences")->findBy(['user'=>$this->getUser()]);
                if(count($references)<5){
                    $reference = new CandidateReferences(
                        $this->getUser(),
                        $request->request->get('firstName'),
                        $request->request->get('lastName'),
                        $request->request->get('company'),
                        $request->request->get('role'),
                        $request->request->get('email'),
                        $request->request->get('comment'),
                        $request->request->get('permission')
                    );
                    $validator = $this->get('validator');
                    $errors = $validator->validate($reference, null, array('validateReferences'));
                    if(count($errors) === 0){
                        $em->persist($reference);
                        $em->flush();

                        $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                        $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                        $em->persist($profileDetails);
                        $em->flush();

                        $view = $this->view([
                            'reference' => [
                                'id' => $reference->getId(),
                                'firstName' => $reference->getFirstName(),
                                'lastName' => $reference->getLastName(),
                                'company' => $reference->getCompany(),
                                'role' => $reference->getRole(),
                                'email' => $reference->getEmail(),
                                'comment' => $reference->getComment(),
                                'permission' => $reference->getPermission()
                            ],
                            'percentage'=>$profileDetails->getPercentage(),
                            'looking' => $profileDetails->getLooking(),
                            'visible' => $profileDetails->getVisible()
                        ], Response::HTTP_OK);
                    }
                    else {
                        $error_description = [];
                        foreach ($errors as $er) {
                            $error_description[] = $er->getMessage();
                        }
                        $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                    }
                }
                else{
                    $view = $this->view(['error'=>'limit references. Max 5 references.'], Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $view = $this->view(['error'=>'Profile Details Not Found'], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(['error'=>'all field is required'], Response::HTTP_BAD_REQUEST);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Put("/references/{id}",requirements={"id"="\d+"})
     * @SWG\Put(path="/api/candidate/profile/references/{id}",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Edit Candidate References",
     *   description="The method for edit candidate References",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="references ID"
     *   ),
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
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
     *              property="firstName",
     *              example="firstName",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="lastName",
     *              example="lastName",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="company",
     *              example="company",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="role",
     *              example="role",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="email",
     *              example="email@gmail.com",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="comment",
     *              example="comment",
     *              description="Required",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="permission",
     *              example=false,
     *              description="Required",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          ),
     *      )
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
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
     *      description="Not Found",
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
    public function editReferencesAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();

        $reference = $em->getRepository("AppBundle:CandidateReferences")->findOneBy(['id'=>$id,'user'=>$this->getUser()]);
        if($reference instanceof CandidateReferences){
            if($request->request->has('firstName') && $request->request->has('lastName') && $request->request->has('company')
                && $request->request->has('role') && $request->request->has('email') && $request->request->has('comment') && $request->request->has('permission')){
                $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$this->getUser()]);
                if($profileDetails instanceof ProfileDetails){
                    $reference->setFirstName($request->request->get('firstName'));
                    $reference->setLastName($request->request->get('lastName'));
                    $reference->setCompany($request->request->get('company'));
                    $reference->setRole($request->request->get('role'));
                    $reference->setEmail($request->request->get('email'));
                    $reference->setComment($request->request->get('comment'));
                    $reference->setPermission($request->request->get('permission'));
                    $validator = $this->get('validator');
                    $errors = $validator->validate($reference, null, array('validateReferences'));
                    if(count($errors) === 0){
                        $em->persist($reference);
                        $em->flush();

                        $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                        $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                        $em->persist($profileDetails);
                        $em->flush();

                        $view = $this->view([
                            'percentage'=>$profileDetails->getPercentage(),
                            'looking' => $profileDetails->getLooking(),
                            'visible' => $profileDetails->getVisible()
                        ], Response::HTTP_OK);
                    }
                    else {
                        $error_description = [];
                        foreach ($errors as $er) {
                            $error_description[] = $er->getMessage();
                        }
                        $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                    }
                }
                else{
                    $view = $this->view(['error'=>'Profile Details Not Found'], Response::HTTP_NOT_FOUND);
                }
            }
            else{
                $view = $this->view(['error'=>'all field is required'], Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            $view = $this->view(['error'=>'Reference not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Delete("/references/{id}",requirements={"id"="\d+"})
     * @SWG\Delete(path="/api/candidate/profile/references/{id}",
     *   tags={"Candidate Profile"},
     *   security={true},
     *   summary="Delete Candidate References",
     *   description="The method for Delete candidate References",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="references ID"
     *   ),
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
     *          ),
     *          @SWG\Property(
     *              property="looking",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="visible",
     *              type="boolean"
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="error_error_description",
     *              type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Forbidden(Access Denied)",
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
     *      description="Not Found",
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
    public function deleteReferencesAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();

        $reference = $em->getRepository("AppBundle:CandidateReferences")->findOneBy(['id'=>$id,'user'=>$this->getUser()]);
        if($reference instanceof CandidateReferences){
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$this->getUser()]);
            if($profileDetails instanceof ProfileDetails){
                $em->remove($reference);
                $em->flush();

                $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                $em->persist($profileDetails);
                $em->flush();

                $view = $this->view([
                    'percentage'=>$profileDetails->getPercentage(),
                    'looking' => $profileDetails->getLooking(),
                    'visible' => $profileDetails->getVisible()
                ], Response::HTTP_OK);
            }
            else{
                $view = $this->view(['error'=>'Profile Details Not Found'], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(['error'=>'Reference not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($view);
    }

}