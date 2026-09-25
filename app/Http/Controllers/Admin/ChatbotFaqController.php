<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatbotFaqRequest;
use App\Models\Category;
use App\Models\ChatbotFaq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatbotFaqController extends Controller
{
    public function index(Request $request): View
    {
        $query = ChatbotFaq::query()->with(['category', 'createdBy']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $faqs = $query->latest()->paginate(25)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $stats = [
            'total' => ChatbotFaq::count(),
            'this_month' => ChatbotFaq::where('created_at', '>=', now()->startOfMonth())->count(),
            'categorized' => ChatbotFaq::whereNotNull('category_id')->distinct()->count('category_id'),
            'uncategorized' => ChatbotFaq::whereNull('category_id')->count(),
        ];

        return view('admin.chatbot.faqs.index', compact('faqs', 'categories', 'stats'));
    }

    public function create(): View
    {
        return view('admin.chatbot.faqs.create', $this->formData());
    }

    public function store(ChatbotFaqRequest $request): RedirectResponse
    {
        ChatbotFaq::create([
            ...$request->validated(),
            'created_by' => $request->user()?->id,
        ]);

        return redirect()->route('admin.chatbot.faqs.index')
            ->with('success', 'FAQ created successfully.');
    }

    public function show(ChatbotFaq $chatbotFaq): View
    {
        return view('admin.chatbot.faqs.show', [
            'faq' => $chatbotFaq->load(['category', 'createdBy']),
        ]);
    }

    public function edit(ChatbotFaq $chatbotFaq): View
    {
        return view('admin.chatbot.faqs.edit', array_merge($this->formData(), [
            'faq' => $chatbotFaq,
        ]));
    }

    public function update(ChatbotFaqRequest $request, ChatbotFaq $chatbotFaq): RedirectResponse
    {
        $chatbotFaq->update($request->validated());

        return redirect()->route('admin.chatbot.faqs.index')
            ->with('success', 'FAQ updated successfully.');
    }

    public function destroy(ChatbotFaq $chatbotFaq): RedirectResponse
    {
        $chatbotFaq->delete();

        return redirect()->route('admin.chatbot.faqs.index')
            ->with('success', 'FAQ deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ];
    }
}
