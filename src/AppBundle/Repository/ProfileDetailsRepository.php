<?php
/**
 * Created by PhpStorm.
 * Date: 17.04.18
 * Time: 14:47
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class ProfileDetailsRepository extends EntityRepository
{
    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCandidateDetails($id){
        return $this->createQueryBuilder('pd')
            ->select("pd.saicaStatus, pd.saicaNumber, pd.mostRole, pd.mostEmployer, pd.articlesFirm, pd.articlesFirmName, pd.nationality, pd.idNumber, pd.ethnicity,
                pd.gender, pd.dateOfBirth, pd.dateArticlesCompleted, pd.costToCompany, pd.criminal, pd.criminalDescription, pd.credit, pd.creditDescription, pd.otherQualifications, pd.boards, pd.homeAddress,
                pd.addressCountry, pd.addressState, pd.addressZipCode, pd.addressCity, pd.addressSuburb, pd.addressStreet, pd.addressStreetNumber, pd.addressUnit, 
                pd.availability, pd.availabilityPeriod, pd.dateAvailability, pd.citiesWorking, pd.picture, pd.matricCertificate, pd.tertiaryCertificate, pd.universityManuscript,
                pd.creditCheck, pd.cvFiles, pd.linkedinCheck, pd.linkedinUrl, pd.video, pd.percentage, pd.looking ,pd.visible")
            ->where("pd.user = :id")
            ->setParameter('id',$id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCandidateByIdForBusiness($id){
        return $this->createQueryBuilder('pd')
            ->from("AppBundle:User", "u")
            ->select("u.id, u.firstName, u.lastName, pd.articlesFirm, pd.articlesFirmName, pd.mostEmployer, pd.mostRole, pd.boards, pd.nationality, pd.ethnicity, pd.availability, pd.availabilityPeriod, pd.dateAvailability, pd.citiesWorking, pd.otherQualifications, pd.criminal, pd.criminalDescription, pd.credit, pd.creditDescription, pd.matricCertificate, pd.tertiaryCertificate, pd.universityManuscript, pd.creditCheck, pd.cvFiles, pd.video, pd.picture, pd.gender, pd.dateArticlesCompleted")
            ->where("pd.user = u.id")
            ->andWhere("pd.user = :id")
            ->setParameter('id',$id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getCandidateWithCriteria($params = array()){

        $query = $this->createQueryBuilder('pd')
            ->from("AppBundle:User", "u")
            ->select('pd')
            ->where("pd.user = u.id")
            ->andWhere('pd.percentage >= :percentage')
            ->setParameter('percentage', 50)
            ->andWhere("u.approved = :true")
            ->andWhere("u.enabled = :true")
            ->andWhere("pd.looking = :true")
            ->setParameter("true", true)
            ->orderBy('pd.percentage', 'DESC')
        ;

        if(isset($params['search']) && !empty($params['search'])){
            $search = explode(" ", $params['search']);
            if(count($search) > 1){
                $query->andWhere('(u.firstName LIKE :search1) AND (u.lastName LIKE :search2)')
                    ->setParameter('search1', '%'.$search[0].'%')
                    ->setParameter('search2', '%'.$search[1].'%');
            }
            else{
                $query->andWhere('(u.firstName LIKE :search) OR (u.lastName LIKE :search)')
                    ->setParameter('search', '%'.$params['search'].'%');
            }
        }

        if(isset($params['articlesFirm']) && $params['articlesFirm'] != 'null' && $params['articlesFirm'] != NULL){
            if(is_array($params['articlesFirm'])){
                $articlesFirm = $params['articlesFirm'];
            }
            else{
                $articlesFirm = explode(',',$params['articlesFirm']);
            }
            if(!empty($articlesFirm) && !in_array('All', $articlesFirm) ){
                $str = '(';
                foreach ($articlesFirm as $key=>$article){
                    if($key>0){
                        $str .= " OR pd.articlesFirm='$article'";
                    }
                    else{
                        $str .= "pd.articlesFirm='$article'";
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }
        }

        if(isset($params['gender']) && $params['gender'] != 'All' && ($params['gender'] == 'Male' || $params['gender'] == 'Female')){
            $query->andWhere('pd.gender = :gender')
                ->setParameter('gender',$params['gender']);
        }

        if(isset($params['ethnicity']) && $params['ethnicity'] != 'null' && $params['ethnicity'] != NULL && !empty($params['ethnicity']) && $params['ethnicity']!="All"){
            $query->andWhere('pd.ethnicity = :ethnicity')
                ->setParameter('ethnicity', $params['ethnicity']);
        }

        if(isset($params['nationality']) && $params['nationality'] != 'null' && $params['nationality'] != NULL && $params['nationality'] >0){
            $query->andWhere('pd.nationality = :nationality')
                ->setParameter('nationality', $params['nationality']);
        }

        if(isset($params['location']) && $params['location'] != 'null' && $params['location'] != NULL && !empty($params['location']) && $params['location'] != 'All'){
            $query->andWhere('pd.citiesWorking LIKE :location')
                ->setParameter('location', '%'.$params['location'].'%');
        }

        if(isset($params['qualification']) && $params['qualification'] != 'null' && $params['qualification'] != NULL && $params['qualification']>0){
            if($params['qualification'] == 1 || $params['qualification'] == '1'){
                $query->andWhere('pd.boards IN (1,2)');
            }
            elseif($params['qualification'] == 2 || $params['qualification'] == '2'){
                $query->andWhere('pd.boards IN (3,4)');
            }
        }

        if(isset($params['video']) && $params['video'] != 'null' && $params['video'] != NULL && $params['video'] == 1){
            $query->andWhere('pd.video != :video')
                ->setParameter('video', serialize(NULL));
        }

        if(isset($params['availability']) && $params['availability'] != 'null' && $params['availability'] != NULL && $params['availability']>0 && $params['availability']<4){
            if($params['availability'] == 1){
                $now = new \DateTime();
                $query->andWhere("(pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now))")
                    ->setParameter('now', $now->format('Y-m-d'));
            }
            elseif ($params['availability'] == 2){
                $now = new \DateTime('+1 month');
                $query->andWhere("(pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now) OR pd.availabilityPeriod = 1)")
                    ->setParameter('now', $now->format('Y-m-d'));
            }
            else{
                $now = new \DateTime('+3month');
                $query->andWhere("((pd.availability = true) OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now) OR (pd.availabilityPeriod = 1 OR pd.availabilityPeriod = 2 OR pd.availabilityPeriod = 3))")
                    ->setParameter('now', $now->format('Y-m-d'));
            }

        }

        if(isset($params['articlesCompletedStart']) && $params['articlesCompletedStart'] != 'null' && $params['articlesCompletedStart'] != NULL){
            $articlesCompletedStart = ($params['articlesCompletedStart'] instanceof \DateTime) ? $params['articlesCompletedStart'] : new \DateTime($params['articlesCompletedStart']);
            $date_arr = explode(', ',$params['articlesCompletedStart']);
            if(isset($date_arr[1])){
                $articlesCompletedStart->setDate($date_arr[1],$articlesCompletedStart->format('m'),$articlesCompletedStart->format('d'));
            }
            $query->andWhere("DATE_FORMAT(pd.dateArticlesCompleted, '%Y-%m') >= :articlesCompletedStart")
                ->setParameter('articlesCompletedStart', $articlesCompletedStart->format('Y-m'));
        }

        if(isset($params['articlesCompletedEnd']) && $params['articlesCompletedEnd'] != 'null' && $params['articlesCompletedEnd'] != NULL){
            $articlesCompletedEnd = ($params['articlesCompletedEnd'] instanceof \DateTime) ? $params['articlesCompletedEnd'] : new \DateTime($params['articlesCompletedEnd']);
            $date_arr = explode(', ',$params['articlesCompletedEnd']);
            if(isset($date_arr[1])){
                $articlesCompletedEnd->setDate($date_arr[1],$articlesCompletedEnd->format('m'),$articlesCompletedEnd->format('d'));
            }
            $query->andWhere("DATE_FORMAT(pd.dateArticlesCompleted, '%Y-%m') <= :articlesCompletedEnd")
                ->setParameter('articlesCompletedEnd', $articlesCompletedEnd->format('Y-m'));
        }

        return $query->getQuery()->getResult();

    }

    /**
     * @param array $params
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountCandidateWithCriteria($params = array()){
        $query = $this->createQueryBuilder('pd')
            ->from("AppBundle:User", "u")
            ->select('COUNT(pd.id) as countCandidate')
            ->where("pd.user = u.id")
            ->andWhere('pd.percentage >= :percentage')
            ->setParameter('percentage', 50)
            ->andWhere("u.approved = :true")
            ->andWhere("u.enabled = :true")
            ->andWhere("pd.looking = :true")
            ->setParameter("true", true)
        ;

        if(isset($params['search']) && !empty($params['search'])){
            $search = explode(" ", $params['search']);
            if(count($search) > 1){
                $query->andWhere('(u.firstName LIKE :search1) AND (u.lastName LIKE :search2)')
                    ->setParameter('search1', '%'.$search[0].'%')
                    ->setParameter('search2', '%'.$search[1].'%');
            }
            else{
                $query->andWhere('(u.firstName LIKE :search) OR (u.lastName LIKE :search)')
                    ->setParameter('search', '%'.$params['search'].'%');
            }
        }

        if(isset($params['articlesFirm']) && $params['articlesFirm'] != 'null' && $params['articlesFirm'] != NULL){
            if(is_array($params['articlesFirm'])){
                $articlesFirm = $params['articlesFirm'];
            }
            else{
                $articlesFirm = explode(',',$params['articlesFirm']);
            }
            if(!empty($articlesFirm) && !in_array('All', $articlesFirm) ){
                $str = '(';
                foreach ($articlesFirm as $key=>$article){
                    if($key>0){
                        $str .= " OR pd.articlesFirm='$article'";
                    }
                    else{
                        $str .= "pd.articlesFirm='$article'";
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }
        }

        if(isset($params['gender']) && $params['gender'] != 'All' && ($params['gender'] == 'Male' || $params['gender'] == 'Female')){
            $query->andWhere('pd.gender = :gender')
                ->setParameter('gender',$params['gender']);
        }

        if(isset($params['ethnicity']) && $params['ethnicity'] != 'null' && $params['ethnicity'] != NULL && !empty($params['ethnicity']) && $params['ethnicity']!="All"){
            $query->andWhere('pd.ethnicity = :ethnicity')
                ->setParameter('ethnicity', $params['ethnicity']);
        }

        if(isset($params['nationality']) && $params['nationality'] != 'null' && $params['nationality'] != NULL && $params['nationality'] >0){
            $query->andWhere('pd.nationality = :nationality')
                ->setParameter('nationality', $params['nationality']);
        }

        if(isset($params['location']) && $params['location'] != 'null' && $params['location'] != NULL && !empty($params['location']) && $params['location'] != 'All'){
            $query->andWhere('pd.citiesWorking LIKE :location')
                ->setParameter('location', '%'.$params['location'].'%');
        }

        if(isset($params['qualification']) && $params['qualification'] != 'null' && $params['qualification'] != NULL && $params['qualification']>0){
            if($params['qualification'] == 1 || $params['qualification'] == '1'){
                $query->andWhere('pd.boards IN (1,2)');
            }
            elseif($params['qualification'] == 2 || $params['qualification'] == '2'){
                $query->andWhere('pd.boards IN (3,4)');
            }
        }

        if(isset($params['video']) && $params['video'] != 'null' && $params['video'] != NULL && $params['video'] == 1){
            $query->andWhere('pd.video != :video')
                ->setParameter('video', serialize(NULL));
        }

        if(isset($params['availability']) && $params['availability'] != 'null' && $params['availability'] != NULL && $params['availability']>0 && $params['availability']<4){
            if($params['availability'] == 1){
                $now = new \DateTime();
                $query->andWhere("(pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now))")
                    ->setParameter('now', $now->format('Y-m-d'));
            }
            elseif ($params['availability'] == 2){
                $now = new \DateTime('+1 month');
                $query->andWhere("(pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now) OR pd.availabilityPeriod = 1)")
                    ->setParameter('now', $now->format('Y-m-d'));
            }
            else{
                $now = new \DateTime('+3month');
                $query->andWhere("((pd.availability = true) OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now) OR (pd.availabilityPeriod = 1 OR pd.availabilityPeriod = 2 OR pd.availabilityPeriod = 3))")
                    ->setParameter('now', $now->format('Y-m-d'));
            }
        }

        if(isset($params['articlesCompletedStart']) && $params['articlesCompletedStart'] != 'null' && $params['articlesCompletedStart'] != NULL){
            $articlesCompletedStart = ($params['articlesCompletedStart'] instanceof \DateTime) ? $params['articlesCompletedStart'] : new \DateTime($params['articlesCompletedStart']);
            $date_arr = explode(', ',$params['articlesCompletedStart']);
            if(isset($date_arr[1])){
                $articlesCompletedStart->setDate($date_arr[1],$articlesCompletedStart->format('m'),$articlesCompletedStart->format('d'));
            }
            $query->andWhere("DATE_FORMAT(pd.dateArticlesCompleted, '%Y-%m') >= :articlesCompletedStart")
                ->setParameter('articlesCompletedStart', $articlesCompletedStart->format('Y-m'));
        }

        if(isset($params['articlesCompletedEnd']) && $params['articlesCompletedEnd'] != 'null' && $params['articlesCompletedEnd'] != NULL){
            $articlesCompletedEnd = ($params['articlesCompletedEnd'] instanceof \DateTime) ? $params['articlesCompletedEnd'] : new \DateTime($params['articlesCompletedEnd']);
            $date_arr = explode(', ',$params['articlesCompletedEnd']);
            if(isset($date_arr[1])){
                $articlesCompletedEnd->setDate($date_arr[1],$articlesCompletedEnd->format('m'),$articlesCompletedEnd->format('d'));
            }
            $query->andWhere("DATE_FORMAT(pd.dateArticlesCompleted, '%Y-%m') <= :articlesCompletedEnd")
                ->setParameter('articlesCompletedEnd', $articlesCompletedEnd->format('Y-m'));
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param array $params
     * @param bool $visible
     * @return mixed
     */
    public function getCandidateWithCriteriaWithVisible($params = array(), $visible=true){

        $query = $this->createQueryBuilder('pd')
            ->from("AppBundle:User", "u")
            ->select('pd')
            ->where("pd.user = u.id")
            ->andWhere('pd.percentage > :percentage')
            ->setParameter('percentage', 50)
            ->andWhere("u.approved = :true")
            ->andWhere("u.enabled = :true")
            ->andWhere("pd.looking = :true")
            ->setParameter("true", true)
            ->orderBy('pd.percentage', 'DESC')
        ;
        if($visible == true){
            $query->andWhere("pd.visible = :visible")
                ->setParameter("visible", true);
        }

        if(isset($params['search']) && !empty($params['search'])){
            $search = explode(" ", $params['search']);
            if(count($search) > 1){
                $query->andWhere('(u.firstName LIKE :search1) AND (u.lastName LIKE :search2)')
                    ->setParameter('search1', '%'.$search[0].'%')
                    ->setParameter('search2', '%'.$search[1].'%');
            }
            else{
                $query->andWhere('(u.firstName LIKE :search) OR (u.lastName LIKE :search)')
                    ->setParameter('search', '%'.$params['search'].'%');
            }
        }

        if(isset($params['articlesFirm']) && $params['articlesFirm'] != 'null' && $params['articlesFirm'] != NULL){
            if(is_array($params['articlesFirm'])){
                $articlesFirm = $params['articlesFirm'];
            }
            else{
                $articlesFirm = explode(',',$params['articlesFirm']);
            }
            if(!empty($articlesFirm) && !in_array('All', $articlesFirm) ){
                $str = '(';
                foreach ($articlesFirm as $key=>$article){
                    if($key>0){
                        $str .= " OR pd.articlesFirm='$article'";
                    }
                    else{
                        $str .= "pd.articlesFirm='$article'";
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }
        }

        if(isset($params['gender']) && $params['gender'] != 'All' && ($params['gender'] == 'Male' || $params['gender'] == 'Female')){
            $query->andWhere('pd.gender = :gender')
                ->setParameter('gender',$params['gender']);
        }

        if(isset($params['ethnicity']) && $params['ethnicity'] != 'null' && $params['ethnicity'] != NULL && !empty($params['ethnicity']) && $params['ethnicity']!="All"){
            $query->andWhere('pd.ethnicity = :ethnicity')
                ->setParameter('ethnicity', $params['ethnicity']);
        }

        if(isset($params['nationality']) && $params['nationality'] != 'null' && $params['nationality'] != NULL && $params['nationality'] >0){
            $query->andWhere('pd.nationality = :nationality')
                ->setParameter('nationality', $params['nationality']);
        }

        if(isset($params['location']) && $params['location'] != 'null' && $params['location'] != NULL && !empty($params['location']) && $params['location'] != 'All'){
            $query->andWhere('pd.citiesWorking LIKE :location')
                ->setParameter('location', '%'.$params['location'].'%');
        }

        if(isset($params['qualification']) && $params['qualification'] != 'null' && $params['qualification'] != NULL && $params['qualification']>0){
            if($params['qualification'] == 1 || $params['qualification'] == '1'){
                $query->andWhere('pd.boards IN (1,2)');
            }
            elseif($params['qualification'] == 2 || $params['qualification'] == '2'){
                $query->andWhere('pd.boards IN (3,4)');
            }
        }

        if(isset($params['video']) && $params['video'] != 'null' && $params['video'] != NULL && $params['video'] == 1){
            $query->andWhere('pd.video != :video')
                ->setParameter('video', serialize(NULL));
        }

        if(isset($params['availability']) && $params['availability'] != 'null' && $params['availability'] != NULL && $params['availability']>0 && $params['availability']<4){
            if($params['availability'] == 1){
                $now = new \DateTime();
                $query->andWhere("(pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now))")
                    ->setParameter('now', $now->format('Y-m-d'));
            }
            elseif ($params['availability'] == 2){
                $now = new \DateTime('+1 month');
                $query->andWhere("(pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now) OR pd.availabilityPeriod = 1)")
                    ->setParameter('now', $now->format('Y-m-d'));
            }
            else{
                $now = new \DateTime('+3month');
                $query->andWhere("((pd.availability = true) OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now) OR (pd.availabilityPeriod = 1 OR pd.availabilityPeriod = 2 OR pd.availabilityPeriod = 3))")
                    ->setParameter('now', $now->format('Y-m-d'));
            }

        }

        if(isset($params['articlesCompletedStart']) && $params['articlesCompletedStart'] != 'null' && $params['articlesCompletedStart'] != NULL){
            $articlesCompletedStart = ($params['articlesCompletedStart'] instanceof \DateTime) ? $params['articlesCompletedStart'] : new \DateTime($params['articlesCompletedStart']);
            $date_arr = explode(', ',$params['articlesCompletedStart']);
            if(isset($date_arr[1])){
                $articlesCompletedStart->setDate($date_arr[1],$articlesCompletedStart->format('m'),$articlesCompletedStart->format('d'));
            }
            $query->andWhere("DATE_FORMAT(pd.dateArticlesCompleted, '%Y-%m') >= :articlesCompletedStart")
                ->setParameter('articlesCompletedStart', $articlesCompletedStart->format('Y-m'));
        }

        if(isset($params['articlesCompletedEnd']) && $params['articlesCompletedEnd'] != 'null' && $params['articlesCompletedEnd'] != NULL){
            $articlesCompletedEnd = ($params['articlesCompletedEnd'] instanceof \DateTime) ? $params['articlesCompletedEnd'] : new \DateTime($params['articlesCompletedEnd']);
            $date_arr = explode(', ',$params['articlesCompletedEnd']);
            if(isset($date_arr[1])){
                $articlesCompletedEnd->setDate($date_arr[1],$articlesCompletedEnd->format('m'),$articlesCompletedEnd->format('d'));
            }
            $query->andWhere("DATE_FORMAT(pd.dateArticlesCompleted, '%Y-%m') <= :articlesCompletedEnd")
                ->setParameter('articlesCompletedEnd', $articlesCompletedEnd->format('Y-m'));
        }

        return $query->getQuery()->getResult();

    }

    /**
     * @param array $params
     * @param bool $visible
     * @return mixed
     */
    public function getCandidateWithCriteriaWithVisibleNew($params = array(), $visible=true){

        $query = $this->createQueryBuilder('pd')
            ->from("AppBundle:User", "u")
            ->select('pd')
            ->where("pd.user = u.id")
            ->andWhere('pd.percentage > :percentage')
            ->setParameter('percentage', 50)
            ->andWhere("u.approved = :true")
            ->andWhere("u.enabled = :true")
            ->andWhere("pd.looking = :true")
            ->setParameter("true", true)
            ->orderBy('pd.percentage', 'DESC')
        ;
        if($visible == true){
            $query->andWhere("pd.visible = :visible")
                ->setParameter("visible", true);
        }

        if(isset($params['search']) && !empty($params['search'])){
            $search = explode(" ", $params['search']);
            if(count($search) > 1){
                $query->andWhere('(u.firstName LIKE :search1) AND (u.lastName LIKE :search2)')
                    ->setParameter('search1', '%'.$search[0].'%')
                    ->setParameter('search2', '%'.$search[1].'%');
            }
            else{
                $query->andWhere('(u.firstName LIKE :search) OR (u.lastName LIKE :search)')
                    ->setParameter('search', '%'.$params['search'].'%');
            }
        }

        if(isset($params['articlesFirm']) && $params['articlesFirm'] != 'null' && $params['articlesFirm'] != NULL){
            if(is_array($params['articlesFirm'])){
                $articlesFirm = $params['articlesFirm'];
            }
            else{
                $articlesFirm = explode(',',$params['articlesFirm']);
            }
            if(!empty($articlesFirm) && !in_array('All', $articlesFirm) ){
                $str = '(';
                foreach ($articlesFirm as $key=>$article){
                    if($key>0){
                        $str .= " OR pd.articlesFirm='$article'";
                    }
                    else{
                        $str .= "pd.articlesFirm='$article'";
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }
        }

        if(isset($params['gender']) && $params['gender'] != 'null' && $params['gender'] != NULL){
            if(is_array($params['gender'])){
                $genders = $params['gender'];
            }
            else{
                $genders = explode(',',$params['gender']);
            }
            if(!empty($genders) && !in_array('All', $genders) ){
                $str = '(';
                foreach ($genders as $key=>$gender){
                    if($key>0){
                        $str .= " OR pd.gender='$gender'";
                    }
                    else{
                        $str .= "pd.gender='$gender'";
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }
        }

        if(isset($params['ethnicity']) && $params['ethnicity'] != 'null' && $params['ethnicity'] != NULL){
            if(is_array($params['ethnicity'])){
                $ethnicitys = $params['ethnicity'];
            }
            else{
                $ethnicitys = explode(',',$params['ethnicity']);
            }
            if(!empty($ethnicitys) && !in_array('All', $ethnicitys) ){
                $str = '(';
                foreach ($ethnicitys as $key=>$ethnicity){
                    if($key>0){
                        $str .= " OR pd.ethnicity='$ethnicity'";
                    }
                    else{
                        $str .= "pd.ethnicity='$ethnicity'";
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }
        }

        if(isset($params['nationality']) && $params['nationality'] != 'null' && $params['nationality'] != NULL && $params['nationality'] != 0){
            if(is_array($params['nationality'])){
                $nationalitys = $params['nationality'];
            }
            else{
                $nationalitys = explode(',',$params['nationality']);
            }
            if(!empty($nationalitys) && !in_array('All', $nationalitys) ){
                $str = '(';
                foreach ($nationalitys as $key=>$nationality){
                    if($key>0){
                        $str .= " OR pd.nationality='$nationality'";
                    }
                    else{
                        $str .= "pd.nationality='$nationality'";
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }
        }

        if(isset($params['location']) && $params['location'] != 'null' && $params['location'] != NULL){
            if(is_array($params['location'])){
                $locations = $params['location'];
            }
            else{
                $locations = explode(',',$params['location']);
            }
            if(!empty($locations) && !in_array('All', $locations) ){
                $str = '(';
                foreach ($locations as $key=>$location){
                    $location = "%".$location."%";
                    if($key>0){
                        $str .= " OR pd.citiesWorking LIKE '$location'";
                    }
                    else{
                        $str .= "pd.citiesWorking LIKE '$location'";
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }
        }

        if(isset($params['qualification']) && $params['qualification'] != 'null' && $params['qualification'] != NULL){
            if(is_array($params['qualification'])){
                $qualifications = $params['qualification'];
            }
            else{
                $qualifications = explode(',',$params['qualification']);
            }
            if(!empty($qualifications) && !in_array('All', $qualifications) ){
                $str = '(';
                foreach ($qualifications as $key=>$qualification){
                    if($qualification == 1 || $qualification == '1'){
                        if($key>0){
                            $str .= " OR pd.boards IN (1,2)";
                        }
                        else{
                            $str .= "pd.boards IN (1,2)";
                        }
                    }
                    elseif($qualification == 2 || $qualification == '2'){
                        if($key>0){
                            $str .= " OR pd.boards IN (3,4)";
                        }
                        else{
                            $str .= "pd.boards IN (3,4)";
                        }
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }
        }

        if(isset($params['availability']) && $params['availability'] != 'null' && $params['availability'] != NULL && $params['availability'] != 0){
            if(is_array($params['availability'])){
                $availabilitys = $params['availability'];
            }
            else{
                $availabilitys = explode(',',$params['availability']);
            }
            if(!empty($availabilitys) && !in_array('All', $availabilitys) ){
                $str = '(';
                foreach ($availabilitys as $key=>$availability){
                    if($availability == 1){
                        $nowD = new \DateTime();
                        $now = $nowD->format('Y-m-d');
                        if($key>0){
                            $str .= " OR (pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= '$now'))";
                        }
                        else{
                            $str .= "(pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= '$now'))";
                        }
                    }
                    elseif ($availability == 2){
                        $nowD = new \DateTime('+1 month');
                        $now = $nowD->format('Y-m-d');
                        if($key>0){
                            $str .= " OR (pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= '$now') OR pd.availabilityPeriod = 1)";
                        }
                        else{
                            $str .= "(pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= '$now') OR pd.availabilityPeriod = 1)";
                        }
                    }
                    elseif ($availability == 3){
                        $nowD = new \DateTime('+3month');
                        $now = $nowD->format('Y-m-d');
                        if($key>0){
                            $str .= " OR ((pd.availability = true) OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= '$now') OR (pd.availabilityPeriod = 1 OR pd.availabilityPeriod = 2 OR pd.availabilityPeriod = 3))";
                        }
                        else{
                            $str .= "((pd.availability = true) OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= '$now') OR (pd.availabilityPeriod = 1 OR pd.availabilityPeriod = 2 OR pd.availabilityPeriod = 3))";
                        }
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }

        }

        if(isset($params['articlesCompletedStart']) && $params['articlesCompletedStart'] != 'null' && $params['articlesCompletedStart'] != NULL){
            $articlesCompletedStart = ($params['articlesCompletedStart'] instanceof \DateTime) ? $params['articlesCompletedStart'] : new \DateTime($params['articlesCompletedStart']);
            $date_arr = explode(', ',$params['articlesCompletedStart']);
            if(isset($date_arr[1])){
                $articlesCompletedStart->setDate($date_arr[1],$articlesCompletedStart->format('m'),$articlesCompletedStart->format('d'));
            }
            $query->andWhere("DATE_FORMAT(pd.dateArticlesCompleted, '%Y-%m') >= :articlesCompletedStart")
                ->setParameter('articlesCompletedStart', $articlesCompletedStart->format('Y-m'));
        }

        if(isset($params['articlesCompletedEnd']) && $params['articlesCompletedEnd'] != 'null' && $params['articlesCompletedEnd'] != NULL){
            $articlesCompletedEnd = ($params['articlesCompletedEnd'] instanceof \DateTime) ? $params['articlesCompletedEnd'] : new \DateTime($params['articlesCompletedEnd']);
            $date_arr = explode(', ',$params['articlesCompletedEnd']);
            if(isset($date_arr[1])){
                $articlesCompletedEnd->setDate($date_arr[1],$articlesCompletedEnd->format('m'),$articlesCompletedEnd->format('d'));
            }
            $query->andWhere("DATE_FORMAT(pd.dateArticlesCompleted, '%Y-%m') <= :articlesCompletedEnd")
                ->setParameter('articlesCompletedEnd', $articlesCompletedEnd->format('Y-m'));
        }

        return $query->getQuery()->getResult();

    }

    /**
     * @param array $params
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountCandidateWithCriteriaWithVisible($params = array()){
        $query = $this->createQueryBuilder('pd')
            ->from("AppBundle:User", "u")
            ->select('COUNT(pd.id) as countCandidate')
            ->where("pd.user = u.id")
            ->andWhere('pd.percentage >= :percentage')
            ->setParameter('percentage', 50)
            ->andWhere("u.approved = :true")
            ->andWhere("u.enabled = :true")
            ->andWhere("pd.looking = :true")
            ->andWhere("pd.visible = :true")
            ->setParameter("true", true)
        ;

        if(isset($params['search']) && !empty($params['search'])){
            $search = explode(" ", $params['search']);
            if(count($search) > 1){
                $query->andWhere('(u.firstName LIKE :search1) AND (u.lastName LIKE :search2)')
                    ->setParameter('search1', '%'.$search[0].'%')
                    ->setParameter('search2', '%'.$search[1].'%');
            }
            else{
                $query->andWhere('(u.firstName LIKE :search) OR (u.lastName LIKE :search)')
                    ->setParameter('search', '%'.$params['search'].'%');
            }
        }

        if(isset($params['articlesFirm']) && $params['articlesFirm'] != 'null' && $params['articlesFirm'] != NULL){
            if(is_array($params['articlesFirm'])){
                $articlesFirm = $params['articlesFirm'];
            }
            else{
                $articlesFirm = explode(',',$params['articlesFirm']);
            }
            if(!empty($articlesFirm) && !in_array('All', $articlesFirm) ){
                $str = '(';
                foreach ($articlesFirm as $key=>$article){
                    if($key>0){
                        $str .= " OR pd.articlesFirm='$article'";
                    }
                    else{
                        $str .= "pd.articlesFirm='$article'";
                    }
                }
                $str .= ')';
                $query->andWhere($str);
            }
        }

        if(isset($params['gender']) && $params['gender'] != 'All' && ($params['gender'] == 'Male' || $params['gender'] == 'Female')){
            $query->andWhere('pd.gender = :gender')
                ->setParameter('gender',$params['gender']);
        }

        if(isset($params['ethnicity']) && $params['ethnicity'] != 'null' && $params['ethnicity'] != NULL && !empty($params['ethnicity']) && $params['ethnicity']!="All"){
            $query->andWhere('pd.ethnicity = :ethnicity')
                ->setParameter('ethnicity', $params['ethnicity']);
        }

        if(isset($params['nationality']) && $params['nationality'] != 'null' && $params['nationality'] != NULL && $params['nationality'] >0){
            $query->andWhere('pd.nationality = :nationality')
                ->setParameter('nationality', $params['nationality']);
        }

        if(isset($params['location']) && $params['location'] != 'null' && $params['location'] != NULL && !empty($params['location']) && $params['location'] != 'All'){
            $query->andWhere('pd.citiesWorking LIKE :location')
                ->setParameter('location', '%'.$params['location'].'%');
        }

        if(isset($params['qualification']) && $params['qualification'] != 'null' && $params['qualification'] != NULL && $params['qualification']>0){
            if($params['qualification'] == 1 || $params['qualification'] == '1'){
                $query->andWhere('pd.boards IN (1,2)');
            }
            elseif($params['qualification'] == 2 || $params['qualification'] == '2'){
                $query->andWhere('pd.boards IN (3,4)');
            }
        }

        if(isset($params['video']) && $params['video'] != 'null' && $params['video'] != NULL && $params['video'] == 1){
            $query->andWhere('pd.video != :video')
                ->setParameter('video', serialize(NULL));
        }

        if(isset($params['availability']) && $params['availability'] != 'null' && $params['availability'] != NULL && $params['availability']>0 && $params['availability']<4){
            if($params['availability'] == 1){
                $now = new \DateTime();
                $query->andWhere("(pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now))")
                    ->setParameter('now', $now->format('Y-m-d'));
            }
            elseif ($params['availability'] == 2){
                $now = new \DateTime('+1 month');
                $query->andWhere("(pd.availability = true OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now) OR pd.availabilityPeriod = 1)")
                    ->setParameter('now', $now->format('Y-m-d'));
            }
            else{
                $now = new \DateTime('+3month');
                $query->andWhere("((pd.availability = true) OR (pd.availability = false AND pd.availabilityPeriod = 4 AND DATE_FORMAT(pd.dateAvailability, '%Y-%m-%d') <= :now) OR (pd.availabilityPeriod = 1 OR pd.availabilityPeriod = 2 OR pd.availabilityPeriod = 3))")
                    ->setParameter('now', $now->format('Y-m-d'));
            }
        }

        if(isset($params['articlesCompletedStart']) && $params['articlesCompletedStart'] != 'null' && $params['articlesCompletedStart'] != NULL){
            $articlesCompletedStart = ($params['articlesCompletedStart'] instanceof \DateTime) ? $params['articlesCompletedStart'] : new \DateTime($params['articlesCompletedStart']);
            $date_arr = explode(', ',$params['articlesCompletedStart']);
            if(isset($date_arr[1])){
                $articlesCompletedStart->setDate($date_arr[1],$articlesCompletedStart->format('m'),$articlesCompletedStart->format('d'));
            }
            $query->andWhere("DATE_FORMAT(pd.dateArticlesCompleted, '%Y-%m') >= :articlesCompletedStart")
                ->setParameter('articlesCompletedStart', $articlesCompletedStart->format('Y-m'));
        }

        if(isset($params['articlesCompletedEnd']) && $params['articlesCompletedEnd'] != 'null' && $params['articlesCompletedEnd'] != NULL){
            $articlesCompletedEnd = ($params['articlesCompletedEnd'] instanceof \DateTime) ? $params['articlesCompletedEnd'] : new \DateTime($params['articlesCompletedEnd']);
            $date_arr = explode(', ',$params['articlesCompletedEnd']);
            if(isset($date_arr[1])){
                $articlesCompletedEnd->setDate($date_arr[1],$articlesCompletedEnd->format('m'),$articlesCompletedEnd->format('d'));
            }
            $query->andWhere("DATE_FORMAT(pd.dateArticlesCompleted, '%Y-%m') <= :articlesCompletedEnd")
                ->setParameter('articlesCompletedEnd', $articlesCompletedEnd->format('Y-m'));
        }

        return $query->getQuery()->getOneOrNullResult();
    }
}