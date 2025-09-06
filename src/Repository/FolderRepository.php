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

    /**
     * Retourne le(s) dossier(s) "home" d’un utilisateur
     *
     * @param User $user
     * @return Folder[]
     */
    public function findHomeByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.name = :home')
            ->setParameter('user', $user)
            ->setParameter('home', 'home')
            ->getQuery()
            ->getResult(); // Retourne un tableau de Folder[]
    }

    /**
     * Retourne les enfants directs d’un dossier
     *
     * @param User $user
     * @param Folder $parent
     * @return Folder[]
     */
    public function findChildrenByParent(User $user, Folder $parent): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.parent = :parent')
            ->setParameter('user', $user)
            ->setParameter('parent', $parent)
            ->getQuery()
            ->getResult(); // Retourne un tableau de Folder[]
    }

    /**
     * Construit la structure récursive d’un dossier et de ses enfants
     *
     * @param User $user
     * @param Folder $parent
     * @return array
     */
    public function getFolderStructure(User $user, Folder $parent): array
    {
        return $this->buildFolderStructure($user, $parent);
    }

    /**
     * Méthode récursive pour construire un arbre de dossiers
     *
     * @param User $user
     * @param Folder $folder
     * @return array
     */
    private function buildFolderStructure(User $user, Folder $folder): array
    {
        $folderInfo = [
            'id' => $folder->getId(),
            'name' => $folder->getName(),
            'type' => 'folder',
            'children' => []
        ];

        $children = $this->findChildrenByParent($user, $folder);

        foreach ($children as $childFolder) {
            $folderInfo['children'][] = $this->buildFolderStructure($user, $childFolder);
        }

        return $folderInfo;
    }
}
