<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailTemplate::with('creator')->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%");
            });
        }

        $templates = $query->get();
        $categories = EmailTemplate::distinct()->whereNotNull('category')->pluck('category');

        return Inertia::render('Marketing/Templates/Index', [
            'templates' => $templates,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Marketing/Templates/Editor', [
            'template' => null,
            'categories' => EmailTemplate::distinct()->whereNotNull('category')->pluck('category'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'editor_type' => 'required|in:richtext,dragdrop',
            'html_content' => 'nullable|string',
            'design_json' => 'nullable|string',
            'category' => 'nullable|string|max:100',
        ]);

        $validated['created_by'] = auth()->id();

        $template = EmailTemplate::create($validated);

        return redirect()->route('marketing.templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function show($id)
    {
        $template = EmailTemplate::with('creator')->findOrFail($id);

        return Inertia::render('Marketing/Templates/Editor', [
            'template' => $template,
            'categories' => EmailTemplate::distinct()->whereNotNull('category')->pluck('category'),
        ]);
    }

    public function edit($id)
    {
        $template = EmailTemplate::with('creator')->findOrFail($id);

        return Inertia::render('Marketing/Templates/Editor', [
            'template' => $template,
            'categories' => EmailTemplate::distinct()->whereNotNull('category')->pluck('category'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'editor_type' => 'required|in:richtext,dragdrop',
            'html_content' => 'nullable|string',
            'design_json' => 'nullable|string',
            'category' => 'nullable|string|max:100',
        ]);

        $template->update($validated);

        return redirect()->route('marketing.templates.index')
            ->with('success', 'Template updated successfully.');
    }

    public function destroy($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('marketing.templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    public function duplicate($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->created_by = auth()->id();
        $newTemplate->save();

        return redirect()->route('marketing.templates.edit', $newTemplate->id)
            ->with('success', 'Template duplicated successfully.');
    }

    public function preview($id)
    {
        $template = EmailTemplate::findOrFail($id);
        return response($template->html_content ?? '<p>No content</p>');
    }
}
