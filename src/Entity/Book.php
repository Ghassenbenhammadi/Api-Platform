<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: BookRepository::class)]

#[ApiResource(
//   operations: [
//     new Get(),
//     new GetCollection(),
//     new Post(),
//     new Put(),
//     new Patch(),
//     new Delete()
//   ]
)]
#[Get(normalizationContext:['groups'=>['read']])]
#[Post(
    inputFormats: ['multipart' => ['multipart/form-data']],
    normalizationContext:['groups'=>['read']],
    denormalizationContext:['groups' =>['write']]
)]
#[GetCollection()]
#[Put()]
#[Patch()]
#[Vich\Uploadable]
#[ApiFilter(SearchFilter::class,properties:['title' => 'exact', 'author' => 'exact'])]
#[ApiFilter(DateFilter::class,properties:['publicatedAt'])]
#[ApiFilter(OrderFilter::class, properties:['id' => 'DESC', 'title' =>'DESC'])]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['write','read'])]
    #[Assert\NotBlank]
    #[Assert\Length(
        min:3,
        max:20,
        minMessage: 'il faut avoir plus que {{ limit }} caratéres',
        maxMessage: 'il faut avoir moins que {{ limit }} caratéres',
    )]
    private ?string $author = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['write','read'])]
    #[Assert\NotBlank]
   // #[ApiFilter(SearchFilter::class, strategy : 'exact')]
   #[ApiFilter(OrderFilter::class, strategy:'DESC')] 
   private ?string $title = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['write'])]
    private ?\DateTimeImmutable $publicatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['write'])]
    #[Assert\NotBlank]
    private ?string $description = null;
    

    #[ORM\Column(nullable: true)]
    #[Groups(['write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?int $userId = null;

    #[ORM\OneToMany(mappedBy: 'book', targetEntity: Comment::class)]
    #[Groups(['read'])]
    private Collection $comments;
    #[Vich\UploadableField(mapping: 'book', fileNameProperty: 'imageName', size: 'imageSize')]
    #[Groups(['write'])]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPublicatedAt(): ?\DateTimeImmutable
    {
        return $this->publicatedAt;
    }

    public function setPublicatedAt(?\DateTimeImmutable $publicatedAt): static
    {
        $this->publicatedAt = $publicatedAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setBook($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getBook() === $this) {
                $comment->setBook(null);
            }
        }

        return $this;
    }
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }
}
