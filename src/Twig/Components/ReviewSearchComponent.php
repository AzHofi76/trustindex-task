<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * Live component to display instant search for Posts.
 *
 * @author Attila HOFFEREK <azhofi@gmail.com>
 */
#[AsLiveComponent(name: 'review_search')]
final class ReviewSearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private readonly ReviewRepository $reviewRepository,
    ) {
    }

    /**
     * @return array<Review>
     */
    public function getReviews(): array
    {
        return $this->reviewRepository->findBySearchQuery($this->query);
    }
}
