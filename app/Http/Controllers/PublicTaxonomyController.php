<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;

class PublicTaxonomyController extends Controller
{
    public function category(string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->published()
            ->whereBelongsTo($category)
            ->with(['author.user', 'tags'])
            ->latest('published_at')
            ->paginate(12);

        return view('taxonomies.category', [
            'entity' => $category,
            'posts' => $posts,
            'title' => $category->name,
            'description' => $category->description,
        ]);
    }

    public function tag(string $slug): View
    {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->published()
            ->whereHas('tags', static fn ($query) => $query->whereKey($tag->getKey()))
            ->with(['author.user', 'category'])
            ->latest('published_at')
            ->paginate(12);

        return view('taxonomies.tag', [
            'entity' => $tag,
            'posts' => $posts,
            'title' => $tag->name,
            'description' => $tag->description,
        ]);
    }

    public function author(string $slug): View
    {
        $author = Author::query()->where('slug', $slug)->with('user')->firstOrFail();

        $posts = Post::query()
            ->published()
            ->whereBelongsTo($author)
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->paginate(12);

        return view('taxonomies.author', [
            'entity' => $author,
            'posts' => $posts,
            'title' => $author->name,
            'description' => $author->bio,
        ]);
    }
}
