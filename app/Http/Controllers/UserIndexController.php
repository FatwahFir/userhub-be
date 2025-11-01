<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class UserIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $perPage = (int) $request->input('size', 20);
        $perPage = max(1, min($perPage, 100));
        $page = (int) $request->input('page', 1);

        $query = User::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->input('q');
                $connection = $query->getModel()->getConnection();
                $operator = $connection->getDriverName() === 'pgsql' ? 'ilike' : 'like';
                $term = "%{$search}%";

                $query->where(function ($inner) use ($operator, $term) {
                    $inner->where('name', $operator, $term)
                        ->orWhere('email', $operator, $term)
                        ->orWhere('username', $operator, $term);
                });
            })
            ->orderBy('name');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $users = $paginator->getCollection()
            ->map(fn (User $user) => (new UserResource($user))->toArray($request))
            ->all();

        return ApiResponse::success(
            data: $users,
            pagination: [
                'page' => $paginator->currentPage(),
                'page_size' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ]
        );
    }
}
