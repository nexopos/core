<script lang="ts" setup>
import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import Quote from '@editorjs/quote';
import Warning from '@editorjs/warning';
import Paragraph from '@editorjs/paragraph';
import Delimiter from '@editorjs/delimiter';
import EditorjsList from '@editorjs/list';
import Table from '@editorjs/table';
import DragDrop from "editorjs-drag-drop";
import { onMounted, ref } from 'vue';
import { __ } from '~/libraries/lang';
import { default as nsMedia } from '~/pages/dashboard/ns-media.vue';

declare const Popup;

class Media {
    private wrapper: HTMLElement;
    private mediaWrapper: HTMLElement;
    private buttonWrapper: HTMLElement;
    private mediaElement: HTMLImageElement | HTMLVideoElement;
    private choose: HTMLButtonElement;
    private mediaType: 'image' | 'video' = 'image';

    private __align: string = 'left';

    private data = {
        url: undefined,
        align: 'left',
        // Video-specific metadata
        loop: false,
        autoplay: false,
        muted: false,
        controls: true,
    };

    constructor({ data }) {
        this.data = data || this.data;
    }

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
        this.mediaWrapper.classList.remove('align-left', 'align-center', 'align-right');
        this.mediaWrapper.classList.add(`align-${value}`);
    }

    /**
     * Detect media type from file extension
     */
    private detectMediaType(url: string): 'image' | 'video' {
        const ext = url.split('.').pop()?.toLowerCase() || '';
        const videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv', 'm4v'];
        return videoExtensions.includes(ext) ? 'video' : 'image';
    }

    /**
     * Create appropriate media element based on type
     */
    private createMediaElement(mediaType: 'image' | 'video'): HTMLImageElement | HTMLVideoElement {
        if (mediaType === 'video') {
            const video = document.createElement('video');
            video.controls = true;
            video.style.width = '100%';
            video.style.height = 'auto';
            video.style.maxWidth = '100%';
            return video;
        } else {
            return document.createElement('img');
        }
    }

    renderSettings( data ) {
        const wrapper = document.createElement('div');

        // Common alignment settings for all media
        const alignmentSettings = [
            {
                name: __('Align Left'),
                value: 'left',
                icon: '<i class="las la-align-left text-lg"></i>',
                type: 'alignment',
            },
            {
                name: __('Align Center'),
                value: 'center',
                icon: '<i class="las la-align-center text-lg"></i>',
                type: 'alignment',
            },
            {
                name: __('Align Right'),
                value: 'right',
                icon: '<i class="las la-align-right text-lg"></i>',
                type: 'alignment',
            },
        ];

        // Video-specific settings
        const videoSettings = [
            {
                name: __('Loop'),
                value: 'loop',
                icon: '<i class="las la-redo text-lg"></i>',
                type: 'toggle',
                checked: this.data.loop,
            },
            {
                name: __('Autoplay'),
                value: 'autoplay',
                icon: '<i class="las la-play text-lg"></i>',
                type: 'toggle',
                checked: this.data.autoplay,
            },
            {
                name: __('Muted'),
                value: 'muted',
                icon: '<i class="las la-volume-mute text-lg"></i>',
                type: 'toggle',
                checked: this.data.muted,
            },
            {
                name: __('Show Controls'),
                value: 'controls',
                icon: '<i class="las la-sliders-h text-lg"></i>',
                type: 'toggle',
                checked: this.data.controls,
            },
        ];

        // Add alignment settings
        alignmentSettings.forEach(tune => {
            let button = document.createElement('div');
            button.classList.add('cdx-settings-button');
            button.innerHTML = tune.icon;
            button.addEventListener('click', () => {
                this.align = tune.value;
            });
            wrapper.appendChild(button);
        });

        // Add video-specific settings only if video is selected
        if (this.mediaType === 'video') {
            // Add separator
            const separator = document.createElement('div');
            separator.style.height = '1px';
            separator.style.backgroundColor = '#e0e0e0';
            separator.style.margin = '8px 0';
            wrapper.appendChild(separator);

            videoSettings.forEach(tune => {
                const toggleContainer = document.createElement('div');
                toggleContainer.style.display = 'flex';
                toggleContainer.style.alignItems = 'center';
                toggleContainer.style.padding = '8px';
                toggleContainer.style.cursor = 'pointer';
                toggleContainer.style.transition = 'background-color 0.2s';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = tune.checked;
                checkbox.style.marginRight = '8px';
                checkbox.style.cursor = 'pointer';

                const label = document.createElement('span');
                label.innerHTML = tune.icon + ' ' + tune.name;
                label.style.fontSize = '12px';
                label.style.userSelect = 'none';

                toggleContainer.addEventListener('click', () => {
                    checkbox.checked = !checkbox.checked;
                    this.data[tune.value] = checkbox.checked;
                    this.applyVideoSettings();
                });

                checkbox.addEventListener('change', (e) => {
                    this.data[tune.value] = (e.target as HTMLInputElement).checked;
                    this.applyVideoSettings();
                });

                toggleContainer.appendChild(checkbox);
                toggleContainer.appendChild(label);
                wrapper.appendChild(toggleContainer);
            });
        }

        return wrapper;
    }

    /**
     * Apply video settings to the video element
     */
    private applyVideoSettings() {
        if (this.mediaType === 'video' && this.mediaElement instanceof HTMLVideoElement) {
            const video = this.mediaElement as HTMLVideoElement;
            video.loop = this.data.loop;
            video.autoplay = this.data.autoplay;
            video.muted = this.data.muted;
            video.controls = this.data.controls;
        }
    }

    render() { 
        this.wrapper = document.createElement('div');
        this.mediaWrapper = document.createElement('div');
        this.buttonWrapper = document.createElement('div');
        this.choose = document.createElement('button');

        // Create image element initially (will be replaced if video)
        this.mediaElement = this.createMediaElement('image');
        
        this.mediaWrapper.appendChild(this.mediaElement);
        this.buttonWrapper.appendChild(this.choose);

        this.wrapper.appendChild(this.mediaWrapper);
        this.wrapper.appendChild(this.buttonWrapper);

        this.wrapper.classList.add('ns-editor-media');
        this.mediaWrapper.classList.add('ns-editor-media-image');
        this.mediaWrapper.classList.add('hide');
        this.buttonWrapper.classList.add('ns-editor-media-buttons');
        this.choose.classList.add('ns-editor-media-choose');

        this.choose.innerText = __('Choose');
        this.choose.addEventListener('click', async () => {
            try {
                interface MediaData {
                    event: string;
                    value: {
                        name: string;
                        id: number;
                        selected: boolean;
                        slug: string;
                        user_id: number;
                        user: any;
                        extension: string;
                        sizes: {
                            original: string;
                            thumb: string;
                        };
                    }[];
                }

                const promise: MediaData = await new Promise((resolve, reject) => {
                    Popup.show(nsMedia, {
                        resolve, reject
                    })
                });

                const mediaUrl = promise.value[0].sizes.original;
                const mediaType = this.detectMediaType(mediaUrl);
                
                // Reset video metadata when selecting new media
                if (mediaType === 'video') {
                    this.data.loop = false;
                    this.data.autoplay = false;
                    this.data.muted = false;
                    this.data.controls = true;
                }
                
                this.setMedia(mediaUrl, mediaType, this.data.align);
            } catch(exception) {
                console.log({ exception });
            }
        })

        /**
         * Let's restore the saved data
         */
        if (this.data.url) {
            const mediaType = this.detectMediaType(this.data.url);
            this.setMedia(this.data.url, mediaType, this.data.align);
        }

        return this.wrapper;
    }

    /**
     * Set media content (image or video)
     */
    setMedia(url: string, mediaType: 'image' | 'video', align: string = 'left') {
        if (url) {
            // If media type changed, recreate the element
            if (this.mediaType !== mediaType) {
                this.mediaType = mediaType;
                this.mediaWrapper.innerHTML = '';
                this.mediaElement = this.createMediaElement(mediaType);
                this.mediaWrapper.appendChild(this.mediaElement);
            }

            // Set the source
            if (mediaType === 'video') {
                const video = this.mediaElement as HTMLVideoElement;
                video.src = url;
                // Apply saved video settings
                this.applyVideoSettings();
            } else {
                const img = this.mediaElement as HTMLImageElement;
                img.src = url;
            }

            this.align = align || 'left';
            this.mediaWrapper.classList.remove('hide');
            this.buttonWrapper.classList.add('hide');
        } else {
            this.mediaWrapper.classList.add('hide');
            this.buttonWrapper.classList.remove('hide');
        }
    }

    save(blockContent) {
        const mediaElement = blockContent.querySelector('img, video');
        const src = mediaElement?.src || mediaElement?.getAttribute('src') || '';
        
        const savedData: any = {
            url: src,
            align: this.align,
        };

        // Save video-specific metadata if it's a video
        if (this.mediaType === 'video') {
            savedData.loop = this.data.loop;
            savedData.autoplay = this.data.autoplay;
            savedData.muted = this.data.muted;
            savedData.controls = this.data.controls;
        }
        
        return savedData;
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
        data: props.field.value ? JSON.parse(
            // Always deserialise through JSON to strip Vue reactive Proxy wrappers.
            // @editorjs/list ≤2.0.8 returns the input object unchanged when it is
            // already in v2 format, so a Proxy would flow into EditorJS's internal
            // structuredClone() call and throw a DataCloneError.
            typeof props.field.value === 'string' ? props.field.value : JSON.stringify(props.field.value)
        ) : {},
        onReady: () => {
            // Initialize drag and drop
            new DragDrop(editor);
        },
        tools: {
            header: {
                class: Header,
                inlineToolbar: true,
                config: {
                    placeholder: __('Enter a header'),
                    levels: [1, 2, 3],
                    defaultLevel: 2,
                }
            },
            media: Media,
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
            list: {
                class: EditorjsList,
                inlineToolbar: true,
                config: {
                    defaultStyle: 'unordered'
                },
            },
            table: {
                class: Table,
                inlineToolbar: true,
                config: {
                    rows: 2,
                    cols: 3,
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