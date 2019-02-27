<?php
/**
 * Created by PhpStorm.
 * Date: 20.04.18
 * Time: 17:09
 */

namespace AppBundle\Controller\Api\Admin;


use AppBundle\Entity\Logging;
use AppBundle\Entity\NotificationCandidate;
use AppBundle\Entity\ProfileDetails;
use AppBundle\Entity\User;
use AppBundle\Helper\HelpersClass;
use AppBundle\Helper\SendWhatsApp;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class CandidateController
 * @package AppBundle\Controller\Api\Admin
 *
 * @Rest\Route("candidate")
 * @Security("has_role('ROLE_ADMIN')")
 */
class CandidateController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/api/admin/candidate/",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Get All Candidate",
     *   description="The method for getting all Candidate for admin",
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
     *      name="csv",
     *      in="query",
     *      required=false,
     *      type="boolean",
     *      default=false,
     *      description="use for export csv, for use csv = true, response only what in items"
     *   ),
     *   @SWG\Parameter(
     *      name="page",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=1,
     *      description="pagination page"
     *   ),
     *   @SWG\Parameter(
     *      name="limit",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=20,
     *      description="pagination limit"
     *   ),
     *   @SWG\Parameter(
     *      name="search",
     *      in="query",
     *      required=false,
     *      type="string",
     *      description="find by firstName or lastName or email or phone"
     *   ),
     *   @SWG\Parameter(
     *      name="articlesFirm",
     *      in="query",
     *      required=false,
     *      type="string",
     *      description="find by articlesFirm, multiselect"
     *   ),
     *   @SWG\Parameter(
     *      name="nationality",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="find by nationality. All, 1=South African, 2=Other"
     *   ),
     *   @SWG\Parameter(
     *      name="ethnicity",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="find by ethnicity"
     *   ),
     *   @SWG\Parameter(
     *      name="gender",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="find by gender"
     *   ),
     *   @SWG\Parameter(
     *      name="qualification",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="find by Qualification"
     *   ),
     *   @SWG\Parameter(
     *      name="location",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="find by location"
     *   ),
     *   @SWG\Parameter(
     *      name="criminal",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="search by criminal"
     *   ),
     *   @SWG\Parameter(
     *      name="credit",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="search by criminal"
     *   ),
     *   @SWG\Parameter(
     *      name="availability",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="search by availability"
     *   ),
     *   @SWG\Parameter(
     *      name="enabled",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="search by status"
     *   ),
     *   @SWG\Parameter(
     *      name="articlesCompletedStart",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="sort by dateArticlesCompleted"
     *   ),
     *   @SWG\Parameter(
     *      name="articlesCompletedEnd",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="sort by dateArticlesCompleted"
     *   ),
     *   @SWG\Parameter(
     *      name="profileComplete",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="search by profileComplete"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="profile",
     *                      type="object",
     *                      @SWG\Property(
     *                          property="id",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="firstName",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="lastName",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="articlesFirm",
     *                          type="string",
     *                          description="if articlesFirm = Other than show articlesFirmName"
     *                      ),
     *                      @SWG\Property(
     *                          property="articlesFirmName",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="phone",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="email",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="agentName",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="enabled",
     *                          type="boolean"
     *                      ),
     *                      @SWG\Property(
     *                          property="approved",
     *                          type="boolean"
     *                      ),
     *                      @SWG\Property(
     *                          property="percentage",
     *                          type="integer",
     *                          description="if > 50 than Yes else NO"
     *                      ),
     *                      @SWG\Property(
     *                          property="cvFiles",
     *                          type="array",
     *                          @SWG\Items(
     *                              type="object",
     *                              @SWG\Property(
     *                                  property="url",
     *                                  type="string"
     *                              ),
     *                              @SWG\Property(
     *                                  property="name",
     *                                  type="string"
     *                              ),
     *                              @SWG\Property(
     *                                  property="size",
     *                                  type="integer"
     *                              ),
     *                              @SWG\Property(
     *                                  property="approved",
     *                                  type="boolean"
     *                              ),
     *                          )
     *                      ),
     *                      @SWG\Property(
     *                          property="video",
     *                          type="object",
     *                          @SWG\Property(
     *                              property="url",
     *                              type="string"
     *                          ),
     *                          @SWG\Property(
     *                              property="name",
     *                              type="string"
     *                          ),
     *                          @SWG\Property(
     *                              property="approved",
     *                              type="boolean"
     *                          ),
     *                      ),
     *                  )
     *              ),
     *          ),
     *          @SWG\Property(
     *              type="object",
     *              property="pagination",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="current_page_number"
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="total_count"
     *              ),
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
    public function getAllCandidateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $candidates = $em->getRepository("AppBundle:User")->getAllCandidateNew($request->query->all());

        if($request->query->getBoolean('csv', false) == false){
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $candidates,
                ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
            );
            $view = $this->view([
                'items'=>$pagination->getItems(),
                'pagination' => [
                    'current_page_number' => $pagination->getCurrentPageNumber(),
                    'total_count' => $pagination->getTotalItemCount(),
                ]
            ], Response::HTTP_OK);
        }
        else{
            $result = [];
            if(!empty($candidates)){
                foreach ($candidates as $candidate){

                    $name = '';
                    if(isset($candidate['firstName'])){
                        $name .= $candidate['firstName'].' ';
                    }
                    if(isset($client['lastName'])){
                        $name .= $candidate['lastName'];
                    }
                    if($candidate['articlesFirm'] == 'Other'){
                        $articlesFirm = (isset($candidate['articlesFirmName'])) ? $candidate['articlesFirmName'] : $candidate['articlesFirm'] ;
                    }
                    else{
                        $articlesFirm = $candidate['articlesFirm'];
                    }
                    $percentage = 'No';
                    if(isset($candidate['percentage']) && $candidate['percentage'] > 50
                        && isset($candidate['video']) && !empty($candidate['video'])
                        && isset($candidate['cvFiles']) && !empty($candidate['cvFiles'])
                    ){
                        $percentage = 'Yes';
                    }
                    $candidateUser = $em->getRepository("AppBundle:User")->find($candidate['id']);
                    $whatsApp = 'No';
                    if($candidateUser instanceof User){
                        $candidateNotification = $em->getRepository("AppBundle:NotificationCandidate")->findOneBy(['user'=>$candidateUser]);
                        if($candidateNotification instanceof  NotificationCandidate){
                            if($candidateNotification->getNotifyWhatsApp() == true){
                                $whatsApp = 'Yes';
                            }
                        }
                    }
                    $result[] = [
                        'name' => $name,
                        'articlesFirm' => $articlesFirm,
                        'email' => (isset($candidate['email'])) ? $candidate['email'] : '',
                        'phone' => (isset($candidate['phone'])) ? $candidate['phone'] : '',
                        'percentage' => $percentage,
                        'whatsApp' => $whatsApp,
                        'agentName' => (isset($candidate['agentName'])) ? $candidate['agentName'] : '',
                        'active' => (isset($candidate['enabled'])) ? $candidate['enabled'] : '',
                    ];
                }
            }
            $view = $this->view($result, Response::HTTP_OK);
        }

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @Rest\Get("/count")
     * @SWG\Get(path="/api/admin/candidate/count",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Get All count Candidate",
     *   description="The method for getting all count Candidate for admin",
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
     *      name="search",
     *      in="query",
     *      required=false,
     *      type="string",
     *      description="find by firstName or lastName or email or phone"
     *   ),
     *   @SWG\Parameter(
     *      name="articlesFirm",
     *      in="query",
     *      required=false,
     *      type="string",
     *      description="find by articlesFirm, multiselect"
     *   ),
     *   @SWG\Parameter(
     *      name="nationality",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="find by nationality. All, 1=South African, 2=Other"
     *   ),
     *   @SWG\Parameter(
     *      name="ethnicity",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="find by ethnicity"
     *   ),
     *   @SWG\Parameter(
     *      name="gender",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="find by gender"
     *   ),
     *   @SWG\Parameter(
     *      name="qualification",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="find by Qualification"
     *   ),
     *   @SWG\Parameter(
     *      name="location",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="find by location"
     *   ),
     *   @SWG\Parameter(
     *      name="criminal",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="search by criminal"
     *   ),
     *   @SWG\Parameter(
     *      name="credit",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="search by criminal"
     *   ),
     *   @SWG\Parameter(
     *      name="availability",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="search by availability"
     *   ),
     *   @SWG\Parameter(
     *      name="enabled",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="search by status"
     *   ),
     *   @SWG\Parameter(
     *      name="articlesCompletedStart",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="sort by dateArticlesCompleted"
     *   ),
     *   @SWG\Parameter(
     *      name="articlesCompletedEnd",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="sort by dateArticlesCompleted"
     *   ),
     *   @SWG\Parameter(
     *      name="profileComplete",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="All",
     *      description="search by profileComplete"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="integer",
     *              property="candidateCount"
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
    public function getAllCountCandidateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository("AppBundle:User")->getAllCandidateCountNew($request->query->all());

        $view = $this->view([
            'candidateCount' => (isset($result['countCandidate']) && intval($result['countCandidate']) > 0) ? intval($result['countCandidate']) : 0
        ], Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/")
     * @SWG\Post(path="/api/admin/candidate/",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Create Candidate for Admin",
     *   description="The method for CREATE Candidate for Admin",
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
     *                  description="1=South African, 2=Other",
     *                  example=1
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
     *                  type="string",
     *                  example="city1,city2"
     *              ),
     *              @SWG\Property(
     *                  property="picture",
     *                  type="file",
     *                  example="picture1.jpg"
     *              ),
     *              @SWG\Property(
     *                  property="matricCertificate",
     *                  type="array",
     *                  @SWG\Items(type="file"),
     *                  example={"file1","file2"}
     *              ),
     *              @SWG\Property(
     *                  property="tertiaryCertificate",
     *                  type="array",
     *                  @SWG\Items(type="file"),
     *                  example={"file1","file2"}
     *              ),
     *              @SWG\Property(
     *                  property="universityManuscript",
     *                  type="array",
     *                  @SWG\Items(type="file"),
     *                  example={"file1","file2"}
     *              ),
     *              @SWG\Property(
     *                  property="creditCheck",
     *                  type="array",
     *                  @SWG\Items(type="file"),
     *                  example={"file1","file2"}
     *              ),
     *              @SWG\Property(
     *                  property="cvFiles",
     *                  type="array",
     *                  @SWG\Items(type="file"),
     *                  example={"file1","file2"}
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
     *              ),
     *              @SWG\Property(
     *                  property="video",
     *                  type="file",
     *                  example="file.mp4"
     *              ),
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success. Candidate Create"
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
    public function createCandidateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if($request->request->has('user') && !empty(($request->request->get('user')))){
            $userData = $request->request->get('user');

            if(isset($userData['firstName']) && isset($userData['lastName']) && isset($userData['email']) && isset($userData['phone'])){
                $user = new User();
                $password = substr(md5(time()),0,6);
                $user->setRegisterDetails("ROLE_CANDIDATE", $userData['firstName'], $userData['lastName'], $userData['email'], $userData['phone'], $password);
                $user->setEnabled(true);
                $user->setApproved(true);
                $errors = $this->get('validator')->validate($user, null, array('registerCandidate'));
                if(count($errors) === 0){
                    $em->persist($user);
                    $profileDetails = new ProfileDetails($user, '',null);
                    if($request->request->has('profile') && !empty($request->request->get('profile'))){
                        $dataProfile = $request->request->get('profile');
                        $profileDetails->updateForm($dataProfile);
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
                    }
                    $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                    $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                    $em->persist($profileDetails);
                    $em->flush();
                    $notificationCandidate = new NotificationCandidate($user);
                    $em->persist($notificationCandidate);
                    $em->flush();
                    $message = (new \Swift_Message('Welcome to CAs Online!'))
                        ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                        ->setTo($user->getEmail())
                        ->setBody(
                            $this->renderView('emails/user/you_registered.html.twig', [
                                'user' => $user,
                                'password' => $password,
                                'link' => $request->getSchemeAndHttpHost()
                            ]),
                            'text/html'
                        );
                    try{
                        $this->get('mailer')->send($message);
                    }catch(\Swift_TransportException $e){

                    }
                    $files = [];
                    if(!empty($request->files->all())){
                        foreach ($request->files->all() as $key=>$fileArray){
                            $methodName = 'set'.ucfirst($key);
                            $methodNameGet = 'get'.ucfirst($key);
                            if(property_exists(ProfileDetails::class,$key) && method_exists(ProfileDetails::class,$methodName) && method_exists(ProfileDetails::class,$methodNameGet)){
                                $files[$key] = [];
                                if($key == 'video'){
                                    if($fileArray instanceof UploadedFile){
                                        $fileName = $user->getFirstName()."_".$user->getId().".".$fileArray->getClientOriginalExtension();
                                        if($fileArray->move("uploads/candidate/".$user->getId(),$fileName)){

                                            $credentials = new Credentials($this->container->getParameter('aws_key'), $this->container->getParameter('aws_secret'));
                                            $s3Client = new S3Client([
                                                'version'     => 'latest',
                                                'region'      => $this->container->getParameter('aws_region'),
                                                'credentials' => $credentials
                                            ]);

                                            try {
                                                $resultAws = $s3Client->putObject(array(
                                                    'Bucket' => $this->container->getParameter('aws_bucket'),
                                                    'Key'    => $fileName,
                                                    'SourceFile' => "uploads/candidate/".$user->getId()."/".$fileName,
                                                    'ACL' => 'public-read'
                                                ));
                                            } catch (\Exception $e) {

                                            }
                                            if(isset($resultAws) && isset($resultAws['ObjectURL'])){
                                                $filePath = $resultAws['ObjectURL'];
                                                $fileSystem = new Filesystem();
                                                if($fileSystem->exists("uploads/candidate/".$user->getId()."/".$fileName)){
                                                    try{
                                                        $fileSystem->remove("uploads/candidate/".$user->getId()."/".$fileName);
                                                    }
                                                    catch (\Exception $e){}
                                                }

                                            }
                                            else{
                                                $filePath = $request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName;
                                            }

                                            $files[$key] = [
                                                'url'=>$filePath,
                                                'adminUrl'=>$filePath,
                                                'name'=>$fileName,
                                                'approved'=>false
                                            ];
                                        }
                                    }
                                }
                                elseif(is_array($fileArray)){
                                    foreach ($fileArray as $fileUpload){
                                        if($fileUpload instanceof UploadedFile){
                                            $fileName = md5(uniqid()).'.'.$fileUpload->getClientOriginalExtension();
                                            if($fileUpload->move("uploads/candidate/".$user->getId(),$fileName)){
                                                $files[$key][] = [
                                                    'url'=>$request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName,
                                                    'adminUrl'=>$request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName,
                                                    'name'=>$fileUpload->getClientOriginalName(),
                                                    'size'=>$fileUpload->getClientSize(),
                                                    'approved'=>true
                                                ];
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
                                                'adminUrl'=>$request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName,
                                                'name'=>$fileArray->getClientOriginalName(),
                                                'size'=>$fileArray->getClientSize(),
                                                'approved'=>true
                                            ];
                                        }
                                    }
                                }

                                $profileDetails->$methodName($files[$key]);
                            }
                            else{
                                $view = $this->view(['error'=>'field '.$key.' not found'], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }

                        }
                        $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                        $em->persist($profileDetails);
                        $em->flush();
                    }
                    $logging = new Logging($this->getUser(),1, $user->getFirstName()." ".$user->getLastName());
                    $em->persist($logging);
                    $em->flush();
                    $view = $this->view([], Response::HTTP_NO_CONTENT);
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
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @Rest\Get("/{id}",requirements={"id"="\d+"})
     * @SWG\Get(path="/api/admin/candidate/{id}",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Get Candidate Details",
     *   description="The method for getting Candidate details for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateId",
     *      description="Candidate ID"
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
     *                  property="phone",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="email",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="agentName",
     *                  type="string",
     *                  example="agentName"
     *              )
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
     *                  description="1=South African, 2=Other",
     *                  example=1
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
     *                  type="boolean",
     *                  example=false
     *              ),
     *              @SWG\Property(
     *                  property="creditDescription",
     *                  type="string",
     *                  description="show when credit true"
     *              ),
     *              @SWG\Property(
     *                  property="boards",
     *                  type="integer",
     *                  example=1,
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
     *                  type="string"
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
     *                  ),
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
     *      description="Not found",
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
    public function getCandidateDetailsByIdAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User and $user->hasRole('ROLE_CANDIDATE')){
            $userDetails = $em->getRepository("AppBundle:User")->getCandidateProfile($user->getId());
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->getCandidateDetails($user->getId());

            $view = $this->view(['user'=>$userDetails,'profile'=>$profileDetails], Response::HTTP_OK);
        }
        else{
            $view = $this->view(['error'=>'Candidate Not found or user not has ROLE_CANDIDATE'], Response::HTTP_NOT_FOUND);
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
     * @Rest\Put("/{id}",requirements={"id"="\d+"})
     * @SWG\Put(path="/api/admin/candidate/{id}",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Edit Candidate Profile Details For Admin",
     *   description="The method for Edit Candidate profile details for Admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateId",
     *      description="candidateId"
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
     *              @SWG\Property(
     *                  property="agentName",
     *                  type="string",
     *                  example="agentName"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="profile",
     *              type="object",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="saicaStatus",
     *                  example=1,
     *                  description="required.
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
     *                  example="Other",
     *                  description="required"
     *              ),
     *              @SWG\Property(
     *                  type="string",
     *                  property="articlesFirmName",
     *                  example="TEST",
     *                  description="required for candidate when articlesFirm = Other",
     *              ),
     *             @SWG\Property(
     *                  property="nationality",
     *                  type="integer",
     *                  description="1=South African, 2=Other",
     *                  example=1
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
     *                  type="string",
     *                  example="city1,city2"
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
     *      description="Success. Updated.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
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
    public function editCandidateDetailsByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);

        if($user instanceof User && $user->hasRole("ROLE_CANDIDATE")){
            if($request->request->has('user') && !empty(($request->request->get('user')))){
                $userData = $request->request->get('user');

                if(isset($userData['firstName']) && isset($userData['lastName']) && isset($userData['email']) && isset($userData['phone'])){
                    $user->setFirstName($userData['firstName']);
                    $user->setLastName($userData['lastName']);
                    $user->setEmail($userData['email']);
                    $user->setUsername($userData['email']);
                    $user->setPhone($userData['phone']);
                    $user->setAgentName((isset($userData['agentName'])) ? $userData['agentName'] : null);
                    $errors = $this->get('validator')->validate($user, null, array('updateCandidate'));
                    if(count($errors) === 0){
                        $em->persist($user);
                        $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
                        if($request->request->has('profile') && !empty($request->request->get('profile'))){
                            $dataProfile = $request->request->get('profile');

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
                        }
                        $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                        $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                        $em->persist($profileDetails);
                        $em->flush();
                        $logging = new Logging($this->getUser(),2, $user->getFirstName()." ".$user->getLastName(), $user->getId());
                        $em->persist($logging);
                        $em->flush();
                        $view = $this->view(['percentage'=>$profileDetails->getPercentage()], Response::HTTP_OK);
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
        }
        else{
            $view = $this->view(['error'=>['User NOT Found OR User not has ROLE_CANDIDATE']], Response::HTTP_NOT_FOUND);
        }


        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Patch("/{id}",requirements={"id"="\d+"})
     * @SWG\Patch(path="/api/admin/candidate/{id}",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Update Candidate Status",
     *   description="The method for updating Candidate status for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateId",
     *      description="Candidate ID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="enabled",
     *              type="boolean",
     *              example=false,
     *              description="required"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success.Status updated"
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
     *      description="Not found",
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
    public function editCandidateStatusByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User and $user->hasRole('ROLE_CANDIDATE')){
            if($request->request->has('enabled') && is_bool($request->request->get('enabled'))){
                $user->setEnabled($request->request->get('enabled'));
                $user->setApproved($request->request->get('enabled'));
                $em->persist($user);
                $em->flush();
                if($request->request->get('enabled')){
                    $logging = new Logging($this->getUser(),3, $user->getFirstName()." ".$user->getLastName(), $user->getId());
                    $em->persist($logging);
                    $em->flush();
                }
                else{
                    $logging = new Logging($this->getUser(),4, $user->getFirstName()." ".$user->getLastName(), $user->getId());
                    $em->persist($logging);
                    $em->flush();
                }
                $view = $this->view([], Response::HTTP_NO_CONTENT);
            }
            else{
                $view = $this->view(['error'=>'field enabled should be empty and should be boolean type'], Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            $view = $this->view(['error'=>'Candidate Not found or user not has ROLE_CANDIDATE'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Delete("/{id}",requirements={"id"="\d+"})
     * @SWG\Delete(path="/api/admin/candidate/{id}",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Delete Candidate Profile",
     *   description="The method for Delete Candidate for admin",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateID",
     *      description="Candidate ID"
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
     *      response=204,
     *      description="Success. Candidate"
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
    public function removeCandidateByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User && $user->hasRole("ROLE_CANDIDATE")){
            $em->remove($user);
            $em->flush();
            $logging = new Logging($this->getUser(),5, $user->getFirstName()." ".$user->getLastName());
            $em->persist($logging);
            $em->flush();
            $view = $this->view([], Response::HTTP_NO_CONTENT);
        }
        else{
            $view = $this->view(['error'=>'Candidate Not found'], Response::HTTP_NOT_FOUND);
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
     * @Rest\Post("/{id}/file",requirements={"id"="\d+"})
     * @SWG\Post(path="/api/admin/candidate/{id}/file",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Upload Candidate File",
     *   description="The method for Upload candidate File for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateId",
     *      description="Candidate ID"
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
     *              type="integer",
     *              example=50
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
    public function uploadCandidateFileByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User && $user->hasRole("ROLE_CANDIDATE")){
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
            if($profileDetails instanceof ProfileDetails){
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
                                                'adminUrl'=>$request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName,
                                                'name'=>$fileUpload->getClientOriginalName(),
                                                'size'=>$fileUpload->getClientSize(),
                                                'approved'=>true
                                            ];
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
                                            'adminUrl'=>$request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName,
                                            'name'=>$fileArray->getClientOriginalName(),
                                            'size'=>$fileArray->getClientSize(),
                                            'approved'=>true
                                        ];
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
                    $view = $this->view(['percentage'=>$profileDetails->getPercentage(), 'files'=>$files], Response::HTTP_OK);
                }
                else{
                    $view = $this->view(['error'=>'Files is empty'], Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $view = $this->view(['error'=>'Profile Not found'], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(['error'=>'Candidate Not Found OR user not has ROLE_CANDIDATE'], Response::HTTP_NOT_FOUND);
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
     * @Rest\Patch("/{id}/file",requirements={"id"="\d+"})
     * @SWG\Patch(path="/api/admin/candidate/{id}/file",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Remove Candidate File",
     *   description="The method for remove candidate File for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateId",
     *      description="Candidate ID"
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
    public function removeCandidateFileByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User && $user->hasRole("ROLE_CANDIDATE")){
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
                                    if($checkFile == true){
                                        $newFiles = [];
                                        if(!empty($files)){
                                            foreach ($files as $f){
                                                $newFiles[] = $f;
                                            }
                                        }
                                        $profileDetails->$methodNameSet($newFiles);
                                        $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                                        $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                                        $em->persist($profileDetails);
                                        $em->flush();
                                        $view = $this->view([$key=>$profileDetails->$methodNameGet()], Response::HTTP_OK);
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
        }

        $view = $this->view(['error'=>'Candidate Not Found OR user not has ROLE_CANDIDATE'], Response::HTTP_NOT_FOUND);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/{id}/video",requirements={"id"="\d+"})
     * @SWG\Post(path="/api/admin/candidate/{id}/video",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Upload Candidate Video",
     *   description="The method for Upload candidate Video for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateId",
     *      description="Candidate ID"
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
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="percentage",
     *              type="integer",
     *              example=50
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
     *              ),
     *              @SWG\Property(
     *                  property="approved",
     *                  type="boolean"
     *              ),
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
    public function uploadCandidateVideoByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User && $user->hasRole("ROLE_CANDIDATE")){
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
            if($profileDetails instanceof ProfileDetails){
                if(!empty($request->files->get('video'))){
                    $fileUpload = $request->files->get('video');
                    if($fileUpload instanceof UploadedFile){
                        $fileName = $user->getFirstName()."_".$user->getId().".".$fileUpload->getClientOriginalExtension();
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
                        }
                        else{
                            $filePath = $request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName;
                        }
                        $video = [
                            'url'=>$filePath,
                            'adminUrl'=>$filePath,
                            'name'=>$fileName,
                            'approved'=>false
                        ];

                        $profileDetails->setVideo($video);
                        $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                        $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                        $em->persist($profileDetails);
                        $logging = new Logging($this->getUser(),29, $user->getFirstName()." ".$user->getLastName(), $user->getId());
                        $em->persist($logging);
                        $em->flush();
                        $view = $this->view(['percentage'=>$profileDetails->getPercentage(), 'video'=>$video], Response::HTTP_OK);
                    }
                    else{
                        $view = $this->view(['error'=>'Choose video'], Response::HTTP_BAD_REQUEST);
                    }
                }
                else{
                    $view = $this->view(['error'=>'Files is empty'], Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $view = $this->view(['error'=>'Profile Not found'], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(['error'=>'Candidate Not Found OR user not has ROLE_CANDIDATE'], Response::HTTP_NOT_FOUND);
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
     * @Rest\Delete("/{id}/video",requirements={"id"="\d+"})
     * @SWG\Delete(path="/api/admin/candidate/{id}/video",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Remove Candidate video",
     *   description="The method for remove candidate video for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateId",
     *      description="Candidate ID"
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
    public function removeCandidateVideoByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User && $user->hasRole("ROLE_CANDIDATE")){
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
                $logging = new Logging($this->getUser(),30, $user->getFirstName()." ".$user->getLastName(), $user->getId());
                $em->persist($logging);
                $em->flush();
                $view = $this->view(['percentage'=>$profileDetails->getPercentage()], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view(['error'=>'Profile Not Found'], Response::HTTP_NOT_FOUND);
                return $this->handleView($view);
            }
        }

        $view = $this->view(['error'=>'Candidate Not Found OR user not has ROLE_CANDIDATE'], Response::HTTP_NOT_FOUND);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/file/approve")
     * @SWG\Get(path="/api/admin/candidate/file/approve",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Get All Files Candidate when need approve",
     *   description="The method for getting all candidate files when need approve for admin",
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
     *      name="page",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=1,
     *      description="pagination page"
     *   ),
     *   @SWG\Parameter(
     *      name="limit",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=20,
     *      description="pagination limit"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="userId",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="firstName",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="lastName",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="url",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="adminUrl",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="fileName",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="fieldName",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="string"
     *                  ),
     *              ),
     *          ),
     *          @SWG\Property(
     *              type="object",
     *              property="pagination",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="current_page_number"
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="total_count"
     *              ),
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
    public function getFileApproveAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $files = $em->getRepository("AppBundle:User")->getCandidateFilesApprove($request->query->all());

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $files,
            ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
            ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
        );
        $view = $this->view([
            'items'=>$pagination->getItems(),
            'pagination' => [
                'current_page_number' => $pagination->getCurrentPageNumber(),
                'total_count' => $pagination->getTotalItemCount(),
            ]
        ], Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $userId
     * @return Response
     *
     * @Rest\Post("/file/{userId}/approve",requirements={"userId"="\d+"})
     * @SWG\Post(path="/api/admin/candidate/file/{userId}/approve",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="UPLOAD Approve Candidate File for Admin",
     *   description="The method for upload Approve Candidate File for Admin",
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
     *      name="userId",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateID",
     *      description="Candidate ID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="fieldName",
     *              type="string",
     *              example="fieldName",
     *              description="required. matricCertificate OR tertiaryCertificate OR universityManuscript OR creditCheck OR cvFiles"
     *          ),
     *          @SWG\Property(
     *              property="file",
     *              type="string",
     *              example="file.png",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="url",
     *              type="string",
     *              example="url",
     *              description="required"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="adminUrl",
     *              type="string",
     *              example="adminUrl"
     *          )
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
     *      description="Not FOUND",
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
    public function candidateUploadFileApprovedAction(Request $request, $userId){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($userId);
        if($user instanceof User){
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
            if($profileDetails instanceof ProfileDetails){
                if($request->request->has('fieldName') && !empty($request->request->get('fieldName'))){
                    if($request->files->has('file') && !empty($request->files->get('file'))){
                        $fileUpload = $request->files->get('file');
                        if($fileUpload instanceof UploadedFile){
                            if($request->request->has('url') && !empty($request->request->get('url'))){
                                $methodGet = 'get'.ucfirst($request->request->get('fieldName'));
                                $methodSet = 'set'.ucfirst($request->request->get('fieldName'));
                                if(property_exists(ProfileDetails::class,$request->request->get('fieldName')) && method_exists(ProfileDetails::class,$methodGet) && method_exists(ProfileDetails::class,$methodSet)) {
                                    $files = $profileDetails->$methodGet();
                                    if(!empty($files)) {
                                        foreach ($files as $key=>$file){
                                            if(isset($file['url']) && $file['url'] == $request->request->get('url')){
                                                $fileName = md5(uniqid()).'.'.$fileUpload->getClientOriginalExtension();
                                                try {
                                                    $fileUpload->move("uploads/candidate/".$user->getId(),$fileName);
                                                } catch (\Exception $e) {
                                                    $view = $this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                                                    return $this->handleView($view);
                                                }
                                                $files[$key]['adminUrl'] = $request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName;
                                                $profileDetails->$methodSet($files);
                                                $em->persist($profileDetails);
                                                $em->flush();

                                                $view = $this->view([
                                                    'adminUrl'=>$request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName
                                                ], Response::HTTP_OK);
                                                return $this->handleView($view);
                                            }
                                        }
                                    }
                                    $view = $this->view(['error'=>'Candidate File NOT FOUND'], Response::HTTP_NOT_FOUND);
                                }
                                else{
                                    $view = $this->view(['error'=>'fieldName is invalid'], Response::HTTP_BAD_REQUEST);
                                }
                            }
                            else{
                                $view = $this->view(['error'=>'url is required and should be not empty'], Response::HTTP_BAD_REQUEST);
                            }
                        }
                        else{
                            $view = $this->view(['error'=>'Is not file'], Response::HTTP_BAD_REQUEST);
                        }
                    }
                    else{
                        $view = $this->view(['error'=>'file is required and should be not empty'], Response::HTTP_BAD_REQUEST);
                    }
                }
                else{
                    $view = $this->view(['error'=>'fieldName is required and should be not empty'], Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $view = $this->view(['error'=>'Candidate Details Not found'], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(['error'=>'Candidate Not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @param $userId
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Patch("/file/{userId}/approve",requirements={"userId"="\d+"})
     * @SWG\Patch(path="/api/admin/candidate/file/{userId}/approve",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Approve Candidate File for Admin",
     *   description="The method for Approve Candidate File for Admin",
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
     *      name="userId",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateID",
     *      description="Candidate ID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="fieldName",
     *              type="string",
     *              example="fieldName",
     *              description="required. matricCertificate OR tertiaryCertificate OR universityManuscript OR creditCheck OR cvFiles"
     *          ),
     *          @SWG\Property(
     *              property="url",
     *              type="string",
     *              example="url",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="approved",
     *              type="boolean",
     *              example=true,
     *              description="required, true=approve,false=decline"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success. Candidate File Approve or Decline",
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
     *      description="Not FOUND",
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
    public function candidateFileApprovedAction(Request $request, $userId){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($userId);
        if($user instanceof User){
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
            if($profileDetails instanceof ProfileDetails){
                if($request->request->has('fieldName') && $request->request->has('url') && $request->request->has('approved')){
                    if(is_bool($request->request->get('approved'))){
                        $methodGet = 'get'.ucfirst($request->request->get('fieldName'));
                        $methodSet = 'set'.ucfirst($request->request->get('fieldName'));
                        if(property_exists(ProfileDetails::class,$request->request->get('fieldName')) && method_exists(ProfileDetails::class,$methodGet) && method_exists(ProfileDetails::class,$methodSet)){
                            $files = $profileDetails->$methodGet();
                            if(!empty($files)){
                                $checkFile = false;
                                foreach ($files as $key=>$file){
                                    if($file['url'] == $request->request->get('url')){
                                        $checkFile = true;
                                        if($request->request->get('approved') == true){
                                            if(isset($files[$key]['adminUrl']) && !empty($files[$key]['adminUrl'])){
                                                $files[$key]['approved'] = true;
                                                $logging = new Logging($this->getUser(),6, $file['name']);
                                                $em->persist($logging);
                                                $em->flush();
                                                $notifyCandidate = $em->getRepository("AppBundle:NotificationCandidate")->findOneBy(['user'=>$user,'videoApproveStatus'=>true]);
                                                if($notifyCandidate instanceof NotificationCandidate){
                                                    if($notifyCandidate->getNotifyEmail() == true && $notifyCandidate->getDocumentApproveStatus() == true){
                                                        $message = (new \Swift_Message('Your document has been approved on CAs Online'))
                                                            ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                                                            ->setTo($user->getEmail())
                                                            ->setBody(
                                                                $this->renderView('emails/candidate/document_approve.html.twig', [
                                                                    'candidate' => [
                                                                        'firstName'=>$user->getFirstName()
                                                                    ],
                                                                    'link' => $request->getSchemeAndHttpHost().'/candidate/view_cv'
                                                                ]),
                                                                'text/html'
                                                            );

                                                        try{
                                                            $this->get('mailer')->send($message);
                                                        }catch(\Swift_TransportException $e){

                                                        }
                                                    }
                                                    if($notifyCandidate->getNotifyWhatsApp() == true && $notifyCandidate->getDocumentApproveStatus() == true){
                                                        if(!empty($user->getPhone())){
                                                            if(substr($user->getPhone(), 0, 1) == '+'){
                                                                $number = substr($user->getPhone(), 1);
                                                            }
                                                            else{
                                                                $number = $user->getPhone();
                                                            }
                                                            $message = "Hi ".$user->getFirstName()."!\n\n";
                                                            $message .= "Your document that you recently uploaded on CAs Online has been approved\n\n";
                                                            $message .= "Please login to CAs Online to view your profile.\n\n";
                                                            $message .= $request->getSchemeAndHttpHost()." \n\n";
                                                            $message .= "CAs Online Team\n";
                                                            $message .= "info@casonline.co.za";
                                                            if(isset($number) && !empty($number) && isset($message) && !empty($message)){
                                                                $postData = array(
                                                                    'number' => $number,
                                                                    'message' => $message
                                                                );
                                                                $sendWhatsApp = new SendWhatsApp();
                                                                $sendWhatsApp->sendSingleText($postData);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            else{
                                                $view = $this->view(['error'=>'An Admin document version is required'], Response::HTTP_BAD_REQUEST);
                                                return $this->handleView($view);
                                            }
                                        }
                                        else{
                                            $fileSystem = new Filesystem();
                                            $parse = parse_url($file['url']);
                                            if(isset($parse['path']) && !empty($parse['path'])){
                                                $parse['path'] = ltrim($parse['path'], '/');
                                                if($fileSystem->exists($parse['path'])){
                                                    $fileSystem->remove($parse['path']);
                                                }
                                                unset($files[$key]);
                                                $logging = new Logging($this->getUser(),7, $file['name']);
                                                $em->persist($logging);
                                                $em->flush();
                                            }
                                        }
                                    }
                                }
                                if($checkFile === true){
                                    $newFiles = [];
                                    if(!empty($files)){
                                        foreach ($files as $f){
                                            $newFiles[] = $f;
                                        }
                                    }
                                    $profileDetails->$methodSet($newFiles);
                                    $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                                    $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                                    $em->persist($profileDetails);
                                    $em->flush();
                                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                                }
                                else{
                                    $view = $this->view(['error'=>'file not found'], Response::HTTP_BAD_REQUEST);
                                }
                            }
                            else{
                                $view = $this->view(['error'=>'file not found'], Response::HTTP_BAD_REQUEST);
                            }
                        }
                        else{
                            $view = $this->view(['error'=>'fieldName field is invalid'], Response::HTTP_BAD_REQUEST);
                        }
                    }
                    else{
                        $view = $this->view(['error'=>'approved field should be boolean type'], Response::HTTP_BAD_REQUEST);
                    }
                }
                else{
                    $view = $this->view(['error'=>'all fields required'], Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $view = $this->view(['error'=>'Candidate Details Not found'], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(['error'=>'Candidate Not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/video/approve")
     * @SWG\Get(path="/api/admin/candidate/video/approve",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Get All videos Candidate when need approve",
     *   description="The method for getting all candidate videos when need approve for admin",
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
     *      name="page",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=1,
     *      description="pagination page"
     *   ),
     *   @SWG\Parameter(
     *      name="limit",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=20,
     *      description="pagination limit"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="userId",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="firstName",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="lastName",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="url",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="adminUrl",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="fileName",
     *                      type="string"
     *                  ),
     *              ),
     *          ),
     *          @SWG\Property(
     *              type="object",
     *              property="pagination",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="current_page_number"
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="total_count"
     *              ),
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
    public function getVideoApproveAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $video = $em->getRepository("AppBundle:User")->getCandidateVideosApprove();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $video,
            ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
            ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
        );
        $view = $this->view([
            'items'=>$pagination->getItems(),
            'pagination' => [
                'current_page_number' => $pagination->getCurrentPageNumber(),
                'total_count' => $pagination->getTotalItemCount(),
            ]
        ], Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $userId
     * @return Response
     *
     * @Rest\Post("/video/{userId}/approve",requirements={"userId"="\d+"})
     * @SWG\Post(path="/api/admin/candidate/video/{userId}/approve",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="UPLOAD Approve Candidate video for Admin",
     *   description="The method for upload Approve Candidate video for Admin",
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
     *      name="userId",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateID",
     *      description="Candidate ID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="video",
     *              type="string",
     *              example="video.mp4",
     *              description="required"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="adminUrl",
     *              type="string",
     *              example="adminUrl"
     *          )
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
     *      description="Not FOUND",
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
    public function candidateUploadVideoApprovedAction(Request $request, $userId){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($userId);
        if($user instanceof User){
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
            if($profileDetails instanceof ProfileDetails){
                if(!empty($profileDetails->getVideo())){
                    if($request->files->has('video') && !empty($request->files->get('video'))) {
                        $fileUpload = $request->files->get('video');
                        if ($fileUpload instanceof UploadedFile) {
                            $fileName = $user->getFirstName()."_".$user->getId()."_AV.".$fileUpload->getClientOriginalExtension();
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
                            }
                            else{
                                $filePath = $request->getSchemeAndHttpHost()."/uploads/candidate/".$user->getId()."/".$fileName;
                            }

                            $video = $profileDetails->getVideo();
                            $video['adminUrl'] = $filePath;
                            $profileDetails->setVideo($video);
                            $em->persist($profileDetails);
                            $em->flush();

                            $view = $this->view(['adminUrl'=>$filePath], Response::HTTP_OK);
                        }
                        else{
                            $view = $this->view(['error'=>'Is not video'], Response::HTTP_BAD_REQUEST);
                        }
                    }
                    else{
                        $view = $this->view(['error'=>'video is required and should be not empty'], Response::HTTP_BAD_REQUEST);
                    }
                }
                else{
                    $view = $this->view(['error'=>'Candidate video NOT FOUND'], Response::HTTP_NOT_FOUND);
                }
            }
            else{
                $view = $this->view(['error'=>'Candidate Details Not found'], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(['error'=>'Candidate Not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $userId
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Patch("/video/{userId}/approve",requirements={"userId"="\d+"})
     * @SWG\Patch(path="/api/admin/candidate/video/{userId}/approve",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Approve Candidate video for Admin",
     *   description="The method for Approve Candidate video for Admin",
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
     *      name="userId",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateID",
     *      description="Candidate ID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="approved",
     *              type="boolean",
     *              example=true,
     *              description="required, true=approve,false=decline"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success. Candidate Video Approve or Decline",
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
     *      description="Not FOUND",
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
    public function candidateVideoApprovedAction(Request $request, $userId){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($userId);
        if($user instanceof User){
            $profileDetails = $em->getRepository("AppBundle:ProfileDetails")->findOneBy(['user'=>$user]);
            if($profileDetails instanceof ProfileDetails){
                if($request->request->has('approved') && is_bool($request->request->getBoolean('approved'))){
                    $video = $profileDetails->getVideo();
                    if($request->request->getBoolean('approved') == true){
                        $video['approved'] = true;
                        $logging = new Logging($this->getUser(),27, $user->getFirstName()." ".$user->getLastName(), $user->getId());
                        $em->persist($logging);
                        $notifyCandidate = $em->getRepository("AppBundle:NotificationCandidate")->findOneBy(['user'=>$user,'videoApproveStatus'=>true]);
                        if($notifyCandidate instanceof NotificationCandidate){
                            if($notifyCandidate->getNotifyEmail() == true && $notifyCandidate->getVideoApproveStatus() == true){
                                $message = (new \Swift_Message('Your Video has been approved on CAs Online'))
                                    ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                                    ->setTo($user->getEmail())
                                    ->setBody(
                                        $this->renderView('emails/candidate/video_approve.html.twig', [
                                            'candidate' => [
                                                'firstName'=>$user->getFirstName()
                                            ],
                                            'link' => $request->getSchemeAndHttpHost().'/candidate/view_cv'
                                        ]),
                                        'text/html'
                                    );

                                try{
                                    $this->get('mailer')->send($message);
                                }catch(\Swift_TransportException $e){

                                }
                            }
                            if($notifyCandidate->getNotifyWhatsApp() == true && $notifyCandidate->getVideoApproveStatus() == true){
                                if(!empty($user->getPhone())){
                                    if(substr($user->getPhone(), 0, 1) == '+'){
                                        $number = substr($user->getPhone(), 1);
                                    }
                                    else{
                                        $number = $user->getPhone();
                                    }
                                    $message = "Hi ".$user->getFirstName()."!\n\n";
                                    $message .= "Your video that you recently uploaded on CAs Online has been approved.\n\n";
                                    $message .= "Please login to CAs Online to view your profile.\n\n";
                                    $message .= $request->getSchemeAndHttpHost()." \n\n";
                                    $message .= "CAs Online Team\n";
                                    $message .= "info@casonline.co.za";
                                    if(isset($number) && !empty($number) && isset($message) && !empty($message)){
                                        $postData = array(
                                            'number' => $number,
                                            'message' => $message
                                        );
                                        $sendWhatsApp = new SendWhatsApp();
                                        $sendWhatsApp->sendSingleText($postData);
                                    }
                                }
                            }
                        }

                    }
                    else{
                        $video['approved'] = false;
                        $logging = new Logging($this->getUser(),28, $user->getFirstName()." ".$user->getLastName(), $user->getId());
                        $em->persist($logging);
                    }
                    $profileDetails->setVideo($video);
                    $profileDetails = HelpersClass::candidateProfileCompletePercentage($profileDetails, $em);
                    $profileDetails = HelpersClass::checkAutoVisible($profileDetails, $em);
                    $em->persist($profileDetails);
                    $em->flush();
                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                }
                else{
                    $view = $this->view(['error'=>'approved field should be boolean type'], Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $view = $this->view(['error'=>'Candidate Details Not found'], Response::HTTP_NOT_FOUND);
            }
        }
        else{
            $view = $this->view(['error'=>'Candidate Not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/approve")
     * @SWG\Get(path="/api/admin/candidate/approve",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Get Candidate when need Approve",
     *   description="The method for getting Candidate when need Approve",
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
     *      name="page",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=1,
     *      description="pagination page"
     *   ),
     *   @SWG\Parameter(
     *      name="limit",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=20,
     *      description="pagination limit"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="firstName",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="lastName",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="articlesFirm",
     *                      type="string",
     *                      description="if = Other show articlesFirmName"
     *                  ),
     *                  @SWG\Property(
     *                      property="articlesFirmName",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="phone",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="email",
     *                      type="string"
     *                  ),
     *              ),
     *          ),
     *          @SWG\Property(
     *              type="object",
     *              property="pagination",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="current_page_number"
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="total_count"
     *              ),
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
    public function getCandidateApproveAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $candidateApprove = $em->getRepository("AppBundle:User")->getCandidateApprove($request->query->all());

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $candidateApprove,
            ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
            ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
        );
        $view = $this->view([
            'items'=>$pagination->getItems(),
            'pagination' => [
                'current_page_number' => $pagination->getCurrentPageNumber(),
                'total_count' => $pagination->getTotalItemCount(),
            ]
        ], Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Patch("/{id}/approve",requirements={"id"="\d+"})
     * @SWG\Patch(path="/api/admin/candidate/{id}/approve",
     *   tags={"Admin Candidate"},
     *   security={true},
     *   summary="Approve Candidate Account for Admin",
     *   description="The method for Approve Candidate Account for Admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="candidateID",
     *      description="Candidate ID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="approved",
     *              type="boolean",
     *              example=true,
     *              description="required"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success. Candidate Approved or Decline",
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
     *      description="Not FOUND",
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
    public function candidateApprovedAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $candidate = $em->getRepository("AppBundle:User")->find($id);
        if($candidate instanceof User){
            if($candidate->hasRole("ROLE_CANDIDATE")){
                if($request->request->has('approved') && is_bool($request->request->get('approved'))){
                    $candidate->setApproved($request->request->get('approved'));
                    $candidate->setEnabled($request->request->get('approved'));
                    $em->persist($candidate);
                    $em->flush();
                    if($candidate->getApproved() === true){
                        $message = (new \Swift_Message('Welcome to CAs Online!'))
                            ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                            ->setTo($candidate->getEmail())
                            ->setBody(
                                $this->renderView('emails/candidate/candidate_approved.html.twig', [
                                    'user' => $candidate,
                                    'link' => $request->getSchemeAndHttpHost()
                                ]),
                                'text/html'
                            );
                        $logging = new Logging($this->getUser(),8, $candidate->getFirstName()." ".$candidate->getLastName(), $candidate->getId());
                        $em->persist($logging);
                        $em->flush();

                        try{
                            $this->get('mailer')->send($message);
                        }catch(\Swift_TransportException $e){

                        }

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                    }
                    else{
                        $message = (new \Swift_Message('CAs Online Registration declined'))
                            ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                            ->setTo($candidate->getEmail())
                            ->setBody(
                                $this->renderView('emails/candidate/candidate_decline.html.twig', [
                                    'user' => $candidate
                                ]),
                                'text/html'
                            );
                        $logging = new Logging($this->getUser(),9, $candidate->getFirstName()." ".$candidate->getLastName(), $candidate->getId());
                        $em->persist($logging);
                        $em->flush();
                        try{
                            $this->get('mailer')->send($message);
                        }catch(\Swift_TransportException $e){

                        }

                        $em->remove($candidate);
                        $em->flush();

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                    }

                }
                else{
                    $view = $this->view(['error'=>'field approved is required or npt boolean'], Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $view = $this->view(['error'=>'user NOT ROLE_CANDIDATE'], Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            $view = $this->view(['error'=>'User not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }
}