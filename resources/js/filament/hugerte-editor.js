import hugerte from 'hugerte';
import 'hugerte/models/dom';
import 'hugerte/themes/silver';
import 'hugerte/icons/default';
import 'hugerte/skins/ui/oxide/skin.js';
import 'hugerte/skins/ui/oxide/content.js';
import 'hugerte/skins/content/default/content.js';
import 'hugerte/plugins/lists';
import 'hugerte/plugins/link';
import 'hugerte/plugins/table';
import 'hugerte/plugins/code';
import 'hugerte/plugins/autoresize';
import 'hugerte/plugins/wordcount';

export default function hugerteEditor({ state, height, readOnly }) {
    return {
        state,
        editor: null,

        init() {
            const textarea = this.$refs.editor;
            textarea.value = this.state ?? '';

            hugerte.init({
                target: textarea,
                min_height: height,
                max_height: 900,
                readonly: readOnly,
                menubar: false,
                branding: false,
                promotion: false,
                entity_encoding: 'raw',
                plugins: 'lists link table code autoresize wordcount',
                toolbar:
                    'undo redo | blocks | bold italic underline | bullist numlist | link table | removeformat | code',
                block_formats: 'Parágrafo=p; Título 2=h2; Título 3=h3',
                link_default_protocol: 'https',
                convert_urls: false,
                setup: (editor) => {
                    this.editor = editor;

                    editor.on('change input undo redo blur', () => {
                        const html = editor.getContent();

                        if (html !== (this.state ?? '')) {
                            this.state = html;
                        }
                    });
                },
            });

            this.$watch('state', (value) => {
                if (this.editor && value !== this.editor.getContent()) {
                    this.editor.setContent(value ?? '');
                }
            });
        },

        destroy() {
            this.editor?.remove();
            this.editor = null;
        },
    };
}
