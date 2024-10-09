<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Item;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartRepository::class)]
#[ApiResource
(
    denormalizationContext: ['groups' => ['cart:write']],
    normalizationContext: ['groups' => ['cart:read']]
)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(['cart:read'])]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'cart')]
    #[Groups(['cart:read', 'cart:write'])]
    private $items = [];

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Groups(['cart:read', 'cart:write'])]
    private ?string $total = '0.00';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items): static
    {
        $this->items = $items;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;

        return $this;
    }
    #[Post(
        uriTemplate: '/carts/{id}/addItem',
        input: Item::class,
        name: 'add_item_to_cart',
        denormalizationContext: ['groups' => ['cart:add']],
        security: "is_granted('ROLE_USER')"
    )]
    public function addItemToCart(Item $item): Cart
    {
        $this->items[] = $item;
        // Mettez à jour le total du panier ici
        $this->total += $item->getPriceUnit();
        return $this;
    }
}