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
     * Retourne le dossier "home" d'un utilisateur
     */
    public function findHomeForUser(User $user): ?Folder
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.parent IS NULL')
            ->andWhere('LOWER(f.name) = :home')
            ->setParameter('user', $user)
            ->setParameter('home', 'home')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * MÉTHODE CENTRALE : construit l'arbo des dossiers de l'utilisateur et renvoie du JSON.
     * - 1 seule requête ORM (QueryBuilder) pour récupérer id, name, parent_id
     * - assemblage en mémoire (itératif, sans récursion)
     * - encodage JSON joliment formaté
     */
    public function getUserFolderTreeJson(int $userId): string
    {
        // 1) Charger les lignes depuis l’ORM (pas de SQL brut)
        $rows = $this->fetchRowsOrm($userId);

        // 2) Préparer les structures en mémoire
        [$nodes, $parents] = $this->prepareNodes($rows);
        // 3) Lier parent → enfants (modifie $nodes par référence)
        $this->linkNodes($nodes, $parents);
        
        // 4) Récupérer les ids racines (parent_id = NULL)
        $roots = $this->collectRoots($parents);

        // 5) Encoder uniquement les racines (forêt) en JSON
        return $this->encodeToJson($nodes, $roots);
    }

    /* ======================== Méthodes privées ======================== */

    /**
     * 1) ORM QueryBuilder : récupère un tableau associatif
     *    [
     *      ['id' => 1, 'name' => 'home',  'parent_id' => null],
     *      ['id' => 2, 'name' => 'dir1',  'parent_id' => 1],
     *      ...
     *    ]
     *
     *  Remarques :
     *  - IDENTITY(f.user) = :uid permet de filtrer par l'id utilisateur (int),
     *    sans avoir besoin de passer l'objet User.
     *  - IDENTITY(f.parent) AS parent_id donne la FK brute (int ou NULL).
     */
    private function fetchRowsOrm(int $userId): array
    {
        return $this->createQueryBuilder('f')
            ->select('f.id AS id, f.name AS name, IDENTITY(f.parent) AS parent_id')
            ->andWhere('IDENTITY(f.user) = :uid')
            ->setParameter('uid', $userId)
            // Tri simple et portable : parent d’abord, puis nom
            // (sur MySQL, NULL sort en premier avec ASC ; sur PG, c'est correct aussi
            // pour notre logique d’assemblage qui ne dépend pas de l’ordre strict)
            ->orderBy('f.parent', 'ASC')
            ->addOrderBy('f.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * 2) Prépare les structures :
     *    - $nodes   : id => ['id','name','type','children'=>[]]
     *    - $parents : id => parent_id|null
     *
     *  IMPORTANT : NE PAS caster un parent_id NULL en (int), sinon NULL devient 0.
     */
    private function prepareNodes(array $rows): array
    {
        $nodes   = [];
        $parents = [];

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $parentId = $row['parent_id'] !== null ? (int) $row['parent_id'] : null;

            $nodes[$id] = [
                'id'       => $id,
                'name'     => (string) $row['name'],
                'type'     => 'folder',
                'children' => [], // rempli à l’étape suivante
            ];
            $parents[$id] = $parentId;
        }

        return [$nodes, $parents];
    }

    /**
     * 3) Chaînage parent → enfants
     *    - Passe $nodes PAR RÉFÉRENCE (sinon les ajouts sont perdus à la sortie)
     *    - Ajoute les enfants PAR RÉFÉRENCE (évite de dupliquer les sous-arbres)
     */
    private function linkNodes(array &$nodes, array $parents): void
    {
        foreach ($parents as $id => $parentId) {
            if ($parentId === null) {
                // Pas de parent => racine
                continue;
            }
            if (isset($nodes[$parentId])) {
                // Lien par référence : le sous-arbre reste partagé (pas de copies)
                $nodes[$parentId]['children'][] = &$nodes[$id];
            }
            // Si le parent n'existe pas (donnée orpheline), on ignore simplement l’enfant.
            // Option : logger l’orphelin si tu veux surveiller la qualité de données.
        }
    }

    /**
     * 4) Collecte les ids des racines (parent_id NULL)
     */
    private function collectRoots(array $parents): array
    {
        $roots = [];
        foreach ($parents as $id => $parentId) {
            if ($parentId === null) {
                $roots[] = $id;
            }
        }
        return $roots;
    }

    /**
     * 5) Encode uniquement les racines en JSON.
     *    - On ne renvoie pas l’intégralité de $nodes, mais la "forêt" des racines.
     *    - Chez toi, il n’y a qu’une racine (“home”) par utilisateur.
     */
    private function encodeToJson(array $nodes, array $roots): string
    {
        $forest = [];
        foreach ($roots as $rootId) {
            if (isset($nodes[$rootId])) {
                $forest[] = $nodes[$rootId];
            }
        }
        return json_encode($forest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
