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
        // Dimension metadata
        width: null as number | null,
        height: null as number | null,
        // Video-specific metadata
        loop: false,
        autoplay: false,
        muted: false,
        controls: true,
    };

    private originalWidth: number = 0;
    private originalHeight: number = 0;

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

        // Add dimensions section
        const dims = this.getOriginalDimensions();
        this.originalWidth = dims.width;
        this.originalHeight = dims.height;

        // Separator
        let separator = document.createElement('div');
        separator.style.height = '1px';
        separator.style.backgroundColor = '#e0e0e0';
        separator.style.margin = '8px 0';
        wrapper.appendChild(separator);

        // Dimensions section title
        const dimTitle = document.createElement('div');
        dimTitle.style.fontSize = '12px';
        dimTitle.style.fontWeight = 'bold';
        dimTitle.style.padding = '8px';
        dimTitle.style.color = '#666';
        dimTitle.innerHTML = __('Dimensions');
        wrapper.appendChild(dimTitle);

        // Original dimensions display
        if (this.originalWidth > 0 && this.originalHeight > 0) {
            const originalDisplay = document.createElement('div');
            originalDisplay.style.fontSize = '11px';
            originalDisplay.style.padding = '4px 8px';
            originalDisplay.style.color = '#999';
            originalDisplay.innerHTML = `${__('Original')}: ${this.originalWidth}×${this.originalHeight}px`;
            wrapper.appendChild(originalDisplay);
        }

        // Width input
        const widthContainer = document.createElement('div');
        widthContainer.style.display = 'flex';
        widthContainer.style.alignItems = 'center';
        widthContainer.style.padding = '8px';
        widthContainer.style.gap = '8px';

        const widthLabel = document.createElement('label');
        widthLabel.style.fontSize = '12px';
        widthLabel.style.minWidth = '50px';
        widthLabel.innerHTML = __('Width') + ':';

        const widthInput = document.createElement('input');
        widthInput.type = 'number';
        widthInput.placeholder = 'auto';
        widthInput.value = this.data.width ? String(this.data.width) : '';
        widthInput.style.flex = '1';
        widthInput.style.padding = '4px';
        widthInput.style.border = '1px solid #ddd';
        widthInput.style.borderRadius = '3px';
        widthInput.style.fontSize = '12px';

        const widthUnit = document.createElement('span');
        widthUnit.style.fontSize = '12px';
        widthUnit.style.color = '#666';
        widthUnit.innerHTML = 'px';

        widthInput.addEventListener('change', () => {
            const val = widthInput.value ? parseInt(widthInput.value) : null;
            this.data.width = val;
            this.applyDimensions();
        });

        widthContainer.appendChild(widthLabel);
        widthContainer.appendChild(widthInput);
        widthContainer.appendChild(widthUnit);
        wrapper.appendChild(widthContainer);

        // Height input
        const heightContainer = document.createElement('div');
        heightContainer.style.display = 'flex';
        heightContainer.style.alignItems = 'center';
        heightContainer.style.padding = '8px';
        heightContainer.style.gap = '8px';

        const heightLabel = document.createElement('label');
        heightLabel.style.fontSize = '12px';
        heightLabel.style.minWidth = '50px';
        heightLabel.innerHTML = __('Height') + ':';

        const heightInput = document.createElement('input');
        heightInput.type = 'number';
        heightInput.placeholder = 'auto';
        heightInput.value = this.data.height ? String(this.data.height) : '';
        heightInput.style.flex = '1';
        heightInput.style.padding = '4px';
        heightInput.style.border = '1px solid #ddd';
        heightInput.style.borderRadius = '3px';
        heightInput.style.fontSize = '12px';

        const heightUnit = document.createElement('span');
        heightUnit.style.fontSize = '12px';
        heightUnit.style.color = '#666';
        heightUnit.innerHTML = 'px';

        heightInput.addEventListener('change', () => {
            const val = heightInput.value ? parseInt(heightInput.value) : null;
            this.data.height = val;
            this.applyDimensions();
        });

        heightContainer.appendChild(heightLabel);
        heightContainer.appendChild(heightInput);
        heightContainer.appendChild(heightUnit);
        wrapper.appendChild(heightContainer);

        // Reset dimensions button
        const resetButton = document.createElement('button');
        resetButton.style.fontSize = '11px';
        resetButton.style.padding = '4px 8px';
        resetButton.style.margin = '4px 8px';
        resetButton.style.border = '1px solid #ddd';
        resetButton.style.borderRadius = '3px';
        resetButton.style.backgroundColor = '#f5f5f5';
        resetButton.style.cursor = 'pointer';
        resetButton.style.width = 'calc(100% - 16px)';
        resetButton.innerHTML = '<i class="las la-redo" style="margin-right: 4px;"></i>' + __('Reset to Original');
        
        resetButton.addEventListener('click', () => {
            this.data.width = null;
            this.data.height = null;
            widthInput.value = '';
            heightInput.value = '';
            this.applyDimensions();
        });
        wrapper.appendChild(resetButton);

        // Add video-specific settings only if video is selected
        if (this.mediaType === 'video') {
            // Add separator
            const videoSeparator = document.createElement('div');
            videoSeparator.style.height = '1px';
            videoSeparator.style.backgroundColor = '#e0e0e0';
            videoSeparator.style.margin = '8px 0';
            wrapper.appendChild(videoSeparator);

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

    /**
     * Get original dimensions of the media
     */
    private getOriginalDimensions(): { width: number; height: number } {
        if (this.mediaType === 'video' && this.mediaElement instanceof HTMLVideoElement) {
            const video = this.mediaElement as HTMLVideoElement;
            return {
                width: video.videoWidth,
                height: video.videoHeight,
            };
        } else if (this.mediaType === 'image' && this.mediaElement instanceof HTMLImageElement) {
            const img = this.mediaElement as HTMLImageElement;
            return {
                width: img.naturalWidth,
                height: img.naturalHeight,
            };
        }
        return { width: 0, height: 0 };
    }

    /**
     * Apply dimensions to the media element
     */
    private applyDimensions() {
        const width = this.data.width;
        const height = this.data.height;

        if (width) {
            this.mediaElement.style.width = `${width}px`;
        } else {
            this.mediaElement.style.width = '100%';
        }

        if (height) {
            this.mediaElement.style.height = `${height}px`;
        } else {
            this.mediaElement.style.height = 'auto';
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
            
            // The applyDimensions will be called in setMedia after dimensions are detected
            // But we need to also restore video settings here
            if (mediaType === 'video') {
                this.applyVideoSettings();
            }
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
                
                // Capture original dimensions when metadata is loaded
                video.addEventListener('loadedmetadata', () => {
                    this.originalWidth = video.videoWidth;
                    this.originalHeight = video.videoHeight;
                    this.applyDimensions();
                }, { once: true });
            } else {
                const img = this.mediaElement as HTMLImageElement;
                img.src = url;
                
                // Capture original dimensions when image loads
                if (img.complete) {
                    // Image is already cached
                    this.originalWidth = img.naturalWidth;
                    this.originalHeight = img.naturalHeight;
                    this.applyDimensions();
                } else {
                    // Wait for image to load
                    img.addEventListener('load', () => {
                        this.originalWidth = img.naturalWidth;
                        this.originalHeight = img.naturalHeight;
                        this.applyDimensions();
                    }, { once: true });
                }
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

        // Save dimension data if customized
        if (this.data.width !== null) {
            savedData.width = this.data.width;
        }
        if (this.data.height !== null) {
            savedData.height = this.data.height;
        }

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