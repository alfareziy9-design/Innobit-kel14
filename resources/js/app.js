import './bootstrap';

import tinymce from 'tinymce/tinymce';
import 'tinymce/models/dom';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/code';
import 'tinymce/plugins/image';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/wordcount';
import 'tinymce/skins/ui/oxide/skin.css';
import 'tinymce/skins/content/default/content.css';
import 'tinymce/skins/ui/oxide/content.css';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

function initArticleEditors() {
    document.querySelectorAll('textarea[data-rich-editor]').forEach((textarea) => {
        tinymce.init({
            target: textarea,
            height: 520,
            menubar: false,
            branding: false,
            promotion: false,
            license_key: 'gpl',
            plugins: 'advlist autolink code image link lists table wordcount',
            toolbar: [
                'undo redo | blocks | bold italic underline | alignleft aligncenter alignright',
                'bullist numlist blockquote | link image table | removeformat code',
            ].join(' | '),
            block_formats: 'Paragraf=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
            image_title: true,
            image_caption: true,
            image_advtab: true,
            image_class_list: [
                { title: 'Full width', value: 'article-media article-media--full' },
                { title: 'Center', value: 'article-media article-media--center' },
                { title: 'Left', value: 'article-media article-media--left' },
                { title: 'Right', value: 'article-media article-media--right' },
            ],
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
                const uploadUrl = textarea.dataset.mediaUploadUrl;
                const formData = new FormData();
                formData.append('media', blobInfo.blob(), blobInfo.filename());

                fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                })
                    .then((response) => response.json().then((json) => ({ response, json })))
                    .then(({ response, json }) => {
                        if (!response.ok || !json.location) {
                            reject(json.message || 'Upload media gagal.');
                            return;
                        }

                        resolve(json.location);
                    })
                    .catch(() => reject('Upload media gagal.'));
            }),
            setup: (editor) => {
                editor.on('init', () => {
                    const form = editor.getElement()?.form;

                    if (!form || form.dataset.richEditorSubmitBound === 'true') {
                        return;
                    }

                    form.dataset.richEditorSubmitBound = 'true';
                    form.addEventListener('submit', async (event) => {
                        if (form.dataset.richEditorSubmitting === 'true') {
                            return;
                        }

                        event.preventDefault();

                        const formEditors = tinymce.get().filter((activeEditor) => activeEditor.getElement()?.form === form);

                        try {
                            await Promise.all(formEditors.map((activeEditor) => activeEditor.uploadImages()));
                            formEditors.forEach((activeEditor) => activeEditor.save());
                            form.dataset.richEditorSubmitting = 'true';
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit();
                            } else {
                                form.submit();
                            }
                        } catch (error) {
                            delete form.dataset.richEditorSubmitting;
                            alert(typeof error === 'string' ? error : 'Upload media artikel belum selesai. Coba simpan lagi.');
                        }
                    });
                });
            },
            content_style: `
                body { color: #1e293b; font-family: ui-sans-serif, system-ui, sans-serif; font-size: 17px; line-height: 1.75; }
                img { max-width: 100%; height: auto; }
                figure { margin: 1.5rem 0; }
                figcaption { color: #64748b; font-size: 13px; text-align: center; }
                .article-media--center { display: block; margin-left: auto; margin-right: auto; }
                .article-media--full { display: block; width: 100%; }
                .article-media--left { float: left; max-width: 45%; margin: 0.35rem 1.25rem 1rem 0; }
                .article-media--right { float: right; max-width: 45%; margin: 0.35rem 0 1rem 1.25rem; }
            `,
        });
    });
}

document.addEventListener('DOMContentLoaded', initArticleEditors);
