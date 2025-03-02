<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\VideoGameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VideoGameRepository::class)]
// #[ApiResource(
//     operations: [new GetCollection(normalizationContext: ['groups' => ['VideoGame:read']]),
//     new Get(normalizationContext: ['groups' => ['VideoGame:read']])]
// )]
class VideoGame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['video_game:read', 'video_game:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['video_game:read', 'video_game:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['video_game:read', 'video_game:write'])]
    private ?\DateTimeInterface $releaseDate = null;

    #[ORM\Column(length: 255)]
    #[Groups(['video_game:read', 'video_game:write'])]
    private ?string $description = null;

    /**
     * @var Collection<int, Category>
     */
    #[Groups(['video_game:read', 'video_game:write'])]
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'videoGames')]
    private Collection $category;

    #[ORM\ManyToOne(inversedBy: 'videoGames')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['video_game:read', 'video_game:write'])]
    private ?Editor $Editor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover_image = null;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTimeInterface $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->category->removeElement($category);

        return $this;
    }

    public function getEditor(): ?Editor
    {
        return $this->Editor;
    }

    public function setEditor(?Editor $Editor): static
    {
        $this->Editor = $Editor;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->cover_image;
    }

    public function setCoverImage(?string $cover_image): static
    {
        $this->cover_image = $cover_image;

        return $this;
    }
}
