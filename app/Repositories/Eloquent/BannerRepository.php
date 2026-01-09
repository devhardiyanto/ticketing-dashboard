<?php

namespace App\Repositories\Eloquent;

use App\Models\Core\Banner;
use App\Repositories\Contracts\BannerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BannerRepository implements BannerRepositoryInterface
{
    protected $model;

    public function __construct(Banner $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->orderBy('sequence')->orderBy('created_at', 'desc')->get();
    }

    public function getAll(array $params = []): LengthAwarePaginator
    {
        $query = $this->model->with('event')->newQuery();

        if (isset($params['search']) && $params['search']) {
            $search = $params['search'];
            $query->where('title', 'ilike', "%{$search}%");
        }

        $perPage = $params['limit'] ?? 10;
        return $query->orderBy('sequence')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    public function create(array $data): Model
    {
        // Auto-assign sequence to last + 1 if not provided
        if (!isset($data['sequence'])) {
            $maxSequence = $this->model->max('sequence');
            $data['sequence'] = $maxSequence ? $maxSequence + 1 : 1;
        }
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $banner = $this->find($id);
        if ($banner) {
            return $banner->update($data);
        }
        return false;
    }

    public function delete(int|string $id): bool
    {
        $banner = $this->find($id);
        if ($banner) {
            return $banner->delete();
        }
        return false;
    }

    public function reorder(array $ids): bool
    {
        return DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                $this->model->where('id', $id)->update(['sequence' => $index + 1]);
            }
            return true;
        });
    }
}
