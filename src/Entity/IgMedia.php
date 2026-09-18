<?php

namespace App\Entity;

use App\Repository\IgMediaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IgMediaRepository::class)]
class IgMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $container_id = null;

    #[ORM\Column(length: 255)]
    private ?string $media_name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContainerId(): ?string
    {
        return $this->container_id;
    }

    public function setContainerId(string $container_id): static
    {
        $this->container_id = $container_id;

        return $this;
    }

    public function getMediaName(): ?string
    {
        return $this->media_name;
    }

    public function setMediaName(string $media_name): static
    {
        $this->media_name = $media_name;

        return $this;
    }
}
