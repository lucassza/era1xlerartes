<?php

declare(strict_types=1);

namespace App\Media;

use App\Models\Author;
use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class BlogMediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return sprintf('%s/%s/%s/', $this->resolveRoot($media), $this->resolveDirectory($media), $media->model_id);
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'responsive-images/';
    }

    private function resolveRoot(Media $media): string
    {
        return match ($media->model_type) {
            Product::class, Brand::class, CatalogCategory::class => 'catalog',
            default => 'blog',
        };
    }

    private function resolveDirectory(Media $media): string
    {
        return match ($media->model_type) {
            Post::class => 'posts',
            Category::class => 'categories',
            Author::class => 'authors',
            User::class => 'users',
            Product::class => 'products',
            CatalogCategory::class => 'categories',
            Brand::class => 'brands',
            default => 'misc',
        };
    }
}
