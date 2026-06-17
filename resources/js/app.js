import './bootstrap';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

function initThumbnailPreviews() {
    document.querySelectorAll('[data-thumbnail-input]').forEach((input) => {
        const form = input.closest('form');
        const preview = form?.querySelector('[data-thumbnail-preview]');
        const empty = form?.querySelector('[data-thumbnail-empty]');
        const error = form?.querySelector('[data-thumbnail-error]');
        let objectUrl = null;

        if (!preview) {
            return;
        }

        preview.addEventListener('error', () => {
            preview.classList.add('hidden');
            error?.classList.remove('hidden');
        });

        preview.addEventListener('load', () => {
            preview.classList.remove('hidden');
            empty?.classList.add('hidden');
            error?.classList.add('hidden');
        });

        input.addEventListener('change', () => {
            const [file] = input.files;

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            if (!file) {
                return;
            }

            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.alt = `Preview ${file.name}`;
        });
    });
}

function initChats() {
    document.querySelectorAll('[data-chat]').forEach((chat) => {
        const messagesContainer = chat.querySelector('[data-chat-messages]');
        const form = chat.querySelector('[data-chat-form]');
        const errorElement = chat.querySelector('[data-chat-error]');
        const submitButton = chat.querySelector('[data-chat-submit]');
        const viewer = chat.dataset.viewer;
        const updatesUrl = chat.dataset.updatesUrl;

        if (!messagesContainer || !updatesUrl) {
            return;
        }

        const lastMessageId = () => Math.max(
            0,
            ...Array.from(messagesContainer.querySelectorAll('[data-message-id]'))
                .map((element) => Number(element.dataset.messageId) || 0),
        );

        const appendMessage = (message) => {
            if (messagesContainer.querySelector(`[data-message-id="${message.id}"]`)) {
                return;
            }

            const isOwn = message.sender_type === viewer;
            const article = document.createElement('article');
            const bubble = document.createElement('div');
            const sender = document.createElement('p');
            const body = document.createElement('p');
            const time = document.createElement('time');

            article.dataset.messageId = message.id;
            article.className = `flex ${isOwn ? 'justify-end' : 'justify-start'}`;
            bubble.className = `max-w-[85%] rounded-2xl px-4 py-3 ${isOwn ? 'bg-slate-950 text-white' : 'border border-slate-200 bg-white text-slate-700'}`;
            sender.className = `text-xs font-bold ${isOwn ? 'text-lime-300' : 'text-lime-700'}`;
            body.className = 'mt-1 whitespace-pre-wrap break-words text-sm leading-6';
            time.className = `mt-2 block text-[11px] ${isOwn ? 'text-white/55' : 'text-slate-400'}`;

            sender.textContent = message.sender_name;
            body.textContent = message.message;
            time.textContent = message.created_at;

            bubble.append(sender, body, time);
            article.append(bubble);
            messagesContainer.append(article);
        };

        const scrollToLatest = () => {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        };

        const poll = async () => {
            if (document.hidden) {
                return;
            }

            try {
                const url = new URL(updatesUrl, window.location.origin);
                url.searchParams.set('after_id', lastMessageId());
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                if (data.messages?.length) {
                    data.messages.forEach(appendMessage);
                    scrollToLatest();
                }
            } catch {
                // The next polling cycle retries transient network failures.
            }
        };

        if (form) {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                errorElement.textContent = '';
                submitButton.disabled = true;
                submitButton.classList.add('opacity-60');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        errorElement.textContent = data.errors?.message?.[0] || data.message || 'Pesan gagal dikirim.';
                        return;
                    }

                    appendMessage(data.message);
                    form.reset();
                    scrollToLatest();
                } catch {
                    errorElement.textContent = 'Pesan gagal dikirim. Periksa koneksi lalu coba lagi.';
                } finally {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-60');
                }
            });
        }

        scrollToLatest();
        window.setInterval(poll, 5000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                poll();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initThumbnailPreviews();
    initChats();
});
