<?php

namespace Nawasara\Core\Livewire\Changelog;

use Illuminate\Support\Collection;
use Livewire\Component;
use Nawasara\Core\Models\ChangelogEntry;

/**
 * Login "What's New" modal. Mounted once in the app layout, it pops up
 * automatically the first time a user lands on any page after a MAJOR update
 * they haven't seen yet — so nobody misses a significant release. Minor-only
 * updates don't trigger the popup (the topbar sparkle badge covers those);
 * they still appear in the list if a major one opens the modal.
 *
 * Dismissing marks everything seen, so it shows at most once per release wave
 * and never nags on the next page load.
 */
class WhatsNew extends Component
{
    /** Whether the modal should render open on this page load. */
    public bool $show = false;

    /** @var array<int, array{title:string, category:string, category_label:string, category_color:string, is_major:bool, version_tag:?string}> */
    public array $entries = [];

    public int $count = 0;

    public function mount(): void
    {
        $userId = auth()->id();
        if (! $userId) {
            return;
        }

        $unread = ChangelogEntry::unreadFor($userId);

        // Only interrupt for a major update; minor-only waves stay on the badge.
        if ($unread->isEmpty() || ! $unread->contains(fn (ChangelogEntry $e) => $e->is_major)) {
            return;
        }

        $this->entries = $this->present($unread);
        $this->count = $unread->count();
        $this->show = true;
    }

    /**
     * Dismiss the modal and mark every current update as seen (clears the
     * topbar badge too). Called on close, and by "Lihat Semua" before
     * navigating to the full changelog.
     */
    public function acknowledge(): void
    {
        if ($id = auth()->id()) {
            ChangelogEntry::markSeen($id);
            $this->dispatch('changelog-seen'); // refresh the topbar badge
        }
        $this->show = false;
    }

    /** @param Collection<int, ChangelogEntry> $entries */
    protected function present(Collection $entries): array
    {
        return $entries->map(fn (ChangelogEntry $e) => [
            'title'          => $e->title,
            'category'       => $e->category,
            'category_label' => $e->categoryLabel(),
            'category_color' => $e->categoryColor(),
            'is_major'       => $e->is_major,
            'version_tag'    => $e->version_tag,
        ])->all();
    }

    public function render()
    {
        return view('nawasara-core::livewire.shared.changelog-whats-new');
    }
}
