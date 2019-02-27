<?php
/**
 * Created by PhpStorm.
 * Date: 24.04.18
 * Time: 12:44
 */

namespace AppBundle\Controller\Api\Business;

use AppBundle\Entity\Applicants;
use AppBundle\Entity\CompanyDetails;
use AppBundle\Entity\HideJob;
use AppBundle\Entity\Interviews;
use AppBundle\Entity\Job;
use AppBundle\Entity\Opportunities;
use AppBundle\Entity\ProfileDetails;
use AppBundle\Entity\Settings;
use AppBundle\Entity\User;
use AppBundle\Helper\HelpersClass;
use AppBundle\Helper\SendEmail;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class JobController
 * @package AppBundle\Controller\Api\Business
 *
 * @Rest\Route("job")
 * @Security("has_role('ROLE_CLIENT')")
 */
class JobController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/api/business/job/",
     *   tags={"Business Job"},
     *   security={true},
     *   summary="Get All Jobs",
     *   description="The method for getting all jobs for business",
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
     *   @SWG\Parameter(
     *      name="status",
     *      in="query",
     *      required=false,
     *      type="boolean",
     *      default=true,
     *      description="Sort by Status. true=open, false=close. Default true"
     *   ),
     *   @SWG\Parameter(
     *     name="approve",
     *     in="query",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description="Sort by approve. true=is approve, false=decline, null=awaiting"
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
     *                      property="jobTitle",
     *                      type="string",
     *                      example="jobTitle"
     *                  ),
     *                  @SWG\Property(
     *                      property="jobAddress",
     *                      type="string",
     *                      example="jobAddress"
     *                  ),
     *                  @SWG\Property(
     *                      property="jobCreated",
     *                      type="date",
     *                      example="2018-01-01"
     *                  ),
     *                  @SWG\Property(
     *                      property="jobClosure",
     *                      type="date",
     *                      example="2018-01-01"
     *                  ),
     *                  @SWG\Property(
     *                      property="jobStarted",
     *                      type="date",
     *                      example="2018-01-01"
     *                  ),
     *                  @SWG\Property(
     *                      property="jobFilled",
     *                      type="date",
     *                      example="2018-01-01"
     *                  ),
     *                  @SWG\Property(
     *                      property="approve",
     *                      type="boolean",
     *                      example=true,
     *                      description="true=is approve, false=is decline. null=not considered"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean",
     *                      example="true = open, false=close"
     *                  ),
     *                  @SWG\Property(
     *                      property="awaitingCount",
     *                      type="string",
     *                      example="1",
     *                      description="awaitingCount"
     *                  ),
     *                  @SWG\Property(
     *                      property="shortListCount",
     *                      type="string",
     *                      example="1",
     *                      description="shortListCount"
     *                  ),
     *                  @SWG\Property(
     *                      property="approvedCount",
     *                      type="string",
     *                      example="1",
     *                      description="approvedCount"
     *                  ),
     *              ),
     *          ),
     *          @SWG\Property(
     *              type="object",
     *              property="pagination",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="current_page_number",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="total_count",
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
    public function getAllClientJobAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $settings = $em->getRepository('AppBundle:Settings')->findOneBy([]);
        if(!$settings instanceof Settings){
            $settings = new Settings(false);
            $em->persist($settings);
            $em->flush();
        }
        $jobsResult = $em->getRepository("AppBundle:Job")->getClientJobsWithoutApplicants($user->getId(), $request->query->all());
        $jobs = [];
        if(!empty($jobsResult)){
            foreach ($jobsResult as $job){
                if($job instanceof Job){
                    $awaitingCount = 0;
                    $shortListCount = 0;
                    $approvedCount = 0;
                    $applicants = $em->getRepository("AppBundle:Applicants")->findBy(['client'=>$user,'job'=>$job]);
                    if(!empty($applicants)){
                        foreach ($applicants as $applicant) {
                            if($applicant instanceof Applicants){
                                if($applicant->getCandidate() instanceof User){
                                    $profileDetails = $em->getRepository('AppBundle:ProfileDetails')->findOneBy(['user'=>$applicant->getCandidate()]);
                                    if($profileDetails instanceof ProfileDetails){
                                        if(($settings->getAllowVideo() == true) || (isset($profileDetails->getVideo()['approved']) && $profileDetails->getVideo()['approved'] == true)){
                                            $cvFiles = [];
                                            if(!empty($profileDetails->getCvFiles())){
                                                foreach ($profileDetails->getCvFiles() as $cvFile){
                                                    if(isset($cvFile['approved']) && $cvFile['approved'] == true){
                                                        $cvFiles[] = $cvFile;
                                                    }
                                                }
                                            }
                                            if(!empty($cvFiles)){
                                                if($applicant->getStatus() == 1){
                                                    $awaitingCount++;
                                                }
                                                elseif($applicant->getStatus() == 2){
                                                    $shortListCount++;
                                                }
                                                elseif($applicant->getStatus() == 3){
                                                    $approvedCount++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $jobs[] = [
                        'id' => $job->getId(),
                        'jobTitle' => $job->getJobTitle(),
                        'jobAddress' => $job->getCompanyAddress(),
                        'jobCreated' => $job->getCreated(),
                        'jobClosure' => $job->getClosureDate(),
                        'jobStarted' => $job->getStarted(),
                        'jobFilled' => $job->getFilled(),
                        'approve' => $job->getApprove(),
                        'status' => $job->getStatus(),
                        'awaitingCount' => $awaitingCount,
                        'shortListCount' => $shortListCount,
                        'approvedCount' => $approvedCount
                    ];
                }
            }
        }
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $jobs,
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
     * @return Response
     *
     * @Rest\Get("/criteria")
     * @SWG\Get(path="/api/business/job/criteria",
     *   tags={"Business Job"},
     *   security={true},
     *   summary="Get All Jobs With Criteria",
     *   description="The method for getting all jobs With Criteria for business",
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
     *      name="status",
     *      in="query",
     *      required=false,
     *      type="boolean",
     *      default=false,
     *      description="true for popUp set ut interview"
     *   ),
     *   @SWG\Parameter(
     *      name="candidateID",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=0,
     *      description="for popUp set ut interview"
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
     *                  property="jobTitle",
     *                  type="string",
     *                  example="jobTitle"
     *              ),
     *              @SWG\Property(
     *                  property="articlesFirm",
     *                  type="array",
     *                  @SWG\Items(type="string"),
     *                  example={"BDO","Deloitte"},
     *                  description="required."
     *              ),
     *              @SWG\Property(
     *                  property="gender",
     *                  type="string",
     *                  example="All",
     *                  description="required. All OR Male OR Female"
     *              ),
     *              @SWG\Property(
     *                  property="ethnicity",
     *                  type="string",
     *                  example="None",
     *                  description="required. All OR Black OR White Or Coloured Or Indian Or Oriental"
     *              ),
     *              @SWG\Property(
     *                  property="qualification",
     *                  type="integer",
     *                  example=0,
     *                  description="required. 0 = All, 1 = Fully Qualified CAs, 2 = Part Qualified CAs"
     *              ),
     *              @SWG\Property(
     *                  property="nationality",
     *                  type="integer",
     *                  example=0,
     *                  description="required. 0 = All, 1 = South African, 2=Other"
     *              ),
     *              @SWG\Property(
     *                  property="video",
     *                  type="integer",
     *                  example=0,
     *                  description="required. 0 = All, 1 = With Video"
     *              ),
     *              @SWG\Property(
     *                  property="availability",
     *                  type="integer",
     *                  example=0,
     *                  description="required. 0 = All, 1 = Immediately, 2 = Within 1 month,3 = Within 3 month"
     *              ),
     *              @SWG\Property(
     *                  property="location",
     *                  type="string",
     *                  example="location",
     *                  description="required. Only All or Gauteng or Western Cape or Eastern Cape or KZN"
     *              ),
     *              @SWG\Property(
     *                  property="postArticles",
     *                  type="integer",
     *                  example=0,
     *                  description="NOT required. 0 = All, 1 = Newly qualified, 2 = 1-3 years,3 = > 3 years"
     *              ),
     *              @SWG\Property(
     *                  property="salaryRange",
     *                  type="integer",
     *                  example=0,
     *                  description="NOT required. 0 = None, 1 = 700K, 2 = 700K-1 million,3 = >1 million"
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
    public function getAllClientJobWithCriteriaAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $jobs = $em->getRepository("AppBundle:Job")->getClientJobsWithCriteria($user->getId(), $request->query->getBoolean('status', false));

        if(!empty($jobs)){
            if($request->query->has('candidateID') && $request->query->getInt('candidateID',0)>0){
                $freeJobs = [];
                $candidateID = $request->query->getInt('candidateID',0);
                $candidate = $em->getRepository("AppBundle:User")->find($candidateID);
                if($candidate instanceof User){
                    $hideJobsId = [];
                    $applicants = $em->getRepository("AppBundle:Applicants")->findBy(['client'=>$user,'candidate'=>$candidate,'status'=>3]);
                    if(!empty($applicants)){
                        foreach ($applicants as $applicant){
                            if($applicant instanceof Applicants && $applicant->getJob() instanceof Job){
                                $hideJobsId[] = $applicant->getJob()->getId();
                            }
                        }
                    }
                    if(!empty($hideJobsId)){
                        foreach ($jobs as $job){
                            if(isset($job['id']) && !in_array($job['id'],$hideJobsId)){
                                $freeJobs[] = $job;
                            }
                        }
                    }
                    else{
                        $freeJobs = $jobs;
                    }

                    $view = $this->view($freeJobs, Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view(['error'=>'Candidate Not Found'], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
        }


        $view = $this->view($jobs, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     *
     * @Rest\Post("/")
     * @SWG\Post(path="/api/business/job/",
     *   tags={"Business Job"},
     *   security={true},
     *   summary="Create Job",
     *   description="The method for creating new job for Business",
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
     *              property="jobTitle",
     *              type="string",
     *              example="jobTitle",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="industry",
     *              type="array",
     *              @SWG\Items(type="string"),
     *              description="required."
     *          ),
     *          @SWG\Property(
     *              property="companyName",
     *              type="string",
     *              example="companyName",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="companyAddress",
     *              type="string",
     *              example="companyAddress",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressCountry",
     *              type="string",
     *              example="addressCountry",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressState",
     *              type="string",
     *              example="addressState",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressZipCode",
     *              type="string",
     *              example="addressZipCode",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressCity",
     *              type="string",
     *              example="addressCity",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressSuburb",
     *              type="string",
     *              example="addressSuburb",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressStreet",
     *              type="string",
     *              example="addressStreet",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressStreetNumber",
     *              type="string",
     *              example="addressStreetNumber",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressBuildName",
     *              type="string",
     *              example="addressBuildName",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressUnit",
     *              type="string",
     *              example="addressUnit",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="companyDescription",
     *              type="string",
     *              example="companyDescription",
     *              description="required. Max=300"
     *          ),
     *          @SWG\Property(
     *              property="roleDescription",
     *              type="string",
     *              example="roleDescription",
     *              description="required. Max=400"
     *          ),
     *          @SWG\Property(
     *              property="closureDate",
     *              type="date",
     *              example="2018-05-10",
     *              description="required. 1 month maximum"
     *          ),
     *          @SWG\Property(
     *              property="articlesFirm",
     *              type="array",
     *              @SWG\Items(type="string"),
     *              example={"BDO","Deloitte"},
     *              description="required."
     *          ),
     *          @SWG\Property(
     *              property="gender",
     *              type="string",
     *              example="All",
     *              description="required. All OR Male OR Female"
     *          ),
     *          @SWG\Property(
     *              property="ethnicity",
     *              type="string",
     *              example="None",
     *              description="required. All OR Black OR White Or Coloured Or Indian Or Oriental"
     *          ),
     *          @SWG\Property(
     *              property="nationality",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = South African, 2=Other"
     *          ),
     *          @SWG\Property(
     *              property="qualification",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = Fully Qualified CAs, 2 = Part Qualified CAs"
     *          ),
     *          @SWG\Property(
     *              property="video",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = With Video"
     *          ),
     *          @SWG\Property(
     *              property="availability",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = Immediately, 2 = Within 1 month,3 = Within 3 month"
     *          ),
     *          @SWG\Property(
     *              property="location",
     *              type="string",
     *              example="location",
     *              description="required. Only All or Gauteng or Western Cape or Eastern Cape or KZN "
     *          ),
     *          @SWG\Property(
     *              property="postArticles",
     *              type="integer",
     *              example=0,
     *              description="NOT SEND. NOT required. 0 = All, 1 = Newly qualified, 2 = 1-3 years,3 = > 3 years"
     *          ),
     *          @SWG\Property(
     *              property="salaryRange",
     *              type="integer",
     *              example=0,
     *              description="NOT SEND. NOT required. 0 = None, 1 = 700K, 2 = 700K-1 million,3 = >1 million"
     *          ),
     *          @SWG\Property(
     *              property="started",
     *              type="date",
     *              example="2018-05-10",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="filled",
     *              type="date",
     *              example="2018-05-10",
     *              description="not required"
     *          ),
     *          @SWG\Property(
     *              property="spec",
     *              type="string",
     *              example="file.pdj",
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success. Job Created"
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
    public function createClientJobAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if(!empty($request->request->all())){
            $job = new Job($user, $request->request->all());
            $errors = $this->get('validator')->validate($job, null, array('Jobs'));
            if(count($errors) === 0){
                if($request->files->has('spec')){
                    $fileUpload = $request->files->get('spec');
                    if($fileUpload instanceof UploadedFile){
                        $fileName = md5(uniqid()).'.'.$fileUpload->getClientOriginalExtension();
                        try {
                            $fileUpload->move("uploads/client/".$user->getId()."/job",$fileName);
                            $file = [
                                'url' => $request->getSchemeAndHttpHost()."/uploads/client/".$user->getId()."/job/".$fileName,
                                'adminUrl' => null,
                                'name'=>$fileUpload->getClientOriginalName(),
                                'size'=>$fileUpload->getClientSize(),
                                'time'=>time(),
                                'approved'=>false
                            ];

                            $job->setSpec($file);
                        } catch (\Exception $e) {
                            $view = $this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                            return $this->handleView($view);
                        }
                    }
                }
                $em->persist($job);
                $em->flush();

                $emailData = array(
                    'user' => ['firstName'=>$user->getFirstName(),'lastName'=>$user->getLastName()],
                    'job' => ['jobTitle'=>$job->getJobTitle()],
                    'link' => $request->getSchemeAndHttpHost().'/admin/new_jobs'
                );
                $message = (new \Swift_Message('A New Job post is awaiting your approval'))
                    ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                    ->setBody(
                        $this->renderView('emails/admin/new_job_created.html.twig',
                            $emailData
                        ),
                        'text/html'
                    );

                SendEmail::sendEmailForAdmin($em, $message, $this->get('mailer'), $emailData, SendEmail::TYPE_ADMIN_JOB_NEW);
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
            $view = $this->view(['error'=>'All fields required'], Response::HTTP_BAD_REQUEST);
        }

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Get("/{id}",requirements={"id"="\d+"})
     * @SWG\Get(path="/api/business/job/{id}",
     *   tags={"Business Job"},
     *   security={true},
     *   summary="Get Job By Id",
     *   description="The method for getting job by id for business",
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
     *      default="jobId",
     *      description="Job ID"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="jobTitle",
     *              type="string",
     *              example="jobTitle",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="industry",
     *              type="array",
     *              @SWG\Items(type="string"),
     *              description="required."
     *          ),
     *          @SWG\Property(
     *              property="companyName",
     *              type="string",
     *              example="companyName",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="companyAddress",
     *              type="string",
     *              example="companyAddress",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressCountry",
     *              type="string",
     *              example="addressCountry",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressState",
     *              type="string",
     *              example="addressState",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressZipCode",
     *              type="string",
     *              example="addressZipCode",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressCity",
     *              type="string",
     *              example="addressCity",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressStreet",
     *              type="string",
     *              example="addressStreet",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressSuburb",
     *              type="string",
     *              example="addressSuburb",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressStreetNumber",
     *              type="string",
     *              example="addressStreetNumber",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressBuildName",
     *              type="string",
     *              example="addressBuildName",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressUnit",
     *              type="string",
     *              example="addressUnit",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="companyDescription",
     *              type="string",
     *              example="companyDescription",
     *              description="required. Min=50,Max=200"
     *          ),
     *          @SWG\Property(
     *              property="roleDescription",
     *              type="string",
     *              example="roleDescription",
     *              description="required. Max=400"
     *          ),
     *          @SWG\Property(
     *              property="closureDate",
     *              type="date",
     *              example="2018-05-10",
     *              description="required. 1 month maximum"
     *          ),
     *          @SWG\Property(
     *              property="articlesFirm",
     *              type="array",
     *              @SWG\Items(type="string"),
     *              example={"BDO","Deloitte"},
     *              description="required."
     *          ),
     *          @SWG\Property(
     *              property="gender",
     *              type="string",
     *              example="All",
     *              description="required. All OR Male OR Female"
     *          ),
     *          @SWG\Property(
     *              property="ethnicity",
     *              type="string",
     *              example="None",
     *              description="required. All OR Black OR White Or Coloured Or Indian Or Oriental"
     *          ),
     *          @SWG\Property(
     *              property="qualification",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = Fully Qualified CAs, 2 = Part Qualified CAs"
     *          ),
     *          @SWG\Property(
     *              property="nationality",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = South African, 2=Other"
     *          ),
     *          @SWG\Property(
     *              property="video",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = With Video"
     *          ),
     *          @SWG\Property(
     *              property="availability",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = Immediately, 2 = Within 1 month,3 = Within 3 month"
     *          ),
     *          @SWG\Property(
     *              property="location",
     *              type="string",
     *              example="location",
     *              description="required. Only All or Gauteng or Western Cape or Eastern Cape or KZN "
     *          ),
     *          @SWG\Property(
     *              property="postArticles",
     *              type="integer",
     *              example=0,
     *              description="NOT required. 0 = All, 1 = Newly qualified, 2 = 1-3 years,3 = > 3 years"
     *          ),
     *          @SWG\Property(
     *              property="salaryRange",
     *              type="integer",
     *              example=0,
     *              description="NOT required. 0 = None, 1 = 700K, 2 = 700K-1 million,3 = >1 million"
     *          ),
     *          @SWG\Property(
     *              property="approve",
     *              type="boolean",
     *              example=true,
     *              description="true=is approve, false=is decline. null=not considered"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true,
     *              description="required.true=open,false=close"
     *          ),
     *          @SWG\Property(
     *              property="createdDate",
     *              type="date",
     *              example="2018-04-10",
     *              description="required."
     *          ),
     *          @SWG\Property(
     *              property="started",
     *              type="date",
     *              example="2018-04-10"
     *          ),
     *          @SWG\Property(
     *              property="filled",
     *              type="date",
     *              example="2018-04-10"
     *          ),
     *          @SWG\Property(
     *              property="spec",
     *              type="object",
     *              @SWG\Property(
     *                  property="url",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="adminUrl",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="size",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="approved",
     *                  type="boolean"
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="awaitingCount",
     *              type="integer",
     *              description="awaitingCount"
     *          ),
     *          @SWG\Property(
     *              property="shortListCount",
     *              type="integer",
     *              description="shortListCount"
     *          ),
     *          @SWG\Property(
     *              property="approvedCount",
     *              type="integer",
     *              description="approvedCount"
     *          ),
     *          @SWG\Property(
     *              property="candidateCount",
     *              type="integer",
     *              description="candidateCount"
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
    public function getClientJobByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $settings = $em->getRepository('AppBundle:Settings')->findOneBy([]);
        if(!$settings instanceof Settings){
            $settings = new Settings(false);
            $em->persist($settings);
            $em->flush();
        }

        $job = $em->getRepository("AppBundle:Job")->findOneBy(['user'=>$user,'id'=>$id]);

        if($job instanceof Job){
            $awaitingCount = 0;
            $shortListCount = 0;
            $approvedCount = 0;
            $applicants = $em->getRepository("AppBundle:Applicants")->findBy(['client'=>$user,'job'=>$job]);
            if(!empty($applicants)){
                foreach ($applicants as $applicant) {
                    if($applicant instanceof Applicants){
                        if($applicant->getCandidate() instanceof User){
                            $profileDetails = $em->getRepository('AppBundle:ProfileDetails')->findOneBy(['user'=>$applicant->getCandidate()]);
                            if($profileDetails instanceof ProfileDetails){
                                if(($settings->getAllowVideo() == true) || (isset($profileDetails->getVideo()['approved']) && $profileDetails->getVideo()['approved'] == true)){
                                    $cvFiles = [];
                                    if(!empty($profileDetails->getCvFiles())){
                                        foreach ($profileDetails->getCvFiles() as $cvFile){
                                            if(isset($cvFile['approved']) && $cvFile['approved'] == true){
                                                $cvFiles[] = $cvFile;
                                            }
                                        }
                                    }
                                    if(!empty($cvFiles)){
                                        if($applicant->getStatus() == 1){
                                            $awaitingCount++;
                                        }
                                        elseif($applicant->getStatus() == 2){
                                            $shortListCount++;
                                        }
                                        elseif($applicant->getStatus() == 3){
                                            $approvedCount++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $countCandidate = 0;
            $candidates = $em->getRepository("AppBundle:ProfileDetails")->getCandidateWithCriteriaWithVisible([
                'articlesFirm' => $job->getArticlesFirm(),
                'gender' => $job->getGender(),
                'ethnicity' => $job->getEthnicity(),
                'nationality' => $job->getNationality(),
                'location' => $job->getLocation(),
                'qualification' => $job->getQualification(),
                'video' => $job->getVideo(),
                'availability' => $job->getAvailability()
            ], false);
            if(!empty($candidates)){
                $settings = $em->getRepository('AppBundle:Settings')->findOneBy([]);
                if(!$settings instanceof Settings){
                    $settings = new Settings(false);
                    $em->persist($settings);
                    $em->flush();
                }
                foreach ($candidates as $candidate){
                    if($candidate instanceof ProfileDetails){
                        if($settings->getAllowVideo() == true || (isset($candidate->getVideo()['approved']) && $candidate->getVideo()['approved'] == true)){
                            $cvFiles = [];
                            if(!empty($candidate->getCvFiles())){

                                foreach ($candidate->getCvFiles() as $cvFile){
                                    if(isset($cvFile['approved']) && $cvFile['approved'] == true){
                                        $cvFiles[] = $cvFile;
                                    }
                                }
                            }
                            if(!empty($cvFiles)){
                                $countCandidate = $countCandidate +1;
                            }
                        }
                    }
                }
            }

            $view = $this->view([
                'id' => $job->getId(),
                'jobTitle' => $job->getJobTitle(),
                'industry' => $job->getIndustry(),
                'companyName' => $job->getCompanyName(),
                'companyAddress' => $job->getCompanyAddress(),
                'addressCountry' => $job->getAddressCountry(),
                'addressState' => $job->getAddressState(),
                'addressZipCode' => $job->getAddressZipCode(),
                'addressCity' => $job->getAddressCity(),
                'addressSuburb' => $job->getAddressSuburb(),
                'addressStreet' => $job->getAddressStreet(),
                'addressStreetNumber' => $job->getAddressStreetNumber(),
                'addressBuildName' => $job->getAddressBuildName(),
                'addressUnit' => $job->getAddressUnit(),
                'companyDescription' => $job->getCompanyDescription(),
                'roleDescription' => $job->getRoleDescription(),
                'closureDate' => $job->getClosureDate(),
                'articlesFirm' => $job->getArticlesFirm(),
                'gender' => $job->getGender(),
                'ethnicity' => $job->getEthnicity(),
                'qualification' => $job->getQualification(),
                'nationality' => $job->getNationality(),
                'video' => $job->getVideo(),
                'availability' => $job->getAvailability(),
                'location' => $job->getLocation(),
                'postArticles' => $job->getPostArticles(),
                'salaryRange' => $job->getSalaryRange(),
                'approve' => $job->getApprove(),
                'status' => $job->getStatus(),
                'createdDate' => $job->getCreated(),
                'started' => $job->getStarted(),
                'filled' => $job->getFilled(),
                'spec' => $job->getSpec(),
                'awaitingCount' => ($awaitingCount > 0) ? $awaitingCount : 0,
                'shortListCount' => ($shortListCount > 0) ? $shortListCount : 0,
                'approvedCount' => ($approvedCount > 0) ? $approvedCount : 0 ,
                'candidateCount' => ($countCandidate > 0) ? $countCandidate : 0
            ], Response::HTTP_OK);
        }
        else{
            $view = $this->view(['error'=>'Job Not Found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     *
     * @Rest\Put("/{id}",requirements={"id"="\d+"})
     * @SWG\Put(path="/api/business/job/{id}",
     *   tags={"Business Job"},
     *   security={true},
     *   summary="Edit Job",
     *   description="The method for editing job for Business",
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
     *      default="jobID",
     *      description="jobID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="jobTitle",
     *              type="string",
     *              example="jobTitle",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="industry",
     *              type="array",
     *              @SWG\Items(type="string"),
     *              description="required. 0=All,1=Financial Services,2=Non-Financial Services"
     *          ),
     *          @SWG\Property(
     *              property="companyName",
     *              type="string",
     *              example="companyName",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="companyAddress",
     *              type="string",
     *              example="companyAddress",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressCountry",
     *              type="string",
     *              example="addressCountry",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressState",
     *              type="string",
     *              example="addressState",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressZipCode",
     *              type="string",
     *              example="addressZipCode",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressCity",
     *              type="string",
     *              example="addressCity",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressSuburb",
     *              type="string",
     *              example="addressSuburb",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressStreet",
     *              type="string",
     *              example="addressStreet",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressStreetNumber",
     *              type="string",
     *              example="addressStreetNumber",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressBuildName",
     *              type="string",
     *              example="addressBuildName",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="addressUnit",
     *              type="string",
     *              example="addressUnit",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="companyDescription",
     *              type="string",
     *              example="companyDescription",
     *              description="required. Max=300"
     *          ),
     *          @SWG\Property(
     *              property="roleDescription",
     *              type="string",
     *              example="roleDescription",
     *              description="required. Max=400"
     *          ),
     *          @SWG\Property(
     *              property="closureDate",
     *              type="date",
     *              example="2018-05-10",
     *              description="required. 1 month maximum"
     *          ),
     *          @SWG\Property(
     *              property="articlesFirm",
     *              type="array",
     *              @SWG\Items(type="string"),
     *              example={"BDO","Deloitte"},
     *              description="required."
     *          ),
     *          @SWG\Property(
     *              property="gender",
     *              type="string",
     *              example="All",
     *              description="required. All OR Male OR Female"
     *          ),
     *          @SWG\Property(
     *              property="ethnicity",
     *              type="string",
     *              example="None",
     *              description="required. All OR Black OR White Or Coloured Or Indian Or Oriental"
     *          ),
     *          @SWG\Property(
     *              property="qualification",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = Fully Qualified CAs, 2 = Part Qualified CAs"
     *          ),
     *          @SWG\Property(
     *              property="nationality",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = South African, 2=Other"
     *          ),
     *          @SWG\Property(
     *              property="video",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = With Video"
     *          ),
     *          @SWG\Property(
     *              property="availability",
     *              type="integer",
     *              example=0,
     *              description="required. 0 = All, 1 = Immediately, 2 = Within 1 month,3 = Within 3 month"
     *          ),
     *          @SWG\Property(
     *              property="location",
     *              type="string",
     *              example="location",
     *              description="required. Only All or Gauteng or Western Cape or Eastern Cape or KZN "
     *          ),
     *          @SWG\Property(
     *              property="postArticles",
     *              type="integer",
     *              example=0,
     *              description="NOT SEND. NOT required. 0 = All, 1 = Newly qualified, 2 = 1-3 years,3 = > 3 years"
     *          ),
     *          @SWG\Property(
     *              property="salaryRange",
     *              type="integer",
     *              example=0,
     *              description="NOT SEND. NOT required. 0 = None, 1 = 700K, 2 = 700K-1 million,3 = >1 million"
     *          ),
     *          @SWG\Property(
     *              property="started",
     *              type="date",
     *              example="2018-05-10",
     *              description="required."
     *          ),
     *          @SWG\Property(
     *              property="filled",
     *              type="date",
     *              example="2018-05-10",
     *              description="not required."
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success. Job Edit"
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
    public function editClientJobByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $job = $em->getRepository("AppBundle:Job")->findOneBy(['user'=>$user,'id'=>$id]);
        if($job instanceof Job){
            if($job->getStatus() == true){
                $job->update($request->request->all());
                $errors = $this->get('validator')->validate($job, null, array('Jobs'));
                if(count($errors) === 0){
                    $em->persist($job);
                    $em->flush();

                    $emailData = array(
                        'user' => ['firstName'=>$user->getFirstName(),'lastName'=>$user->getLastName()],
                        'job' => ['jobTitle'=>$job->getJobTitle()],
                        'link' => $request->getSchemeAndHttpHost().'/admin/all_jobs'
                    );
                    $message = (new \Swift_Message('A client has edited their job post'))
                        ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                        ->setBody(
                            $this->renderView('emails/admin/job_edit.html.twig',
                                $emailData
                            ),
                            'text/html'
                        );
                    SendEmail::sendEmailForAdmin($em, $message, $this->get('mailer'), $emailData, SendEmail::TYPE_ADMIN_JOB_CHANGE);
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
                $view = $this->view(['error'=>'Job is Close'], Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            $view = $this->view(['error'=>'Job Not Found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Patch("/{id}",requirements={"id"="\d+"})
     * @SWG\Patch(path="/api/business/job/{id}",
     *   tags={"Business Job"},
     *   security={true},
     *   summary="Change Job Status",
     *   description="The method for changing job status for Business",
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
     *      default="jobID",
     *      description="jobID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=false,
     *              description="required"
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success. Job Status Changed"
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
    public function updateClientJobByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $job = $em->getRepository("AppBundle:Job")->findOneBy(['user'=>$user,'id'=>$id]);
        if($job instanceof Job){
            if($request->request->has('status') && is_bool($request->request->get('status'))){
                $job->setStatus($request->request->get('status'));
                $job->setClosureDate(new \DateTime());
                $em->persist($job);
                $em->flush();

                if($request->request->get('status') == false){
                    $applicants = $em->getRepository("AppBundle:Applicants")->findBy(['job'=>$job, 'status'=>1]);
                    foreach ($applicants as $applicant){
                        if($applicant instanceof Applicants){
                            $em->remove($applicant);
                            $em->flush();
                        }
                    }

                    $hideJobs = $em->getRepository("AppBundle:HideJob")->findBy(['job'=>$job]);
                    foreach ($hideJobs as $hideJob){
                        if($hideJob instanceof HideJob){
                            $em->remove($hideJob);
                            $em->flush();
                        }
                    }
                }

                $view = $this->view([], Response::HTTP_NO_CONTENT);
            }
            else{
                $view = $this->view(['error'=>'field status is required and should be boolean type'], Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            $view = $this->view(['error'=>'Job Not Found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Post("/{id}/spec",requirements={"id"="\d+"})
     * @SWG\Post(path="/api/business/job/{id}/spec",
     *   tags={"Business Job"},
     *   security={true},
     *   summary="UPLOAD Job SPEC",
     *   description="The method for upload job spec for Business",
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
     *      default="jobID",
     *      description="jobID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="spec",
     *              type="string",
     *              example="file.pdf"
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="spec",
     *              type="object",
     *              @SWG\Property(
     *                  property="url",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="adminUrl",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="size",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="approved",
     *                  type="boolean"
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="approve",
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
    public function uploadSpecJobByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $job = $em->getRepository("AppBundle:Job")->findOneBy(['user'=>$user,'id'=>$id]);
        if($job instanceof Job){
            if($request->files->has('spec')){
                $fileUpload = $request->files->get('spec');
                if($fileUpload instanceof UploadedFile){
                    $fileName = md5(uniqid()).'.'.$fileUpload->getClientOriginalExtension();
                    try {
                        $fileUpload->move("uploads/client/".$user->getId()."/job",$fileName);
                        $file = [
                            'url' => $request->getSchemeAndHttpHost()."/uploads/client/".$user->getId()."/job/".$fileName,
                            'adminUrl' => null,
                            'name'=>$fileUpload->getClientOriginalName(),
                            'size'=>$fileUpload->getClientSize(),
                            'time'=>time(),
                            'approved'=>false
                        ];

                        $job->setSpec($file);
                        $job->setApprove(NULL);

                        $em->persist($job);
                        $em->flush();
                        $view = $this->view([
                            'spec' => $job->getSpec(),
                            'approve' => $job->getApprove()
                        ], Response::HTTP_OK);

                    } catch (\Exception $e) {
                        $view = $this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view(['error'=>'Job Spec is not file'], Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $view = $this->view(['error'=>'Job Spec is required'], Response::HTTP_BAD_REQUEST);
            }

        }
        else{
            $view = $this->view(['error'=>'Job Not Found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Delete("/{id}/spec",requirements={"id"="\d+"})
     * @SWG\Delete(path="/api/business/job/{id}/spec",
     *   tags={"Business Job"},
     *   security={true},
     *   summary="REMOVE Job SPEC",
     *   description="The method for remove job spec for Business",
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
     *      default="jobID",
     *      description="jobID"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="spec",
     *              type="object",
     *              @SWG\Property(
     *                  property="url",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="adminUrl",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="size",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="approved",
     *                  type="boolean"
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="approve",
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
    public function removeSpecJobByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $job = $em->getRepository("AppBundle:Job")->findOneBy(['user'=>$user,'id'=>$id]);
        if($job instanceof Job){
            $job->setSpec(null);

            $em->persist($job);
            $em->flush();
            $view = $this->view([
                'spec' => $job->getSpec(),
                'approve' => $job->getApprove()
            ], Response::HTTP_OK);
        }
        else{
            $view = $this->view(['error'=>'Job Not Found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Delete("/{id}",requirements={"id"="\d+"})
     * @SWG\Delete(path="/api/business/job/{id}",
     *   tags={"Business Job"},
     *   security={true},
     *   summary="Delete Job",
     *   description="The method for deleting job status for Business",
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
     *      default="jobID",
     *      description="jobID"
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success. Job Status Changed"
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
    public function deleteClientJobByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $job = $em->getRepository("AppBundle:Job")->findOneBy(['user'=>$user,'id'=>$id]);
        if($job instanceof Job){
            $em->remove($job);
            $em->flush();

            $view = $this->view([], Response::HTTP_NO_CONTENT);
        }
        else{
            $view = $this->view(['error'=>'Job Not Found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Post("/send")
     * @SWG\Post(path="/api/business/job/send",
     *   tags={"Business Job"},
     *   security={true},
     *   summary="Qualification Drop Down SEND EMAIL",
     *   description="The method for Qualification Drop Down SEND EMAIL",
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
     *      response=204,
     *      description="Success.",
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
    public function sendNotifyAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $company = $em->getRepository("AppBundle:CompanyDetails")->findOneBy(['user'=>$user]);
        if($company instanceof CompanyDetails){
            $message = (new \Swift_Message('Qualification request'))
                ->setFrom($this->container->getParameter('mailer_user_name'), 'CAs_Online')
                ->setBody(
                    $this->renderView('emails/admin/qualification.html.twig',[
                            'user' => $user,
                            'company' => $company
                        ]
                    ),
                    'text/html'
                );

            $admins = $em->getRepository("AppBundle:User")->findByRolesForSystem(['ROLE_SUPER_ADMIN']);
            if(!empty($admins)){
                foreach ($admins as $admin){
                    if(isset($admin['email'])){
                        $message->setTo($admin['email']);
                        try{
                            $this->get('mailer')->send($message);
                        }catch(\Swift_TransportException $e){

                        }
                    }

                }
            }
            $view = $this->view([], Response::HTTP_NO_CONTENT);
        }
        else{
            $view = $this->view(['error'=>'Not Found'], Response::HTTP_NOT_FOUND);
        }
        return $this->handleView($view);
    }

}