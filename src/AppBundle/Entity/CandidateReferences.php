<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CandidateReferences
 *
 * @ORM\Table(name="candidate_references")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CandidateReferencesRepository")
 */
class CandidateReferences
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @Assert\NotBlank(
     *     message="firstName should not be blank",
     *     groups={"validateReferences"}
     * )
     * @ORM\Column(type="string")
     */
    private $firstName;

    /**
     * @Assert\NotBlank(
     *     message="lastName should not be blank",
     *     groups={"validateReferences"}
     * )
     * @ORM\Column(type="string")
     */
    private $lastName;

    /**
     * @Assert\NotBlank(
     *     message="company should not be blank",
     *     groups={"validateReferences"}
     * )
     * @ORM\Column(type="string")
     */
    private $company;

    /**
     * @Assert\NotBlank(
     *     message="role should not be blank",
     *     groups={"validateReferences"}
     * )
     * @ORM\Column(type="string")
     */
    private $role;

    /**
     * @Assert\NotBlank(
     *     message="email should not be blank",
     *     groups={"validateReferences"}
     * )
     * @Assert\Email(
     *     message="email invalid",
     *     groups={"validateReferences"}
     * )
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @Assert\NotBlank(
     *     message="comment should not be blank",
     *     groups={"validateReferences"}
     * )
     * @ORM\Column(type="text")
     */
    private $comment;

    /**
     * @Assert\Type(
     *     type="boolean",
     *     message="permission should be boolean type",
     *     groups={"validateReferences"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $permission;

    /**
     * CandidateReferences constructor.
     * @param $user
     * @param $firstName
     * @param $lastName
     * @param $company
     * @param $role
     * @param $email
     * @param $comment
     * @param $permission
     */
    public function __construct($user, $firstName, $lastName, $company, $role, $email, $comment, $permission=false)
    {
        $this->user = $user;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->company = $company;
        $this->role = $role;
        $this->email = $email;
        $this->comment = $comment;
        $this->permission = $permission;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param mixed $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }

}
