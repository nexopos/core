<script lang="ts" setup>
import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import Quote from '@editorjs/quote';
import Warning from '@editorjs/warning';
import Paragraph from '@editorjs/paragraph';
import Delimiter from '@editorjs/delimiter';
import EditorjsList from '@editorjs/list';
import { onMounted, ref } from 'vue';
import { __ } from '~/libraries/lang';
import { default as nsMedia } from '~/pages/dashboard/ns-media.vue';
import NsPosLoadingPopup from '~/popups/ns-pos-loading-popup.vue';

declare const Popup;

class Media {
    private wrapper: HTMLElement;
    private imageWrapper: HTMLElement;
    private buttonWrapper: HTMLElement;
    private image: HTMLImageElement;
    private choose: HTMLButtonElement;

    private __align: string = 'left';

    static get toolbox() {
        return {
            title: __('Media'),
        }
    }

    get align() {
        return this.__align;
    }

    set align( value: string ) {
        this.__align = value;
        this.imageWrapper.classList.remove('align-left', 'align-center', 'align-right');
        this.imageWrapper.classList.add(`align-${value}`);
    }

    renderSettings() {
        const settings = [
            {
                name: __( 'Align Left' ),
                value: 'left',
                icon: '<i class="las la-align-left text-lg"></i>',
            },
            {
                name: __( 'Align Center' ),
                value: 'center',
                icon: '<i class="las la-align-center text-lg"></i>',
            },
            {
                name: __( 'Align Right' ),
                value: 'right',
                icon: '<i class="las la-align-right text-lg"></i>',
            },
        ];

        const wrapper = document.createElement('div');

        settings.forEach( tune => {
            let button = document.createElement('div');

            button.classList.add('cdx-settings-button');
            button.innerHTML = tune.icon;

            button.addEventListener('click', () => {
                this.align = tune.value;
            });
            wrapper.appendChild(button);
        });

        return wrapper;
    }

    render() {
        this.wrapper = document.createElement('div');
        this.imageWrapper = document.createElement('div');
        this.buttonWrapper = document.createElement('div');
        this.image = document.createElement('img');
        this.choose = document.createElement( 'button' );

        this.imageWrapper.appendChild(this.image);
        this.buttonWrapper.appendChild(this.choose);

        this.wrapper.appendChild(this.imageWrapper);
        this.wrapper.appendChild(this.buttonWrapper);

        this.wrapper.classList.add('ns-editor-media');
        this.imageWrapper.classList.add('ns-editor-media-image');
        this.imageWrapper.classList.add('hide');
        this.buttonWrapper.classList.add('ns-editor-media-buttons');
        this.choose.classList.add('ns-editor-media-choose');

        this.choose.innerText = __('Choose');

        this.choose.addEventListener( 'click', async () => {
            try {
                interface NewType {
                    event: string;
                    value: {
                        name: string;
                        id: number;
                        selected: boolean;
                        slug: string;
                        user_id: number;
                        user: any;
                        sizes: {
                            original: string;
                            thumb: string;
                        };
                    }[];
                }

                const promise: NewType = await new Promise( ( resolve, reject ) => {
                    Popup.show( nsMedia, {
                        resolve, reject
                    })
                });;

                this.image.src = promise.value[0].sizes.original
                this.imageWrapper.classList.remove('hide');
                this.buttonWrapper.classList.add( 'hide' );
            } catch( exception ) {
                console.log({ exception });
            }
        })

        return this.wrapper;
    }

    save(blockContent) {
        return {
            url: blockContent.querySelector('img').src,
        };
    }
}

const props = defineProps<{
    field: {
        type: string,
        name: string,
        label?: string,
        data?: any,
        description?: string,
        validation?: string,
        value?: any,
    }
}>();

const editorElement = ref<HTMLElement | null>(null);
let editor: EditorJS; // Make editor accessible in onChange

const emit = defineEmits(['change']);

onMounted(() => {
    editor = new EditorJS({
        holder: editorElement.value as HTMLElement,
        data: props.field.value ? (
            typeof props.field.value === 'string' ? JSON.parse(props.field.value) : props.field.value
        ) : {},
        tools: {
            media: Media,
            list: {
                class: EditorjsList,
                inlineToolbar: true,
                config: {
                    defaultStyle: 'unordered'
                },
            },
            delimiter: {
                class: Delimiter,
                inlineToolbar: true,
            },
            warning: {
                class: Warning,
                inlineToolbar: true,
                shortcut: 'CMD+SHIFT+W',
                config: {
                    titlePlaceholder: __('Title'),
                    messagePlaceholder: __('Message'),
                },
            },
            paragraph: {
                class: Paragraph,
                inlineToolbar: true,
                config: {
                    placeholder: __('Enter text here...'),
                }
            },
            quote: {
                class: Quote,
                inlineToolbar: true,
                config: {
                    quotePlaceholder: __('Enter a quote'),
                    captionPlaceholder: __('Quote\'s author'),
                }
            },
            header: {
                class: Header,
                inlineToolbar: true,
                config: {
                    placeholder: __('Enter a header'),
                    levels: [1, 2, 3],
                    defaultLevel: 2,
                }
            }
        },
        onChange: async (api, event) => {
            const data = await editor.save();
            emit('change', data);
            props.field.value = data;
        }
    });
})
</script>
<template>
    <div class="ns-editor w-full">
        <div ref="editorElement" class="editor"></div>
    </div>
</template>