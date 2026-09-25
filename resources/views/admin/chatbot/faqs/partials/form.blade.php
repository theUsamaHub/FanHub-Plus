<div class="row">
    <div class="col-lg-8">
        <form action="{{ $action }}" method="POST">
            @csrf
            @method($method)

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('FAQ Content') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <x-input-label for="question" :value="__('Question')" />
                        <x-text-input id="question" name="question" type="text" class="form-control" :value="old('question', $faq?->question)" maxlength="500" required autofocus />
                        <div class="form-text">{{ __('Use the wording visitors are most likely to type.') }}</div>
                        <x-input-error :messages="$errors->get('question')" class="mt-1" />
                    </div>

                    <div class="mb-3">
                        <x-input-label for="category_id" :value="__('Category')" />
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">{{ __('Uncategorized') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $faq?->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="answer" :value="__('Answer')" />
                        <textarea id="answer" name="answer" rows="10" class="form-control @error('answer') is-invalid @enderror" maxlength="50000" required>{{ old('answer', $faq?->answer) }}</textarea>
                        <div class="form-text">{{ __('Plain text and simple Markdown formatting are supported.') }}</div>
                        <x-input-error :messages="$errors->get('answer')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.chatbot.faqs.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                <x-primary-button>{{ $faq ? __('Update FAQ') : __('Create FAQ') }}</x-primary-button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card fh-adm-detail-card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('How Matching Works') }}</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0" style="font-size:0.875rem;">
                    <li class="mb-2">{{ __('An exact question match returns this answer directly.') }}</li>
                    <li class="mb-2">{{ __('The first six FAQs appear as suggested questions.') }}</li>
                    <li class="mb-0">{{ __('All FAQs provide reference context to the AI fallback.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
