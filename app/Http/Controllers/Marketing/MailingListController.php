<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MailingList;
use App\Models\MailingListSubscriber;
use App\Models\Lead;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MailingListController extends Controller
{
    public function index()
    {
        $lists = MailingList::withCount(['subscribers', 'subscribers as active_subscribers_count' => function ($q) {
            $q->where('status', 'subscribed');
        }])->latest()->get();

        return Inertia::render('Marketing/Lists/Index', [
            'lists' => $lists,
        ]);
    }

    public function create()
    {
        return Inertia::render('Marketing/Lists/Create', [
            'leadStages' => \App\Models\LeadPipelineStage::ordered()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_dynamic' => 'nullable|boolean',
            'filter_rules' => 'nullable|array',
        ]);

        $list = MailingList::create($validated);

        return redirect()->route('marketing.lists.show', $list->id)
            ->with('success', 'Mailing list created successfully.');
    }

    public function show($id)
    {
        $list = MailingList::withCount('subscribers')->findOrFail($id);
        $subscribers = $list->subscribers()->latest()->paginate(50);

        return Inertia::render('Marketing/Lists/Show', [
            'list' => $list,
            'subscribers' => $subscribers,
            'availableLeads' => Lead::whereNotNull('contact_email')
                ->select('id', 'title', 'contact_name', 'contact_email', 'company')
                ->orderBy('title')
                ->get(),
            'availableClients' => Client::whereNotNull('email')
                ->select('id', 'name', 'email', 'company')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit($id)
    {
        $list = MailingList::findOrFail($id);

        return Inertia::render('Marketing/Lists/Edit', [
            'list' => $list,
            'leadStages' => \App\Models\LeadPipelineStage::ordered()->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $list = MailingList::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_dynamic' => 'nullable|boolean',
            'filter_rules' => 'nullable|array',
        ]);

        $list->update($validated);

        return redirect()->route('marketing.lists.show', $list->id)
            ->with('success', 'Mailing list updated successfully.');
    }

    public function destroy($id)
    {
        $list = MailingList::findOrFail($id);
        $list->delete();

        return redirect()->route('marketing.lists.index')
            ->with('success', 'Mailing list deleted successfully.');
    }

    /**
     * Sync subscribers from Leads/Clients based on conditions.
     */
    public function syncSubscribers(Request $request, $id)
    {
        $list = MailingList::findOrFail($id);

        $validated = $request->validate([
            'source_type' => 'required|in:leads,clients,both',
            'lead_stage_id' => 'nullable|exists:lead_pipeline_stages,id',
        ]);

        $added = 0;

        // Sync Leads
        if (in_array($validated['source_type'], ['leads', 'both'])) {
            $leadsQuery = Lead::whereNotNull('contact_email');
            if (!empty($validated['lead_stage_id'])) {
                $leadsQuery->where('lead_pipeline_stage_id', $validated['lead_stage_id']);
            }

            foreach ($leadsQuery->get() as $lead) {
                $exists = MailingListSubscriber::where('mailing_list_id', $list->id)
                    ->where('subscribable_type', Lead::class)
                    ->where('subscribable_id', $lead->id)
                    ->exists();

                if (!$exists) {
                    MailingListSubscriber::create([
                        'mailing_list_id' => $list->id,
                        'subscribable_type' => Lead::class,
                        'subscribable_id' => $lead->id,
                        'email' => $lead->contact_email,
                        'name' => $lead->contact_name ?? $lead->title,
                        'status' => 'subscribed',
                    ]);
                    $added++;
                }
            }
        }

        // Sync Clients
        if (in_array($validated['source_type'], ['clients', 'both'])) {
            foreach (Client::whereNotNull('email')->get() as $client) {
                $exists = MailingListSubscriber::where('mailing_list_id', $list->id)
                    ->where('subscribable_type', Client::class)
                    ->where('subscribable_id', $client->id)
                    ->exists();

                if (!$exists) {
                    MailingListSubscriber::create([
                        'mailing_list_id' => $list->id,
                        'subscribable_type' => Client::class,
                        'subscribable_id' => $client->id,
                        'email' => $client->email,
                        'name' => $client->name,
                        'status' => 'subscribed',
                    ]);
                    $added++;
                }
            }
        }

        return redirect()->back()->with('success', "{$added} subscribers synced successfully.");
    }

    /**
     * Import subscribers from CSV.
     */
    public function importSubscribers(Request $request, $id)
    {
        $list = MailingList::findOrFail($id);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        fgetcsv($handle); // skip header

        $added = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 1 || !filter_var($row[0] ?? '', FILTER_VALIDATE_EMAIL)) continue;

            $exists = MailingListSubscriber::where('mailing_list_id', $list->id)
                ->where('email', $row[0])
                ->exists();

            if (!$exists) {
                MailingListSubscriber::create([
                    'mailing_list_id' => $list->id,
                    'subscribable_type' => 'manual',
                    'subscribable_id' => \Illuminate\Support\Str::uuid(),
                    'email' => $row[0],
                    'name' => $row[1] ?? null,
                    'status' => 'subscribed',
                ]);
                $added++;
            }
        }
        fclose($handle);

        return redirect()->back()->with('success', "{$added} subscribers imported successfully.");
    }

    /**
     * Remove a subscriber from a list.
     */
    public function removeSubscriber($listId, $subscriberId)
    {
        $subscriber = MailingListSubscriber::where('mailing_list_id', $listId)
            ->where('id', $subscriberId)
            ->firstOrFail();

        $subscriber->delete();

        return redirect()->back()->with('success', 'Subscriber removed successfully.');
    }
}
