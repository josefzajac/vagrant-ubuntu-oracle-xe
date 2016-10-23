<?php

namespace App\Model\Repository;

use App\Model\Entity\User;

class Users extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getEntityClass()
    {
        return User::class;
    }

    public function create(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * @param $email
     * @param $password
     * @return User|null
     * @internal param $username
     */
    public function findByCredentials($email, $password)
    {
        return $this->repository()->findOneBy([
                'email'    => $email,
                'password' => $password,
            ]);
    }

    /**
     * @param $email
     * @return null|User
     */
    public function findByEmail($email)
    {
        return $this->repository()->findOneBy([
                'email' => $email,
            ]);
    }
}
