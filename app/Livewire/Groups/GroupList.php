<?php

declare(strict_types=1);

namespace App\Livewire\Groups;

use App\Domain\Group\Services\GroupService;
use App\Models\GroupCategory;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class GroupList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public ?int $categoryId = null;

    #[Url]
    public ?string $city = null;

    #[Url]
    public bool $verifiedOnly = false;

    public function updating(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'categoryId', 'city', 'verifiedOnly']);
    }

    public function render(GroupService $service)
    {
        $groups = $service->search([
            'q'           => $this->search,
            'category_id' => $this->categoryId,
            'city'        => $this->city,
            'verified'    => $this->verifiedOnly,
            'per_page'    => 12,
        ]);

        return view('livewire.groups.group-list', [
            'groups'     => $groups,
            'categories' => GroupCategory::active()->get(),
        ]);
    }
}