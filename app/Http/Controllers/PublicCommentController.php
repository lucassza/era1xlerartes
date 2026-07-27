<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PostCommentStatus;
use App\Http\Requests\StorePostCommentRequest;
use App\Models\Post;
use App\Services\EngagementService;
use Illuminate\Http\RedirectResponse;

class PublicCommentController extends Controller
{
    public function store(StorePostCommentRequest $request, Post $post, EngagementService $service): RedirectResponse
    {
        $comment = $service->submitComment($request->user(), $post, $request->validated());

        if ($comment->status === PostCommentStatus::Rejected) {
            return back()->with('engagement_status', 'Seu comentário não pôde ser enviado. Revise o conteúdo e tente novamente.');
        }

        return back()->with('engagement_status', 'Comentário enviado! Será publicado após aprovação do administrador.');
    }
}
