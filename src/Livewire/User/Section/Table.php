<?php

namespace Nawasara\Core\Livewire\User\Section;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Nawasara\Core\Attributes\RequiresSudo;
use Nawasara\Core\Constants\Constants;
use Nawasara\Core\Support\RoleAssignmentGuard;
use Nawasara\Core\Traits\WithSudo;
use Nawasara\Toaster\Concerns\HasToaster;

class Table extends Component
{
    use HasToaster;

    use WithPagination;
    use WithSudo;

    /**
     * Saringan daftar, diisi hanya oleh listener `filter` di server.
     * #[Locked] supaya browser tidak dapat menulisnya langsung melewati
     * Gate di filter(). Audit keamanan 15 Oktober 2026.
     */
    #[Locked]
    public $params = [];

    protected $listeners = [
        'refreshComponent' => '$refresh'
    ];

    #[Computed]
    public function items()
    {
        return User::filter($this->params)->with(['roles'])->paginate();

    }
    
    /**
     * Hapus user.
     *
     * Audit keamanan 15 Oktober 2026, temuan T6: method ini dipanggil lewat
     * event `confirm-delete` dari modal konfirmasi. Untuk event, Livewire
     * memanggil `__dispatch`, sehingga hook #[RequiresSudo] (yang menyaring
     * berdasarkan nama method) tidak pernah berjalan dan hapus user lolos
     * tanpa sudo. Atributnya dibiarkan untuk pemanggilan langsung, tetapi
     * penjaga yang sebenarnya adalah requireSudo() imperatif di bawah, yang
     * berlaku di kedua jalur.
     */
    #[On('confirm-delete')]
    #[RequiresSudo(reason: 'menghapus user')]
    public function delete($id)
    {
        Gate::authorize('core.user.delete');

        $model = User::with('roles')->findOrFail($id);

        // Temuan T10: akun developer hanya boleh dihapus developer, dan
        // tidak ada yang boleh menghapus akunnya sendiri.
        $denied = RoleAssignmentGuard::checkUserDeletion(
            auth()->user()?->getRoleNames()->all() ?? [],
            $model->getRoleNames()->all(),
            (int) $model->getKey() === (int) auth()->id(),
        );

        if ($denied !== null) {
            $this->dispatch('close-modal', id: 'modalConfirmDelete');
            $this->alert('error', $denied);

            return;
        }

        if (! $this->requireSudo('menghapus user')) {
            return;
        }

        $model->delete();

        /* close modal */
        $this->dispatch('close-modal', id: 'modalConfirmDelete');

        /* show alert */
        $this->alert('success', Constants::NOTIFICATION_SUCCESS_CREATE);
        
        /* refresh component */
        $this->dispatch('$refresh');
    }

    public function updatingfilter()
    {
        $this->resetPage();
    }

    #[On('filter')]
    public function filter($search = null, $selectedRole = null)
    {
        // Public listener — re-check the route-level gate so it can't be
        // poked at via JS dispatch from a non-permissioned session.
        Gate::authorize('core.user.view');

        $this->params = [
            'search' => $search,
            'selectedRole' => $selectedRole,
        ];
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.user.section.table')
            ->layout('nawasara-ui::components.layouts.app');
    }
}
