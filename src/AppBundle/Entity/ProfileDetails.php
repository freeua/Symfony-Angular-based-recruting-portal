<?php
/**
 * Created by PhpStorm.
 * Date: 28.02.18
 * Time: 16:18
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProfileDetailsRepository")
 * @ORM\Table(name="profile_details")
 */
class ProfileDetails
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @Assert\NotBlank(
     *     message="articlesFirm should be not empty",
     *     groups={"updateDetails"}
     * )
     * @ORM\Column(type="string")
     */
    private $articlesFirm;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $articlesFirmName;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(
     *     message="SAICA Status should be not empty",
     *     groups={"updateDetails"}
     * )
     * @Assert\GreaterThan(
     *     message="saicaStatus Invalid value",
     *     value="0",
     *     groups={"updateDetails"}
     * )
     * @Assert\LessThan(
     *     message="saicaStatus Invalid value",
     *     value="4",
     *     groups={"updateDetails"}
     * )
     * 1 = Registered CA (Show saicaNumber and saicaNumber is required)
     * 2 = Eligible to register as a CA
     * 3 = Completing articles whereafter I will register
     * 4 = None of the above (not save show popup)
     */
    private $saicaStatus;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $saicaNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $mostRole;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $mostEmployer;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nationality;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $idNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ethnicity;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $gender;

    /**
     * @Assert\Date(message="dateOfBirth should be date format",groups={"updateDetails"})
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @Assert\Date(message="dateArticlesCompleted should be date format",groups={"updateDetails"})
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateArticlesCompleted;

    /**
     * @Assert\GreaterThanOrEqual(
     *     message="costToCompany Invalid value",
     *     value="0",
     *     groups={"updateDetails"}
     * )
     * @Assert\LessThanOrEqual(
     *     message="costToCompany Invalid value",
     *     value="3",
     *     groups={"updateDetails"}
     * )
     * 0 = Newly Qualified
     * 1 = 700K
     * 2 = 700K-1 million
     * 3 = >1 million
     * @ORM\Column(type="integer", nullable=true)
     */
    private $costToCompany;

    /**
     * @Assert\Choice({false,true},strict=true,message="criminal should be boolean type",groups={"updateDetails"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $criminal;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $criminalDescription;

    /**
     * @Assert\Choice({false,true},strict=true,message="credit should be boolean type",groups={"updateDetails"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $credit;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $creditDescription;

    /**
     * Boards
     * 1 = Passed Both Board Exams First Time,
     * 2 = Passed Both Board Exams,
     * 3 = ITC passed, APC Outstanding,
     * 4 = ITC Outstanding
     * 
     * @Assert\Choice({1,2,3,4},message="boards invalid value",groups={"updateDetails"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $boards;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherQualifications;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $homeAddress;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressCountry;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressState;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressZipCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressCity;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressSuburb;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressStreet;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressStreetNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressUnit;

    /**
     * @Assert\Choice({false,true},strict=true,message="availability should be boolean type",groups={"updateDetails"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $availability;

    /**
     * 1=30 Day notice period
     * 2=60 Day notice period
     * 3=90 Day notice period
     * 4=I can provide a specific date
     *
     * @Assert\Choice({1,2,3,4},message="Select available period",groups={"updateDetails"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $availabilityPeriod;

    /**
     * @Assert\Date(message="dateAvailability should be date format",groups={"updateDetails"})
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateAvailability;

    /**
     * @Assert\Type(type="array",message="citiesWorking should be array type",groups={"updateDetails"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $citiesWorking;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $picture;

    /**
     * @Assert\Type(type="array",message="matricCertificate should be array type",groups={"updateDetails"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $matricCertificate;

    /**
     * @Assert\Type(type="array",message="tertiaryCertificate should be array type",groups={"updateDetails"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $tertiaryCertificate;

    /**
     * @Assert\Type(type="array",message="universityManuscript should be array type",groups={"updateDetails"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $universityManuscript;

    /**
     * @Assert\Type(type="array",message="creditCheck should be array type",groups={"updateDetails"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $creditCheck;

    /**
     * @Assert\Type(type="array",message="cvFiles should be array type",groups={"updateDetails"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $cvFiles;

    /**
     * @Assert\Choice({false,true},strict=true,message="linkedinCheck should be boolean type",groups={"updateDetails"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $linkedinCheck;

    /**
     * @Assert\Regex(
     *     pattern="/^(http(s)?:\/\/)?([\w]+\.)?linkedin\.com\/(pub|in|profile)\/\S+$/",
     *     message="linkedinUrl invalid",
     *     groups={"updateDetails"})
     * @ORM\Column(type="string", nullable=true)
     */
    private $linkedinUrl;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $video;

    /**
     * @ORM\Column(type="integer")
     */
    private $percentage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $looking;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visible;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default":0})
     */
    private $view;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default":0})
     */
    private $play;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastDeactivated;

    /**
     * ProfileDetails constructor.
     * @param $user
     * @param $articlesFirm
     * @param $saicaStatus
     */
    public function __construct($user, $articlesFirm, $saicaStatus)
    {
        $this->user = $user;
        $this->articlesFirm = $articlesFirm;
        $this->saicaStatus = $saicaStatus;
        $this->percentage = 10;
        $this->looking = false;
        $this->visible = false;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getArticlesFirm()
    {
        return $this->articlesFirm;
    }

    /**
     * @param mixed $articlesFirm
     */
    public function setArticlesFirm($articlesFirm)
    {
        $this->articlesFirm = $articlesFirm;
    }

    /**
     * @return mixed
     */
    public function getArticlesFirmName()
    {
        return $this->articlesFirmName;
    }

    /**
     * @param mixed $articlesFirmName
     */
    public function setArticlesFirmName($articlesFirmName)
    {
        $this->articlesFirmName = $articlesFirmName;
    }

    /**
     * @return mixed
     */
    public function getSaicaStatus()
    {
        return $this->saicaStatus;
    }

    /**
     * @param mixed $saicaStatus
     */
    public function setSaicaStatus($saicaStatus)
    {
        $this->saicaStatus = $saicaStatus;
    }

    /**
     * @return mixed
     */
    public function getSaicaNumber()
    {
        return $this->saicaNumber;
    }

    /**
     * @param mixed $saicaNumber
     */
    public function setSaicaNumber($saicaNumber)
    {
        $this->saicaNumber = $saicaNumber;
    }

    /**
     * @return mixed
     */
    public function getMostRole()
    {
        return $this->mostRole;
    }

    /**
     * @param mixed $mostRole
     */
    public function setMostRole($mostRole)
    {
        $this->mostRole = $mostRole;
    }

    /**
     * @return mixed
     */
    public function getMostEmployer()
    {
        return $this->mostEmployer;
    }

    /**
     * @param mixed $mostEmployer
     */
    public function setMostEmployer($mostEmployer)
    {
        $this->mostEmployer = $mostEmployer;
    }

    /**
     * @return mixed
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param mixed $nationality
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
    }

    /**
     * @return mixed
     */
    public function getIdNumber()
    {
        return $this->idNumber;
    }

    /**
     * @param mixed $idNumber
     */
    public function setIdNumber($idNumber)
    {
        $this->idNumber = $idNumber;
    }

    /**
     * @return mixed
     */
    public function getEthnicity()
    {
        return $this->ethnicity;
    }

    /**
     * @param mixed $ethnicity
     */
    public function setEthnicity($ethnicity)
    {
        $this->ethnicity = $ethnicity;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param mixed $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @return mixed
     */
    public function getDateArticlesCompleted()
    {
        return $this->dateArticlesCompleted;
    }

    /**
     * @param mixed $dateArticlesCompleted
     */
    public function setDateArticlesCompleted($dateArticlesCompleted)
    {
        $this->dateArticlesCompleted = $dateArticlesCompleted;
    }

    /**
     * @return mixed
     */
    public function getCostToCompany()
    {
        return $this->costToCompany;
    }

    /**
     * @param mixed $costToCompany
     */
    public function setCostToCompany($costToCompany)
    {
        $this->costToCompany = $costToCompany;
    }

    /**
     * @return mixed
     */
    public function getCriminal()
    {
        return $this->criminal;
    }

    /**
     * @param mixed $criminal
     */
    public function setCriminal($criminal)
    {
        $this->criminal = $criminal;
    }

    /**
     * @return mixed
     */
    public function getCriminalDescription()
    {
        return $this->criminalDescription;
    }

    /**
     * @param mixed $criminalDescription
     */
    public function setCriminalDescription($criminalDescription)
    {
        $this->criminalDescription = $criminalDescription;
    }


    /**
     * @return mixed
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @param mixed $credit
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;
    }

    /**
     * @return mixed
     */
    public function getCreditDescription()
    {
        return $this->creditDescription;
    }

    /**
     * @param mixed $creditDescription
     */
    public function setCreditDescription($creditDescription)
    {
        $this->creditDescription = $creditDescription;
    }


    /**
     * @return mixed
     */
    public function getBoards()
    {
        return $this->boards;
    }

    /**
     * @param mixed $boards
     */
    public function setBoards($boards)
    {
        $this->boards = $boards;
    }


    /**
     * @return mixed
     */
    public function getOtherQualifications()
    {
        return $this->otherQualifications;
    }

    /**
     * @param mixed $otherQualifications
     */
    public function setOtherQualifications($otherQualifications)
    {
        $this->otherQualifications = $otherQualifications;
    }

    /**
     * @return mixed
     */
    public function getHomeAddress()
    {
        return $this->homeAddress;
    }

    /**
     * @param mixed $homeAddress
     */
    public function setHomeAddress($homeAddress)
    {
        $this->homeAddress = $homeAddress;
    }

    /**
     * @return mixed
     */
    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    /**
     * @param mixed $addressCountry
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }

    /**
     * @return mixed
     */
    public function getAddressState()
    {
        return $this->addressState;
    }

    /**
     * @param mixed $addressState
     */
    public function setAddressState($addressState)
    {
        $this->addressState = $addressState;
    }

    /**
     * @return mixed
     */
    public function getAddressZipCode()
    {
        return $this->addressZipCode;
    }

    /**
     * @param mixed $addressZipCode
     */
    public function setAddressZipCode($addressZipCode)
    {
        $this->addressZipCode = $addressZipCode;
    }

    /**
     * @return mixed
     */
    public function getAddressCity()
    {
        return $this->addressCity;
    }

    /**
     * @param mixed $addressCity
     */
    public function setAddressCity($addressCity)
    {
        $this->addressCity = $addressCity;
    }

    /**
     * @return mixed
     */
    public function getAddressSuburb()
    {
        return $this->addressSuburb;
    }

    /**
     * @param mixed $addressSuburb
     */
    public function setAddressSuburb($addressSuburb)
    {
        $this->addressSuburb = $addressSuburb;
    }

    /**
     * @return mixed
     */
    public function getAddressStreet()
    {
        return $this->addressStreet;
    }

    /**
     * @param mixed $addressStreet
     */
    public function setAddressStreet($addressStreet)
    {
        $this->addressStreet = $addressStreet;
    }

    /**
     * @return mixed
     */
    public function getAddressStreetNumber()
    {
        return $this->addressStreetNumber;
    }

    /**
     * @param mixed $addressStreetNumber
     */
    public function setAddressStreetNumber($addressStreetNumber)
    {
        $this->addressStreetNumber = $addressStreetNumber;
    }

    /**
     * @return mixed
     */
    public function getAddressUnit()
    {
        return $this->addressUnit;
    }

    /**
     * @param mixed $addressUnit
     */
    public function setAddressUnit($addressUnit)
    {
        $this->addressUnit = $addressUnit;
    }

    /**
     * @return mixed
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @param mixed $availability
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;
    }

    /**
     * @return mixed
     */
    public function getAvailabilityPeriod()
    {
        return $this->availabilityPeriod;
    }

    /**
     * @param mixed $availabilityPeriod
     */
    public function setAvailabilityPeriod($availabilityPeriod)
    {
        $this->availabilityPeriod = $availabilityPeriod;
    }

    /**
     * @return mixed
     */
    public function getDateAvailability()
    {
        return $this->dateAvailability;
    }

    /**
     * @param mixed $dateAvailability
     */
    public function setDateAvailability($dateAvailability)
    {
        $this->dateAvailability = $dateAvailability;
    }

    /**
     * @return mixed
     */
    public function getCitiesWorking()
    {
        return $this->citiesWorking;
    }

    /**
     * @param mixed $citiesWorking
     */
    public function setCitiesWorking($citiesWorking)
    {
        $this->citiesWorking = $citiesWorking;
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param mixed $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return mixed
     */
    public function getMatricCertificate()
    {
        return $this->matricCertificate;
    }

    /**
     * @param mixed $matricCertificate
     */
    public function setMatricCertificate($matricCertificate)
    {
        $this->matricCertificate = $matricCertificate;
    }

    /**
     * @return mixed
     */
    public function getTertiaryCertificate()
    {
        return $this->tertiaryCertificate;
    }

    /**
     * @param mixed $tertiaryCertificate
     */
    public function setTertiaryCertificate($tertiaryCertificate)
    {
        $this->tertiaryCertificate = $tertiaryCertificate;
    }

    /**
     * @return mixed
     */
    public function getUniversityManuscript()
    {
        return $this->universityManuscript;
    }

    /**
     * @param mixed $universityManuscript
     */
    public function setUniversityManuscript($universityManuscript)
    {
        $this->universityManuscript = $universityManuscript;
    }

    /**
     * @return mixed
     */
    public function getCreditCheck()
    {
        return $this->creditCheck;
    }

    /**
     * @param mixed $creditCheck
     */
    public function setCreditCheck($creditCheck)
    {
        $this->creditCheck = $creditCheck;
    }

    /**
     * @return mixed
     */
    public function getCvFiles()
    {
        return $this->cvFiles;
    }

    /**
     * @param mixed $cvFiles
     */
    public function setCvFiles($cvFiles)
    {
        $this->cvFiles = $cvFiles;
    }

    /**
     * @return mixed
     */
    public function getLinkedinCheck()
    {
        return $this->linkedinCheck;
    }

    /**
     * @param mixed $linkedinCheck
     */
    public function setLinkedinCheck($linkedinCheck)
    {
        $this->linkedinCheck = $linkedinCheck;
    }

    /**
     * @return mixed
     */
    public function getLinkedinUrl()
    {
        return $this->linkedinUrl;
    }

    /**
     * @param mixed $linkedinUrl
     */
    public function setLinkedinUrl($linkedinUrl)
    {
        $this->linkedinUrl = $linkedinUrl;
    }

    /**
     * @return mixed
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param mixed $video
     */
    public function setVideo($video)
    {
        $this->video = $video;
    }

    /**
     * @return mixed
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * @param mixed $percentage
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
    }

    /**
     * @return mixed
     */
    public function getLooking()
    {
        return $this->looking;
    }

    /**
     * @param mixed $looking
     */
    public function setLooking($looking)
    {
        $this->looking = $looking;
    }

    /**
     * @return mixed
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param mixed $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param mixed $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return mixed
     */
    public function getPlay()
    {
        return $this->play;
    }

    /**
     * @param mixed $play
     */
    public function setPlay($play)
    {
        $this->play = $play;
    }

    /**
     * @return mixed
     */
    public function getLastDeactivated()
    {
        return $this->lastDeactivated;
    }

    /**
     * @param mixed $lastDeactivated
     */
    public function setLastDeactivated($lastDeactivated)
    {
        $this->lastDeactivated = $lastDeactivated;
    }

    /**
     * @param array $parameters
     */
    public function update($parameters = array()){
        foreach($parameters as $key => $value) {
            if(property_exists($this,$key) && $key!='user'){
                if($key == 'dateOfBirth' || $key == 'dateArticlesCompleted' || $key == 'dateAvailability'){
                    $now = new \DateTime();
                    $newDate =(!empty($value) && $value != "null") ? ($value instanceof \DateTime ) ? $value : new \DateTime($value) : NULL;
                    if($newDate instanceof \DateTime){
                        $newDate->setTimezone($now->getTimezone());
                    }
                    $this->$key = $newDate;
                }
                elseif ($key == 'citiesWorking'){
                    if(!empty($value) && $value != "null" && $value != NULL){
                        if(is_array($value)){
                            $this->$key = $value;
                        }
                        else{
                            $cities = explode(',',$value);
                            if(is_array($cities)){
                                $this->$key = $cities;
                            }
                            else{
                                $this->$key = NULL;
                            }
                        }
                    }
                    else{
                        $this->$key = NULL;
                    }
                }
                elseif ($key == 'costToCompany'){
                    if($value >= 0 ){
                        $this->$key = $value;
                    }
                    else{
                        $this->$key = NULL;
                    }
                }
                elseif(is_bool($value)){
                    $this->$key = $value;
                }
                else{
                    if(!empty($value) && $value != "null"){
                        $this->$key = $value;
                    }
                    else{
                        $this->$key = NULL;
                    }
                }
            }
        }
    }

    /**
     * @param array $parameters
     */
    public function updateForm($parameters = array()) {
        foreach($parameters as $key => $value) {
            if(property_exists($this,$key) && $key!='user'){
                if($value === 'null' || $value === NULL){
                    $this->$key = NULL;
                }
                else{
                    if($key == 'dateOfBirth' || $key == 'dateArticlesCompleted' || $key == 'dateAvailability'){
                        $now = new \DateTime();
                        $newDate =(!empty($value) && $value != "null") ? ($value instanceof \DateTime ) ? $value : new \DateTime($value) : NULL;
                        if($newDate instanceof \DateTime){
                            $newDate->setTimezone($now->getTimezone());
                        }
                        $this->$key = $newDate;
                    }
                    elseif ($key == 'criminal' || $key == 'credit' || $key == 'availability' || $key == 'linkedinCheck'){
                        if($value == "true" || $value === true){
                            $this->$key = true;
                        }
                        elseif ($value == "false" || $value === false){
                            $this->$key = false;
                        }
                        else{
                            $this->$key = NULL;
                        }
                    }
                    elseif ($key == 'citiesWorking'){
                        if(!empty($value)){
                            if(is_array($value)){
                                $this->$key = $value;
                            }
                            else{
                                $cities = explode(',',$value);
                                if(is_array($cities)){
                                    $this->$key = $cities;
                                }
                                else{
                                    $this->$key = NULL;
                                }
                            }
                        }
                        else{
                            $this->$key = NULL;
                        }
                    }
                    elseif ($key == 'costToCompany'){
                        if($value>=0){
                            $this->$key = $value;
                        }
                        else{
                            $this->$key = NULL;
                        }
                    }
                    else{
                        $this->$key = $value;
                    }
                }
            }
        }
    }

}