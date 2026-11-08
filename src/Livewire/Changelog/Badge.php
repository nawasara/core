<?php

namespace Nawasara\Core\Livewire\Changelog;

use Livewire\Attributes\On;
use Livewire\Component;
use Nawasara\Core\Models\ChangelogEntry;

/**
 * Topbar "What's New" badge: a bell/sparkle icon that shows a count of
 * unseen published changelog entries and links to the Riwayat Update page.
 * Refreshes to zero when the user opens that page (changelog-seen event).
 */
class Badge extends Component
{
    public int $unread = 0;

    /** False for guests: the sparkle is not rendered at all. */
    public bool $visible = true;

    public function mount(): void
    {
        $this->visible = ChangelogEntry::visibleTo(auth()->user());
        $this->refreshCount();
    }

    #[On('changelog-seen')]
    public function refreshCount(): void
    {
        $this->unread = ($this->visible && ($id = auth()->id()))
            ? ChangelogEntry::unreadCountFor($id)
            : 0;
    }

    public function render()
    {
        return view('nawasara-core::livewire.shared.changelog-badge');
    }
}
