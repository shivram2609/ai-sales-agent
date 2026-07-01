<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\ProspectContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProspectContactController extends Controller
{
    public function store(Request $request, Prospect $prospect)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:190'],
            'email' => [
                'nullable',
                'email',
                'max:190',
                Rule::unique('prospect_contacts', 'email')
                    ->where(fn ($query) => $query->where('prospect_id', $prospect->id)),
            ],
            'title' => ['nullable', 'string', 'max:190'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'phone' => ['nullable', 'string', 'max:80'],
            'source' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $hasContacts = $prospect->contacts()->exists();
        $makePrimary = $request->boolean('is_primary') || ! $hasContacts;

        DB::transaction(function () use ($prospect, $validated, $makePrimary) {
            if ($makePrimary) {
                $prospect->contacts()->update(['is_primary' => false]);
            }

            $prospect->contacts()->create([
                'name' => $this->clean($validated['name'] ?? null),
                'email' => $this->cleanLower($validated['email'] ?? null),
                'title' => $this->clean($validated['title'] ?? null),
                'linkedin_url' => $this->clean($validated['linkedin_url'] ?? null),
                'phone' => $this->clean($validated['phone'] ?? null),
                'source' => $validated['source'] ?? 'manual',
                'status' => $validated['status'] ?? ProspectContact::STATUS_ACTIVE,
                'is_primary' => $makePrimary,
                'notes' => $this->clean($validated['notes'] ?? null),
            ]);
        });

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('success', 'Contact added to prospect.');
    }

    public function edit(ProspectContact $prospectContact)
    {
        $prospectContact->load('prospect');

        return view('prospect-contacts.edit', [
            'contact' => $prospectContact,
            'statuses' => ProspectContact::statuses(),
            'sources' => ProspectContact::sources(),
        ]);
    }

    public function update(Request $request, ProspectContact $prospectContact)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:190'],
            'email' => [
                'nullable',
                'email',
                'max:190',
                Rule::unique('prospect_contacts', 'email')
                    ->where(fn ($query) => $query->where('prospect_id', $prospectContact->prospect_id))
                    ->ignore($prospectContact->id),
            ],
            'title' => ['nullable', 'string', 'max:190'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'phone' => ['nullable', 'string', 'max:80'],
            'source' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($prospectContact, $validated, $request) {
            if ($request->boolean('is_primary')) {
                ProspectContact::query()
                    ->where('prospect_id', $prospectContact->prospect_id)
                    ->where('id', '!=', $prospectContact->id)
                    ->update(['is_primary' => false]);
            }

            $prospectContact->update([
                'name' => $this->clean($validated['name'] ?? null),
                'email' => $this->cleanLower($validated['email'] ?? null),
                'title' => $this->clean($validated['title'] ?? null),
                'linkedin_url' => $this->clean($validated['linkedin_url'] ?? null),
                'phone' => $this->clean($validated['phone'] ?? null),
                'source' => $validated['source'] ?? 'manual',
                'status' => $validated['status'] ?? ProspectContact::STATUS_ACTIVE,
                'is_primary' => $request->boolean('is_primary'),
                'notes' => $this->clean($validated['notes'] ?? null),
            ]);
        });

        return redirect()
            ->route('prospects.show', $prospectContact->prospect_id)
            ->with('success', 'Contact updated.');
    }

    public function makePrimary(ProspectContact $prospectContact)
    {
        DB::transaction(function () use ($prospectContact) {
            ProspectContact::query()
                ->where('prospect_id', $prospectContact->prospect_id)
                ->update(['is_primary' => false]);

            $prospectContact->update(['is_primary' => true]);
        });

        return redirect()
            ->route('prospects.show', $prospectContact->prospect_id)
            ->with('success', 'Primary contact updated.');
    }

    public function destroy(ProspectContact $prospectContact)
    {
        $prospectId = $prospectContact->prospect_id;
        $wasPrimary = $prospectContact->is_primary;

        DB::transaction(function () use ($prospectContact, $prospectId, $wasPrimary) {
            $prospectContact->delete();

            if ($wasPrimary) {
                $nextContact = ProspectContact::query()
                    ->where('prospect_id', $prospectId)
                    ->where('status', ProspectContact::STATUS_ACTIVE)
                    ->latest()
                    ->first();

                if ($nextContact) {
                    $nextContact->update(['is_primary' => true]);
                }
            }
        });

        return redirect()
            ->route('prospects.show', $prospectId)
            ->with('success', 'Contact deleted.');
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function cleanLower(?string $value): ?string
    {
        $value = $this->clean($value);

        return $value ? strtolower($value) : null;
    }
}