<?php

namespace App\Entity;

use App\Repository\FolderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FolderRepository::class)]
#[ORM\Table(name: 'folder')]
#[ORM\UniqueConstraint(
    name: 'uq_user_parent_name',
    columns: ['user_id', 'parent_id', 'name']
)]
#[ORM\HasLifecycleCallbacks]
class Folder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // Auto-référence vers le parent (nullable = racine)
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?self $parent = null;

    /** @var Collection<int, self> */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'folders')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du dossier est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 30,
        minMessage: '3 caractères minimum',
        maxMessage: '30 caractères maximum'
    )]
    #[Assert\Regex(
        pattern: '/^(?!-)(?![0-9])[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: "Nom invalide : lettres minuscules, chiffres et tirets uniquement, sans chiffre ou tiret au début/fin."
    )]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    // --- Lifecycle ---

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->created_at ??= $now;
        $this->updated_at ??= $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    // --- Getters/Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /** @return Collection<int, self> */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null); // orphanRemoval => supprimé
            }
        }
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    public function isHome(): bool
    {
        return $this->isRoot() && mb_strtolower($this->name ?? '') === 'home';
    }

    public function getPath(): string
    {
        $parts = [];
        $cur = $this;
        while ($cur) {
            $parts[] = $cur->getName();
            $cur = $cur->getParent();
        }
        $parts = array_reverse($parts);
        if (!empty($parts) && \mb_strtolower($parts[0]) !== 'home') {
        }
        return '/' . implode('/', $parts);
    }

    public function getPathElements(): array
    {
        $elements = [];
        $current = $this;

        while ($current !== null) {
            array_unshift($elements, $current); // ajoute au début
            $current = $current->getParent();
        }

        return $elements;
    }

}
