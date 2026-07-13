<?php

namespace Nawasara\Core\Livewire\Changelog;

use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Nawasara\Core\Models\ChangelogEntry;

/**
 * Admin screen to author changelog entries (create / edit / publish / delete).
 * Gated by core.changelog.manage. Entries are drafts until published.
 */
class Manage extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    #[Validate('required|string|max:150')]
    public string $title = '';

    #[Validate('required|string|max:5000')]
    public string $body = '';

    #[Validate('required|in:feature,improvement,fix,security')]
    public string $category = 'feature';

    public bool $isMajor = false;

    #[Validate('nullable|string|max:32')]
    public string $versionTag = '';

    public bool $publishNow = true;

    public function mount(): void
    {
        $this->authorize('core.changelog.manage');
    }

    public function edit(int $id): void
    {
        $this->authorize('core.changelog.manage');
        $entry = ChangelogEntry::findOrFail($id);

        $this->editingId  = $entry->id;
        $this->title      = $entry->title;
        $this->body       = $entry->body;
        $this->category   = $entry->category;
        $this->isMajor    = $entry->is_major;
        $this->versionTag = (string) $entry->version_tag;
        $this->publishNow = $entry->isPublished();

        $this->dispatch('modal-open:changelog-form');
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('modal-open:changelog-form');
    }

    public function save(): void
    {
        $this->authorize('core.changelog.manage');
        $this->validate();

        $data = [
            'title'        => $this->title,
            'body'         => $this->body,
            'category'     => $this->category,
            'is_major'     => $this->isMajor,
            'version_tag'  => $this->versionTag ?: null,
            'published_at' => $this->publishNow ? now() : null,
        ];

        if ($this->editingId) {
            $entry = ChangelogEntry::findOrFail($this->editingId);
            // Preserve the original publish time when it was already published.
            if ($this->publishNow && $entry->published_at) {
                unset($data['published_at']);
            }
            $entry->update($data);
        } else {
            $data['created_by'] = auth()->id();
            ChangelogEntry::create($data);
        }

        $this->resetForm();
        $this->dispatch('close-modal', 'changelog-form');
        $this->dispatch('toast', type: 'success', message: 'Catatan update disimpan.');
    }

    public function togglePublish(int $id): void
    {
        $this->authorize('core.changelog.manage');
        $entry = ChangelogEntry::findOrFail($id);
        $entry->update(['published_at' => $entry->isPublished() ? null : now()]);
    }

    public function delete(int $id): void
    {
        $this->authorize('core.changelog.manage');
        ChangelogEntry::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Catatan dihapus.');
    }

    protected function resetForm(): void
    {
        $this->reset('editingId', 'title', 'body', 'versionTag');
        $this->category = 'feature';
        $this->isMajor = false;
        $this->publishNow = true;
        $this->resetValidation();
    }

    public function render()
    {
        $entries = ChangelogEntry::orderByDesc('created_at')->paginate(15);

        return view('nawasara-core::livewire.pages.changelog.manage', [
            'entries' => $entries,
        ])->layout('nawasara-ui::components.layouts.app');
    }
}
