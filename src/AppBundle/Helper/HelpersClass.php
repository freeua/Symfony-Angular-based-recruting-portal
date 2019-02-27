<?php
/**
 * Created by PhpStorm.
 * Date: 16.04.18
 * Time: 10:40
 */

namespace AppBundle\Helper;


use AppBundle\Entity\NotificationAdmin;
use AppBundle\Entity\ProfileDetails;
use AppBundle\Entity\Settings;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class HelpersClass
{
    /**
     * @param ProfileDetails $profileDetails
     * @param EntityManager $em
     * @return ProfileDetails
     */
    public static function candidateProfileCompletePercentage(ProfileDetails $profileDetails, EntityManager $em){
        $RFP_MAX = 20;
        $NRFP_MAX = 20;
        $ARP_MAX= 10;

        $RFUser = [
            'firstName', 'lastName', 'email', 'phone'
        ];
        $RFProfile = [
            'articlesFirm', 'saicaStatus', 'mostRole','mostEmployer', 'nationality', 'ethnicity', 'gender', 'dateArticlesCompleted',
            'boards', 'homeAddress', 'availability', 'citiesWorking'
        ];
        $NRFProfile = [
            'idNumber', 'dateOfBirth', 'criminal', 'credit', 'otherQualifications', 'addressCountry', 'addressState', 'addressZipCode', 'addressCity', 'addressSuburb',
            'addressStreet', 'addressStreetNumber', 'addressUnit', 'picture', 'matricCertificate', 'tertiaryCertificate', 'universityManuscript', 'creditCheck', 'linkedinCheck'
        ];

        $RFP_ONE = ($RFP_MAX/(count($RFUser) + count($RFProfile)));
        $NRFP_ONE = ($NRFP_MAX/count($NRFProfile));
        $ARP_ONE = ($ARP_MAX/2);
        $VP_ONE = $CVP_ONE = 25;

        $RFP = $NRFP = $ARP = $percentage = 0;

        //REQUIRED FIELDS USER
        foreach ($RFUser as $field){
            $methodGet = 'get'.ucfirst($field);
            if(method_exists(User::class,$methodGet)){
                if(!empty($profileDetails->getUser()->$methodGet())){
                    $RFP = $RFP + $RFP_ONE;
                }
            }
        }
        //REQUIRED FIELDS PROFILE
        foreach ($RFProfile as $field){
            $methodGet = 'get'.ucfirst($field);
            if(method_exists(ProfileDetails::class,$methodGet)){
                if(is_bool($profileDetails->$methodGet()) || !empty($profileDetails->$methodGet())){
                    $RFP = $RFP + $RFP_ONE;
                }
            }
        }
        //CHECK MAX REQUIRED FIELDS
        if($RFP > $RFP_MAX){
            $RFP = $RFP_MAX;
        }
        $percentage = $percentage + $RFP;

        //NON REQUIRED FIELDS PROFILE
        foreach ($NRFProfile as $field){
            $methodGet = 'get'.ucfirst($field);
            if(method_exists(ProfileDetails::class,$methodGet)){
                if(is_bool($profileDetails->$methodGet()) || !empty($profileDetails->$methodGet())){
                    $NRFP = $NRFP + $NRFP_ONE;
                }
            }
        }

        //CHECK MAX NON REQUIRED FIELDS
        if($NRFP > $NRFP_MAX){
            $NRFP = $NRFP_MAX;
        }
        $percentage = $percentage + $NRFP;

        //Achievements
        $achievements = $em->getRepository("AppBundle:CandidateAchievements")->findBy(['user'=>$profileDetails->getUser()]);
        if(!empty($achievements)){
            $ARP = $ARP + $ARP_ONE;
        }
        //References
        $references = $em->getRepository("AppBundle:CandidateReferences")->findBy(['user'=>$profileDetails->getUser()]);
        if(!empty($references)){
            $ARP = $ARP + $ARP_ONE;
        }
        //CHECK MAX Achievements & References
        if($ARP > $ARP_MAX){
            $ARP = $ARP_MAX;
        }
        $percentage = $percentage + $ARP;

        //VIDEO
        if(!empty($profileDetails->getVideo())){
            $percentage = $percentage + $VP_ONE;
        }
        //CV
        if(!empty($profileDetails->getCvFiles())){
            $percentage = $percentage + $CVP_ONE;
        }

        $percentage = round($percentage);
        if($percentage >= 100){
            $percentage = 100;
        }
        $profileDetails->setPercentage($percentage);

        return $profileDetails;
    }

    /**
     * @param ProfileDetails $profileDetails
     * @param EntityManager $em
     * @return ProfileDetails
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function checkAutoVisible(ProfileDetails $profileDetails, EntityManager $em){
        $settings = $em->getRepository('AppBundle:Settings')->findOneBy([]);
        if(!$settings instanceof Settings){
            $settings = new Settings(false);
            $em->persist($settings);
            $em->flush();
        }
        if($profileDetails->getPercentage() > 50
            && !empty($profileDetails->getCvFiles() && isset($profileDetails->getCvFiles()[0]))
            && isset($profileDetails->getCvFiles()[0]['approved']) && $profileDetails->getCvFiles()[0]['approved'] == true
            && (
                ($settings->getAllowVideo() == true)
                || (!empty($profileDetails->getVideo()) && isset($profileDetails->getVideo()['approved']) && $profileDetails->getVideo()['approved'] == true)
            )
        ){
            $profileDetails->setVisible(true);
            $profileDetails->setLooking(true);
        }
        else{
            $profileDetails->setVisible(false);
            $profileDetails->setLooking(false);
        }
        return $profileDetails;
    }

}