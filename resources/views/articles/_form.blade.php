@csrf
@if (isset($formHttpMethod))
    @method($formHttpMethod)
@elseif (isset($article))
    @if (auth()->user()?->isAdmin() || $article->status !== 'published')
        @method('PUT')
    @endif
@endif

@php
    $storedQuizzes = [];
    $formSource = $editorSource ?? $revisionDraft ?? $article ?? null;

    if ($formSource instanceof \App\Models\ArticleRevision && $formSource->quiz_data) {
        $storedQuizzes = $formSource->quiz_data;
    } elseif (isset($article) && $article->normalizedQuiz) {
        $article->normalizedQuiz->loadMissing('questions.options');

        $storedQuizzes = $article->normalizedQuiz->questions
            ->take(3)
            ->map(function ($question) {
                $options = $question->options->values();
                $correctOption = $options->search(fn ($option) => $option->is_correct);

                return [
                    'question' => $question->question,
                    'options' => $options->pluck('option_text')->all(),
                    'correct_option' => $correctOption === false ? 0 : $correctOption,
                ];
            })
            ->values()
            ->all();
    }

    $legacyQuiz = isset($article) && $article->quiz ? [$article->quiz] : [];
    $quizzes = old('quizzes', $storedQuizzes ?: $legacyQuiz ?: [[
            'question' => '',
            'options' => ['', '', ''],
            'correct_option' => 0,
        ]]);

    $quizzes = collect($quizzes)
        ->take(3)
        ->map(fn ($quiz) => [
            'question' => $quiz['question'] ?? '',
            'options' => array_pad($quiz['options'] ?? [], 3, ''),
            'correct_option' => (int) ($quiz['correct_option'] ?? 0),
        ])
        ->values()
        ->all();

    $visibleQuizCount = max(1, count($quizzes));
@endphp

<div>
    @isset($article)
        <label class="block mb-2 font-medium">Thumbnail Saat Ini</label>
    @else
        <label class="block mb-2 font-medium">Preview Thumbnail</label>
    @endisset
        <img
            src="{{ $formSource?->thumbnailMedia?->url ?? '' }}"
            alt="{{ $formSource?->title ?? 'Preview thumbnail artikel' }}"
            class="{{ $formSource?->thumbnailMedia ? '' : 'hidden' }} mb-3 aspect-video w-full max-w-md rounded-xl border border-slate-200 bg-slate-100 object-cover"
            data-thumbnail-preview
        >
        <p class="{{ $formSource?->thumbnailMedia ? 'hidden' : '' }} mb-3 text-slate-500" data-thumbnail-empty>Belum ada thumbnail.</p>
        <p class="hidden mb-3 text-sm font-semibold text-rose-600" data-thumbnail-error>Thumbnail tidak dapat ditampilkan. Pilih file lain.</p>
</div>

<div>
    <label for="thumbnail" class="block mb-2 font-medium">{{ isset($article) ? 'Ganti Thumbnail' : 'Thumbnail' }}</label>
    <input type="file" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.webp" class="w-full border rounded-xl px-4 py-3 bg-white" data-thumbnail-input>
    <p class="text-sm text-slate-500 mt-2">Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</p>
</div>

<div>
    <label for="title" class="block mb-2 font-medium">Judul Artikel</label>
    <input type="text" id="title" name="title" value="{{ old('title', $formSource->title ?? '') }}" class="w-full border rounded-xl px-4 py-3">
</div>

<div>
    <label for="category_id" class="block mb-2 font-medium">Kategori</label>
    <select id="category_id" name="category_id" class="w-full border rounded-xl px-4 py-3">
        <option value="">Pilih kategori</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $formSource->category_id ?? '') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
</div>

<div>
    <label for="summary" class="block mb-2 font-medium">Ringkasan</label>
    <textarea id="summary" name="summary" rows="3" required maxlength="1000" class="w-full border rounded-xl px-4 py-3">{{ old('summary', $formSource->summary ?? '') }}</textarea>
</div>

<div>
    <label for="content" class="block mb-2 font-medium">Isi Artikel</label>
    <textarea
        id="content"
        name="content"
        rows="12"
        data-summernote-editor
        data-media-upload-url="{{ route('articles.media.store') }}"
        class="w-full border rounded-xl px-4 py-3"
    >{{ old('content', $formSource->content ?? '') }}</textarea>
    <p class="text-sm text-slate-500 mt-2">Isi artikel mendukung format teks, tabel, tautan, serta upload foto/GIF.</p>
    <p class="mt-2 hidden text-sm font-semibold text-sky-700" data-editor-upload-status></p>
    <p class="mt-2 hidden text-sm font-semibold text-rose-600" data-editor-error></p>
</div>

<div class="rounded-xl border border-lime-200 bg-lime-50/60 p-4" data-quiz-builder>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-bold text-slate-900">Quiz Artikel</h2>
            <p class="mt-1 text-sm text-slate-500">Wajib minimal 1 quiz dan maksimal 3 quiz. Setiap quiz memiliki 3 pilihan jawaban.</p>
        </div>
        <button type="button" data-add-quiz class="rounded-xl bg-lime-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-lime-700">
            + Tambah Quiz
        </button>
    </div>

    <div class="space-y-4" data-quiz-list>
        @for ($quizIndex = 0; $quizIndex < 3; $quizIndex++)
            @php
                $quiz = $quizzes[$quizIndex] ?? [
                    'question' => '',
                    'options' => ['', '', ''],
                    'correct_option' => 0,
                ];
                $quizOptions = array_pad($quiz['options'] ?? [], 3, '');
                $isVisible = $quizIndex < $visibleQuizCount;
            @endphp

            <section class="{{ $isVisible ? '' : 'hidden' }} rounded-xl border border-lime-200 bg-white p-4 shadow-sm" data-quiz-item data-quiz-index="{{ $quizIndex }}">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="font-bold text-slate-900">Quiz {{ $quizIndex + 1 }}</h3>
                    @if ($quizIndex > 0)
                        <button type="button" data-remove-quiz class="text-sm font-bold text-red-600 transition hover:text-red-700">Hapus</button>
                    @endif
                </div>

                <div class="mb-4">
                    <label for="quiz_{{ $quizIndex }}_question" class="block mb-2 font-medium">Pertanyaan Quiz</label>
                    <input type="text" id="quiz_{{ $quizIndex }}_question" name="quizzes[{{ $quizIndex }}][question]" value="{{ $quiz['question'] ?? '' }}" class="w-full border rounded-xl px-4 py-3 bg-white" @disabled(! $isVisible)>
                </div>

                <div class="space-y-3">
                    <p class="font-medium">Pilihan Jawaban</p>
                    @for ($index = 0; $index < 3; $index++)
                        <div class="flex flex-col gap-2 rounded-xl border bg-white p-3 md:flex-row md:items-center">
                            <label for="quiz_{{ $quizIndex }}_option_{{ $index }}" class="text-sm font-medium text-slate-600 md:w-24">Pilihan {{ $index + 1 }}</label>
                            <input type="text" id="quiz_{{ $quizIndex }}_option_{{ $index }}" name="quizzes[{{ $quizIndex }}][options][{{ $index }}]" value="{{ $quizOptions[$index] }}" class="min-w-0 flex-1 border rounded-xl px-4 py-3" @disabled(! $isVisible)>
                            <label class="inline-flex items-center gap-2 text-sm font-semibold text-lime-800">
                                <input type="radio" name="quizzes[{{ $quizIndex }}][correct_option]" value="{{ $index }}" @checked(($quiz['correct_option'] ?? 0) === $index) class="h-4 w-4 border-slate-300 text-lime-700 focus:ring-lime-600" @disabled(! $isVisible)>
                                Jawaban benar
                            </label>
                        </div>
                    @endfor
                </div>
            </section>
        @endfor
    </div>
</div>

<script>
    document.querySelectorAll('[data-quiz-builder]').forEach((builder) => {
        const items = Array.from(builder.querySelectorAll('[data-quiz-item]'));
        const addButton = builder.querySelector('[data-add-quiz]');

        const setItemEnabled = (item, enabled) => {
            item.classList.toggle('hidden', !enabled);
            item.querySelectorAll('input').forEach((input) => {
                input.disabled = !enabled;
            });
        };

        const refreshAddButton = () => {
            addButton.disabled = items.filter((item) => !item.classList.contains('hidden')).length >= 3;
            addButton.classList.toggle('cursor-not-allowed', addButton.disabled);
            addButton.classList.toggle('opacity-60', addButton.disabled);
        };

        addButton.addEventListener('click', () => {
            const nextItem = items.find((item) => item.classList.contains('hidden'));

            if (nextItem) {
                setItemEnabled(nextItem, true);
                refreshAddButton();
            }
        });

        builder.querySelectorAll('[data-remove-quiz]').forEach((button) => {
            button.addEventListener('click', () => {
                const item = button.closest('[data-quiz-item]');

                item.querySelectorAll('input[type="text"]').forEach((input) => {
                    input.value = '';
                });
                item.querySelector('input[type="radio"][value="0"]').checked = true;
                setItemEnabled(item, false);
                refreshAddButton();
            });
        });

        refreshAddButton();
    });
</script>

@if ($adminReviewMode ?? false)
    <div class="rounded-lg border border-violet-200 bg-violet-50 p-4 text-sm leading-6 text-violet-900">
        Naskah tetap berstatus <strong>Menunggu Review</strong> setelah disimpan. Gunakan halaman review untuk menyetujui atau mengembalikannya kepada author.
    </div>
@elseif (auth()->user()?->isAdmin())
    @if (isset($article) && $article->status === 'review')
        <input type="hidden" name="status" value="review">
        <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm leading-6 text-sky-800">
            Artikel admin ini sedang berstatus Menunggu Review. Artikel milik author harus diputuskan dari antrean editorial tanpa mengubah isinya.
        </div>
    @else
        <div>
            <label for="status" class="block mb-2 font-medium">Status</label>
            <select id="status" name="status" class="w-full border rounded-xl px-4 py-3">
                <option value="published" @selected(old('status', $article->status ?? 'published') === 'published')>Published</option>
                <option value="draft" @selected(old('status', $article->status ?? '') === 'draft')>Draft</option>
            </select>
        </div>
    @endif
@else
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800">
        Artikel disimpan sebagai <strong>Draft</strong>. Kirim artikel untuk review dari dashboard saat sudah siap.
        @isset($article)
            @if ($article->status === 'published')
                Mengedit artikel Terbit akan membuat pembaruan baru untuk direview admin. Versi Terbit tetap tayang sampai pembaruan disetujui.
                @if (isset($revisionDraft) && $revisionDraft->status === 'rejected' && $revisionDraft->review_note)
                    <span class="mt-2 block"><strong>Alasan revisi terakhir:</strong> {{ $revisionDraft->review_note }}</span>
                @elseif (isset($revisionDraft) && $revisionDraft->status === 'review')
                    <span class="mt-2 block"><strong>Status:</strong> Revisi ini sedang menunggu review admin.</span>
                @endif
            @elseif ($article->status === 'rejected' && $article->latestReview?->note)
                <span class="mt-2 block"><strong>Alasan revisi:</strong> {{ $article->latestReview->note }}</span>
            @endif
        @endisset
    </div>
@endif

<div class="flex flex-wrap gap-3">
    <button type="submit" class="bg-lime-600 text-white px-5 py-3 rounded-xl hover:bg-lime-700">{{ $submitLabel ?? (isset($article) ? 'Update Artikel' : (auth()->user()?->isAdmin() ? 'Simpan Artikel' : 'Simpan Draft')) }}</button>
    <a href="{{ $cancelUrl ?? route(auth()->user()?->isAdmin() ? 'admin.dashboard' : 'author.dashboard') }}" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl hover:bg-slate-300">Kembali</a>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-lite.min.css">
        <style>
            .note-editor.note-frame {
                border-color: #cbd5e1;
                border-radius: 0.75rem;
                overflow: hidden;
            }

            .note-editor .note-toolbar {
                background: #f8fafc;
                border-bottom-color: #e2e8f0;
            }

            .note-editor .note-editable {
                color: #1e293b;
                font-family: ui-sans-serif, system-ui, sans-serif;
                font-size: 17px;
                line-height: 1.75;
                min-height: 420px;
            }

            .note-editor .note-editable img {
                height: auto;
                max-width: 100%;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-lite.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

                document.querySelectorAll('textarea[data-summernote-editor]').forEach((textarea) => {
                    const form = textarea.form;
                    const uploadStatus = form?.querySelector('[data-editor-upload-status]');
                    const errorElement = form?.querySelector('[data-editor-error]');
                    const pendingUploads = new Set();
                    const $editor = window.jQuery?.(textarea);

                    if (!window.jQuery || !window.jQuery.fn.summernote || !$editor) {
                        textarea.classList.remove('hidden');
                        errorElement?.classList.remove('hidden');
                        if (errorElement) {
                            errorElement.textContent = 'Editor visual gagal dimuat. Isi artikel tetap dapat ditulis melalui kolom teks.';
                        }
                        return;
                    }

                    const setUploadStatus = () => {
                        if (!uploadStatus) {
                            return;
                        }

                        uploadStatus.classList.toggle('hidden', pendingUploads.size === 0);
                        uploadStatus.textContent = pendingUploads.size > 0
                            ? `${pendingUploads.size} gambar sedang diunggah...`
                            : '';
                    };

                    const uploadImage = (file) => {
                        if (!file.type.startsWith('image/')) {
                            return Promise.reject(new Error('File harus berupa gambar.'));
                        }

                        const formData = new FormData();
                        formData.append('media', file, file.name || 'gambar-artikel');

                        const upload = fetch(textarea.dataset.mediaUploadUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                        })
                            .then(async (response) => {
                                const data = await response.json();

                                if (!response.ok || !data.location) {
                                    throw new Error(data.errors?.media?.[0] || data.message || 'Upload gambar gagal.');
                                }

                                const image = document.createElement('img');
                                image.src = data.location;
                                image.alt = file.name || 'Gambar artikel';
                                image.className = 'article-media article-media--center';
                                image.loading = 'lazy';
                                $editor.summernote('insertNode', image);
                            })
                            .catch((error) => {
                                errorElement?.classList.remove('hidden');
                                if (errorElement) {
                                    errorElement.textContent = error.message || 'Upload gambar gagal.';
                                }
                                throw error;
                            })
                            .finally(() => {
                                pendingUploads.delete(upload);
                                setUploadStatus();
                            });

                        pendingUploads.add(upload);
                        setUploadStatus();

                        return upload;
                    };

                    $editor.summernote({
                        height: 520,
                        minHeight: 420,
                        placeholder: 'Tulis isi artikel di sini...',
                        dialogsInBody: true,
                        disableDragAndDrop: false,
                        codeviewFilter: true,
                        codeviewIframeFilter: true,
                        styleTags: ['p', 'blockquote', 'pre', 'h2', 'h3', 'h4'],
                        toolbar: [
                            ['history', ['undo', 'redo']],
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                            ['fontname', ['fontname']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['height', ['height']],
                            ['insert', ['link', 'picture', 'table', 'hr']],
                            ['view', ['fullscreen', 'codeview', 'help']],
                        ],
                        popover: {
                            image: [
                                ['resize', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                                ['float', ['floatLeft', 'floatRight', 'floatNone']],
                                ['remove', ['removeMedia']],
                            ],
                            link: [
                                ['link', ['linkDialogShow', 'unlink']],
                            ],
                            table: [
                                ['add', ['addRowDown', 'addRowUp', 'addColLeft', 'addColRight']],
                                ['delete', ['deleteRow', 'deleteCol', 'deleteTable']],
                            ],
                        },
                        callbacks: {
                            onImageUpload(files) {
                                errorElement?.classList.add('hidden');
                                Array.from(files).forEach((file) => uploadImage(file).catch(() => {}));
                            },
                            onImageLinkInsert() {
                                errorElement?.classList.remove('hidden');
                                if (errorElement) {
                                    errorElement.textContent = 'Gambar dari URL eksternal tidak didukung. Unggah gambar dari perangkat.';
                                }
                            },
                        },
                    });

                    if (!form || form.dataset.summernoteSubmitBound === 'true') {
                        return;
                    }

                    form.dataset.summernoteSubmitBound = 'true';
                    form.addEventListener('submit', async (event) => {
                        if (form.dataset.summernoteSubmitting === 'true') {
                            return;
                        }

                        event.preventDefault();

                        try {
                            await Promise.all(Array.from(pendingUploads));

                            const editors = Array.from(form.querySelectorAll('textarea[data-summernote-editor]'));
                            for (const editor of editors) {
                                const $formEditor = window.jQuery(editor);
                                const html = $formEditor.summernote('code');

                                if (/<img[^>]+src=["']data:/i.test(html)) {
                                    throw new Error('Gambar harus selesai diunggah sebelum artikel disimpan.');
                                }

                                editor.value = $formEditor.summernote('isEmpty') ? '' : html;
                            }

                            form.dataset.summernoteSubmitting = 'true';
                            form.requestSubmit();
                        } catch (error) {
                            delete form.dataset.summernoteSubmitting;
                            errorElement?.classList.remove('hidden');
                            if (errorElement) {
                                errorElement.textContent = error.message || 'Artikel belum dapat disimpan.';
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce
