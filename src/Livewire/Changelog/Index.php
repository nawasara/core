<?php

namespace Nawasara\Core\Livewire\Changelog;

use Livewire\Component;
use Livewire\WithPagination;
use Nawasara\Core\Models\ChangelogEntry;

/**
 * Riwayat Update — the user-facing "What's New" page. Any logged-in user
 * except guests can read it (ChangelogEntry::visibleTo). Opening the page marks everything seen, which clears the topbar
 * badge.
 */
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(ChangelogEntry::visibleTo(auth()->user()), 404);

        // Viewing the page = the user has seen the latest updates.
        if ($id = auth()->id()) {
            ChangelogEntry::markSeen($id);
            // Tell the topbar badge to refresh to zero.
            $this->dispatch('changelog-seen');
        }
    }

    public function render()
    {
        $entries = ChangelogEntry::published()
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('nawasara-core::livewire.pages.changelog.index', [
            'entries' => $entries,
        ])->layout('nawasara-ui::components.layouts.app');
    }
}
