<?php
namespace App\Repository;

use App\Entity\Folder;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Folder::class);
    }

    public function findHomeByUser(User $user): Folder
    {
        return $this->createQueryBuilder('f')
        ->where('f.name LIKE :home')
        ->andWhere('f.user = :user')
        ->setParameter('home', '%home%')
        ->setParameter('user',$user)
        ->getQuery()
        ->getOneOrNullResult();
    }

    public function findWithChildren(int $id): Folder
{
    return $this->createQueryBuilder('f')
        ->leftJoin('f.children', 'c')
        ->addSelect('c')
        ->where('f.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult();
}

}
