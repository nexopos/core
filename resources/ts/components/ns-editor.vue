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

class Code {
    private wrapper: HTMLElement;
    private codeInput: HTMLTextAreaElement;
    private codeDisplay: HTMLElement;
    private selectedLanguage: string = 'javascript';

    private data = {
        code: '',
        language: 'javascript',
    };

    private languages = [
        { label: 'JavaScript', value: 'javascript' },
        { label: 'Python', value: 'python' },
        { label: 'PHP', value: 'php' },
        { label: 'HTML', value: 'html' },
        { label: 'CSS', value: 'css' },
        { label: 'TypeScript', value: 'typescript' },
        { label: 'Java', value: 'java' },
        { label: 'C++', value: 'cpp' },
        { label: 'C#', value: 'csharp' },
        { label: 'Ruby', value: 'ruby' },
        { label: 'Go', value: 'go' },
        { label: 'Rust', value: 'rust' },
        { label: 'SQL', value: 'sql' },
        { label: 'JSON', value: 'json' },
        { label: 'XML', value: 'xml' },
        { label: 'Bash', value: 'bash' },
        { label: 'Shell', value: 'shell' },
        { label: 'Plain Text', value: 'plaintext' },
    ];

    constructor({ data }) {
        this.data = data || this.data;
        this.selectedLanguage = this.data.language || 'javascript';
    }

    static get toolbox() {
        return {
            title: __('Code'),
        };
    }

    renderSettings() {
        const wrapper = document.createElement('div');

        const languageLabel = document.createElement('div');
        languageLabel.style.fontSize = '12px';
        languageLabel.style.fontWeight = 'bold';
        languageLabel.style.padding = '8px';
        languageLabel.style.color = '#666';
        languageLabel.innerHTML = __('Language');
        wrapper.appendChild(languageLabel);

        const select = document.createElement('select');
        select.style.width = '100%';
        select.style.padding = '6px';
        select.style.marginBottom = '8px';
        select.style.border = '1px solid #ddd';
        select.style.borderRadius = '3px';
        select.style.fontSize = '12px';

        this.languages.forEach(lang => {
            const option = document.createElement('option');
            option.value = lang.value;
            option.textContent = lang.label;
            option.selected = this.selectedLanguage === lang.value;
            select.appendChild(option);
        });

        select.addEventListener('change', (e) => {
            this.selectedLanguage = (e.target as HTMLSelectElement).value;
            this.data.language = this.selectedLanguage;
        });

        wrapper.appendChild(select);
        return wrapper;
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.add('ns-editor-code');

        // Language badge
        const languageBadge = document.createElement('div');
        languageBadge.classList.add('ns-editor-code-language');
        languageBadge.style.backgroundColor = '#f0f0f0';
        languageBadge.style.padding = '4px 8px';
        languageBadge.style.fontSize = '11px';
        languageBadge.style.color = '#666';
        languageBadge.style.marginBottom = '8px';
        languageBadge.style.borderRadius = '3px';
        languageBadge.style.display = 'inline-block';
        languageBadge.innerHTML = this.languages.find(l => l.value === this.selectedLanguage)?.label || 'Code';

        // Code input
        this.codeInput = document.createElement('textarea');
        this.codeInput.classList.add('ns-editor-code-input');
        this.codeInput.style.width = '100%';
        this.codeInput.style.minHeight = '200px';
        this.codeInput.style.padding = '12px';
        this.codeInput.style.fontFamily = 'monospace';
        this.codeInput.style.fontSize = '13px';
        this.codeInput.style.border = '1px solid #ddd';
        this.codeInput.style.borderRadius = '3px';
        this.codeInput.style.backgroundColor = '#fafafa';
        this.codeInput.value = this.data.code || '';
        this.codeInput.placeholder = __('Enter your code here...');

        this.codeInput.addEventListener('input', (e) => {
            this.data.code = (e.target as HTMLTextAreaElement).value;
            languageBadge.innerHTML = this.languages.find(l => l.value === this.selectedLanguage)?.label || 'Code';
        });

        this.wrapper.appendChild(languageBadge);
        this.wrapper.appendChild(this.codeInput);

        return this.wrapper;
    }

    save(blockContent) {
        return {
            code: this.data.code,
            language: this.data.language,
        };
    }
}

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
        widthContainer.style.flexDirection = 'column';
        widthContainer.style.padding = '8px';
        widthContainer.style.gap = '4px';

        const widthLabel = document.createElement('label');
        widthLabel.style.fontSize = '12px';
        widthLabel.style.fontWeight = '500';
        widthLabel.innerHTML = __('Width') + ':';

        const widthInputWrapper = document.createElement('div');
        widthInputWrapper.style.display = 'flex';
        widthInputWrapper.style.alignItems = 'center';
        widthInputWrapper.style.gap = '4px';

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
        widthInputWrapper.appendChild(widthInput);
        widthInputWrapper.appendChild(widthUnit);
        widthContainer.appendChild(widthInputWrapper);
        wrapper.appendChild(widthContainer);

        // Height input
        const heightContainer = document.createElement('div');
        heightContainer.style.display = 'flex';
        heightContainer.style.flexDirection = 'column';
        heightContainer.style.padding = '8px';
        heightContainer.style.gap = '4px';

        const heightLabel = document.createElement('label');
        heightLabel.style.fontSize = '12px';
        heightLabel.style.fontWeight = '500';
        heightLabel.innerHTML = __('Height') + ':';

        const heightInputWrapper = document.createElement('div');
        heightInputWrapper.style.display = 'flex';
        heightInputWrapper.style.alignItems = 'center';
        heightInputWrapper.style.gap = '4px';

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
        heightInputWrapper.appendChild(heightInput);
        heightInputWrapper.appendChild(heightUnit);
        heightContainer.appendChild(heightInputWrapper);
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

/**
 * YouTube embed tool for EditorJS.
 *
 * Saves a full guide data object so consumers can render the iframe
 * with every supported YouTube player parameter.
 */
class Youtube {
    private wrapper: HTMLElement;
    private embedWrapper: HTMLElement;
    private formWrapper: HTMLElement;
    private urlInput: HTMLInputElement;
    private captionInput: HTMLInputElement;
    private iframe: HTMLIFrameElement | null = null;
    private __align: string = 'center';

    private data = {
        url: '' as string,
        videoId: '' as string,
        service: 'youtube' as const,
        // Layout
        align: 'center' as string,
        width: null as number | null,
        height: null as number | null,
        aspectRatio: '16:9' as string,
        caption: '' as string,
        // Player parameters (YouTube IFrame Player API)
        autoplay: false,
        mute: false,
        controls: true,
        loop: false,
        modestBranding: true,
        rel: false,
        fs: true,
        disablekb: false,
        ccLoadPolicy: false,
        ivLoadPolicy: 1 as 1 | 3,
        playsinline: true,
        privacyMode: true,
        color: 'red' as 'red' | 'white',
        start: null as number | null,
        end: null as number | null,
        hl: '' as string,
        playlist: '' as string,
    };

    /**
     * Schema describing every field persisted by save().
     * Use this as a guide when configuring / rendering the embed output.
     */
    static get guideData() {
        return {
            url: {
                type: 'string',
                description: 'Original YouTube URL provided by the user.',
                example: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            },
            videoId: {
                type: 'string',
                description: 'Extracted YouTube video identifier.',
                example: 'dQw4w9WgXcQ',
            },
            service: {
                type: 'string',
                description: 'Embed provider identifier.',
                value: 'youtube',
            },
            align: {
                type: 'string',
                description: 'Horizontal alignment of the embed.',
                options: [ 'left', 'center', 'right' ],
                default: 'center',
            },
            width: {
                type: 'number|null',
                description: 'Fixed width in pixels. null keeps fluid 100% width.',
                default: null,
            },
            height: {
                type: 'number|null',
                description: 'Fixed height in pixels. null uses aspect-ratio height.',
                default: null,
            },
            aspectRatio: {
                type: 'string',
                description: 'Aspect ratio used when height is not fixed.',
                options: [ '16:9', '4:3', '1:1', '9:16' ],
                default: '16:9',
            },
            caption: {
                type: 'string',
                description: 'Optional caption shown below the embed.',
                default: '',
            },
            autoplay: {
                type: 'boolean',
                description: 'Start playback automatically (often requires mute).',
                default: false,
                param: 'autoplay',
            },
            mute: {
                type: 'boolean',
                description: 'Mute audio on load.',
                default: false,
                param: 'mute',
            },
            controls: {
                type: 'boolean',
                description: 'Show player controls.',
                default: true,
                param: 'controls',
            },
            loop: {
                type: 'boolean',
                description: 'Loop the video. Uses playlist=videoId when no playlist is set.',
                default: false,
                param: 'loop',
            },
            modestBranding: {
                type: 'boolean',
                description: 'Reduce YouTube branding on the player.',
                default: true,
                param: 'modestbranding',
            },
            rel: {
                type: 'boolean',
                description: 'Show related videos from any channel when true; same channel when false.',
                default: false,
                param: 'rel',
            },
            fs: {
                type: 'boolean',
                description: 'Show the fullscreen button.',
                default: true,
                param: 'fs',
            },
            disablekb: {
                type: 'boolean',
                description: 'Disable keyboard controls.',
                default: false,
                param: 'disablekb',
            },
            ccLoadPolicy: {
                type: 'boolean',
                description: 'Force closed captions on by default.',
                default: false,
                param: 'cc_load_policy',
            },
            ivLoadPolicy: {
                type: 'number',
                description: 'Video annotations: 1 = show, 3 = hide.',
                options: [ 1, 3 ],
                default: 1,
                param: 'iv_load_policy',
            },
            playsinline: {
                type: 'boolean',
                description: 'Play inline on iOS instead of fullscreen.',
                default: true,
                param: 'playsinline',
            },
            privacyMode: {
                type: 'boolean',
                description: 'Use youtube-nocookie.com (privacy-enhanced mode).',
                default: true,
            },
            color: {
                type: 'string',
                description: 'Progress bar color.',
                options: [ 'red', 'white' ],
                default: 'red',
                param: 'color',
            },
            start: {
                type: 'number|null',
                description: 'Start playback at this second.',
                default: null,
                param: 'start',
            },
            end: {
                type: 'number|null',
                description: 'Stop playback at this second.',
                default: null,
                param: 'end',
            },
            hl: {
                type: 'string',
                description: 'Player interface language (ISO 639-1 code).',
                default: '',
                param: 'hl',
                example: 'en',
            },
            playlist: {
                type: 'string',
                description: 'Comma-separated video IDs or playlist ID to play after the main video.',
                default: '',
                param: 'playlist',
            },
            embedUrl: {
                type: 'string',
                description: 'Computed embed URL including all active player parameters (included in save output).',
            },
        };
    }

    constructor({ data }) {
        this.data = {
            ...this.data,
            ...( data || {} ),
        };
        this.__align = this.data.align || 'center';
    }

    static get toolbox() {
        return {
            title: __( 'YouTube' ),
            icon: '<svg width="17" height="12" viewBox="0 0 17 12" xmlns="http://www.w3.org/2000/svg"><path d="M16.3 1.9A2.1 2.1 0 0 0 14.8.4C13.5 0 8.5 0 8.5 0S3.5 0 2.2.4A2.1 2.1 0 0 0 .7 1.9 22 22 0 0 0 0 6a22 22 0 0 0 .7 4.1 2.1 2.1 0 0 0 1.5 1.5C3.5 12 8.5 12 8.5 12s5 0 6.3-.4a2.1 2.1 0 0 0 1.5-1.5A22 22 0 0 0 17 6a22 22 0 0 0-.7-4.1zM6.8 8.6V3.4L11.2 6 6.8 8.6z" fill="currentColor"/></svg>',
        };
    }

    /**
     * Accept pasted YouTube URLs and convert them into this block.
     */
    static get pasteConfig() {
        return {
            patterns: {
                youtube: /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be|youtube-nocookie\.com)\/\S+/i,
            },
        };
    }

    onPaste( event ) {
        if ( event.type === 'pattern' ) {
            const url = event.detail.data;
            this.urlInput.value = url;
            this.applyUrl( url );
        }
    }

    get align() {
        return this.__align;
    }

    set align( value: string ) {
        this.__align = value;
        this.data.align = value;
        this.embedWrapper.classList.remove( 'align-left', 'align-center', 'align-right' );
        this.embedWrapper.classList.add( `align-${value}` );
    }

    /**
     * Extract a video ID (and optional playlist / start time) from common YouTube URL forms.
     */
    private parseYoutubeUrl( raw: string ): { videoId: string; playlist: string; start: number | null; privacyMode: boolean } | null {
        if ( ! raw || typeof raw !== 'string' ) {
            return null;
        }

        const input = raw.trim();

        // Bare video ID
        if ( /^[a-zA-Z0-9_-]{11}$/.test( input ) ) {
            return { videoId: input, playlist: '', start: null, privacyMode: this.data.privacyMode };
        }

        let url: URL;
        try {
            url = new URL( input.startsWith( 'http' ) ? input : `https://${input}` );
        } catch {
            return null;
        }

        const host = url.hostname.replace( /^www\./, '' );
        const privacyMode = host.includes( 'youtube-nocookie.com' ) || this.data.privacyMode;
        let videoId = '';
        let playlist = url.searchParams.get( 'list' ) || '';
        let start: number | null = null;

        const t = url.searchParams.get( 't' ) || url.searchParams.get( 'start' );
        if ( t ) {
            start = this.parseTimeToSeconds( t );
        }

        if ( host === 'youtu.be' ) {
            videoId = url.pathname.split( '/' ).filter( Boolean )[0] || '';
        } else if ( host.includes( 'youtube.com' ) || host.includes( 'youtube-nocookie.com' ) ) {
            const path = url.pathname;
            if ( path === '/watch' ) {
                videoId = url.searchParams.get( 'v' ) || '';
            } else if ( path.startsWith( '/embed/' ) || path.startsWith( '/v/' ) || path.startsWith( '/shorts/' ) || path.startsWith( '/live/' ) ) {
                videoId = path.split( '/' ).filter( Boolean )[1] || '';
            }
        }

        if ( ! videoId || ! /^[a-zA-Z0-9_-]{11}$/.test( videoId ) ) {
            // Playlist-only URL is still valid if list is present
            if ( ! playlist ) {
                return null;
            }
        }

        return { videoId, playlist, start, privacyMode };
    }

    private parseTimeToSeconds( value: string ): number | null {
        if ( ! value ) {
            return null;
        }

        // Pure seconds: "90" or "90s"
        if ( /^\d+s?$/.test( value ) ) {
            return parseInt( value, 10 );
        }

        // YouTube time format: 1h2m3s
        const match = value.match( /(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?/ );
        if ( match && ( match[1] || match[2] || match[3] ) ) {
            const hours = parseInt( match[1] || '0', 10 );
            const minutes = parseInt( match[2] || '0', 10 );
            const seconds = parseInt( match[3] || '0', 10 );
            return hours * 3600 + minutes * 60 + seconds;
        }

        const asNumber = parseInt( value, 10 );
        return Number.isFinite( asNumber ) ? asNumber : null;
    }

    /**
     * Build the iframe src from current guide data.
     */
    private buildEmbedUrl(): string {
        if ( ! this.data.videoId && ! this.data.playlist ) {
            return '';
        }

        const host = this.data.privacyMode
            ? 'https://www.youtube-nocookie.com'
            : 'https://www.youtube.com';

        const path = this.data.videoId
            ? `/embed/${this.data.videoId}`
            : '/embed/videoseries';

        const params = new URLSearchParams();

        params.set( 'autoplay', this.data.autoplay ? '1' : '0' );
        params.set( 'mute', this.data.mute ? '1' : '0' );
        params.set( 'controls', this.data.controls ? '1' : '0' );
        params.set( 'loop', this.data.loop ? '1' : '0' );
        params.set( 'modestbranding', this.data.modestBranding ? '1' : '0' );
        params.set( 'rel', this.data.rel ? '1' : '0' );
        params.set( 'fs', this.data.fs ? '1' : '0' );
        params.set( 'disablekb', this.data.disablekb ? '1' : '0' );
        params.set( 'playsinline', this.data.playsinline ? '1' : '0' );
        params.set( 'iv_load_policy', String( this.data.ivLoadPolicy ) );
        params.set( 'color', this.data.color );

        if ( this.data.ccLoadPolicy ) {
            params.set( 'cc_load_policy', '1' );
        }

        if ( this.data.start !== null && this.data.start !== undefined && this.data.start >= 0 ) {
            params.set( 'start', String( this.data.start ) );
        }

        if ( this.data.end !== null && this.data.end !== undefined && this.data.end > 0 ) {
            params.set( 'end', String( this.data.end ) );
        }

        if ( this.data.hl ) {
            params.set( 'hl', this.data.hl );
        }

        // Loop requires playlist; default to the same video ID
        let playlist = this.data.playlist || '';
        if ( this.data.loop && ! playlist && this.data.videoId ) {
            playlist = this.data.videoId;
        }
        if ( playlist ) {
            params.set( 'playlist', playlist );
        }
        if ( ! this.data.videoId && this.data.playlist ) {
            params.set( 'list', this.data.playlist );
        }

        return `${host}${path}?${params.toString()}`;
    }

    private aspectRatioPadding(): string {
        const map: Record<string, string> = {
            '16:9': '56.25%',
            '4:3': '75%',
            '1:1': '100%',
            '9:16': '177.78%',
        };
        return map[ this.data.aspectRatio ] || map['16:9'];
    }

    private applyUrl( raw: string ) {
        const parsed = this.parseYoutubeUrl( raw );
        if ( ! parsed ) {
            this.data.url = raw;
            this.data.videoId = '';
            this.showFormOnly();
            return;
        }

        this.data.url = raw.trim();
        this.data.videoId = parsed.videoId;
        this.data.privacyMode = parsed.privacyMode;
        if ( parsed.playlist ) {
            this.data.playlist = parsed.playlist;
        }
        if ( parsed.start !== null ) {
            this.data.start = parsed.start;
        }

        this.renderEmbed();
    }

    private showFormOnly() {
        this.embedWrapper.classList.add( 'hide' );
        this.formWrapper.classList.remove( 'hide' );
        if ( this.iframe ) {
            this.iframe.remove();
            this.iframe = null;
        }
    }

    private renderEmbed() {
        const embedUrl = this.buildEmbedUrl();
        if ( ! embedUrl ) {
            this.showFormOnly();
            return;
        }

        this.formWrapper.classList.add( 'hide' );
        this.embedWrapper.classList.remove( 'hide' );

        // Outer sizing container
        let frameBox = this.embedWrapper.querySelector( '.ns-editor-youtube-frame' ) as HTMLElement;
        if ( ! frameBox ) {
            frameBox = document.createElement( 'div' );
            frameBox.classList.add( 'ns-editor-youtube-frame' );
            this.embedWrapper.insertBefore( frameBox, this.embedWrapper.firstChild );
        }

        this.applyDimensions( frameBox );

        if ( ! this.iframe ) {
            this.iframe = document.createElement( 'iframe' );
            this.iframe.setAttribute( 'frameborder', '0' );
            this.iframe.setAttribute( 'allowfullscreen', 'true' );
            this.iframe.setAttribute(
                'allow',
                'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'
            );
            this.iframe.setAttribute( 'referrerpolicy', 'strict-origin-when-cross-origin' );
            this.iframe.title = __( 'YouTube video player' );
            this.iframe.style.position = 'absolute';
            this.iframe.style.inset = '0';
            this.iframe.style.width = '100%';
            this.iframe.style.height = '100%';
            this.iframe.style.border = '0';
            frameBox.appendChild( this.iframe );
        }

        this.iframe.src = embedUrl;
        this.align = this.data.align || 'center';

        // Caption
        let captionEl = this.embedWrapper.querySelector( '.ns-editor-youtube-caption' ) as HTMLElement;
        if ( this.data.caption ) {
            if ( ! captionEl ) {
                captionEl = document.createElement( 'div' );
                captionEl.classList.add( 'ns-editor-youtube-caption' );
                this.embedWrapper.appendChild( captionEl );
            }
            captionEl.textContent = this.data.caption;
            captionEl.classList.remove( 'hide' );
        } else if ( captionEl ) {
            captionEl.classList.add( 'hide' );
        }
    }

    private applyDimensions( frameBox?: HTMLElement ) {
        const box = frameBox || this.embedWrapper.querySelector( '.ns-editor-youtube-frame' ) as HTMLElement;
        if ( ! box ) {
            return;
        }

        box.style.position = 'relative';
        box.style.width = this.data.width ? `${this.data.width}px` : '100%';
        box.style.maxWidth = '100%';

        if ( this.data.height ) {
            box.style.height = `${this.data.height}px`;
            box.style.paddingBottom = '0';
        } else {
            box.style.height = '0';
            box.style.paddingBottom = this.aspectRatioPadding();
        }
    }

    private createToggle( label: string, key: string, icon: string ): HTMLElement {
        const row = document.createElement( 'div' );
        row.style.display = 'flex';
        row.style.alignItems = 'center';
        row.style.padding = '6px 8px';
        row.style.cursor = 'pointer';

        const checkbox = document.createElement( 'input' );
        checkbox.type = 'checkbox';
        checkbox.checked = !! this.data[ key ];
        checkbox.style.marginRight = '8px';
        checkbox.style.cursor = 'pointer';

        const text = document.createElement( 'span' );
        text.innerHTML = `${icon} ${label}`;
        text.style.fontSize = '12px';
        text.style.userSelect = 'none';

        const apply = () => {
            this.data[ key ] = checkbox.checked;
            if ( this.data.videoId || this.data.playlist ) {
                this.renderEmbed();
            }
        };

        row.addEventListener( 'click', ( e ) => {
            if ( e.target !== checkbox ) {
                checkbox.checked = ! checkbox.checked;
            }
            apply();
        } );

        checkbox.addEventListener( 'change', apply );

        row.appendChild( checkbox );
        row.appendChild( text );
        return row;
    }

    private createNumberField( label: string, key: string, placeholder: string ): HTMLElement {
        const container = document.createElement( 'div' );
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.padding = '6px 8px';
        container.style.gap = '4px';

        const fieldLabel = document.createElement( 'label' );
        fieldLabel.style.fontSize = '12px';
        fieldLabel.style.fontWeight = '500';
        fieldLabel.textContent = label;

        const input = document.createElement( 'input' );
        input.type = 'number';
        input.min = '0';
        input.placeholder = placeholder;
        input.value = this.data[ key ] !== null && this.data[ key ] !== undefined && this.data[ key ] !== ''
            ? String( this.data[ key ] )
            : '';
        input.style.width = '100%';
        input.style.padding = '4px';
        input.style.border = '1px solid #ddd';
        input.style.borderRadius = '3px';
        input.style.fontSize = '12px';

        input.addEventListener( 'change', () => {
            const val = input.value === '' ? null : parseInt( input.value, 10 );
            this.data[ key ] = Number.isFinite( val as number ) ? val : null;
            if ( this.data.videoId || this.data.playlist ) {
                this.renderEmbed();
            }
        } );

        container.appendChild( fieldLabel );
        container.appendChild( input );
        return container;
    }

    private createSelectField( label: string, key: string, options: { label: string; value: string | number }[] ): HTMLElement {
        const container = document.createElement( 'div' );
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.padding = '6px 8px';
        container.style.gap = '4px';

        const fieldLabel = document.createElement( 'label' );
        fieldLabel.style.fontSize = '12px';
        fieldLabel.style.fontWeight = '500';
        fieldLabel.textContent = label;

        const select = document.createElement( 'select' );
        select.style.width = '100%';
        select.style.padding = '4px';
        select.style.border = '1px solid #ddd';
        select.style.borderRadius = '3px';
        select.style.fontSize = '12px';

        options.forEach( opt => {
            const option = document.createElement( 'option' );
            option.value = String( opt.value );
            option.textContent = opt.label;
            option.selected = String( this.data[ key ] ) === String( opt.value );
            select.appendChild( option );
        } );

        select.addEventListener( 'change', () => {
            const raw = select.value;
            // Preserve numeric ivLoadPolicy
            this.data[ key ] = key === 'ivLoadPolicy' ? ( parseInt( raw, 10 ) as 1 | 3 ) : raw;
            if ( this.data.videoId || this.data.playlist ) {
                this.renderEmbed();
            }
        } );

        container.appendChild( fieldLabel );
        container.appendChild( select );
        return container;
    }

    private createTextField( label: string, key: string, placeholder: string ): HTMLElement {
        const container = document.createElement( 'div' );
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.padding = '6px 8px';
        container.style.gap = '4px';

        const fieldLabel = document.createElement( 'label' );
        fieldLabel.style.fontSize = '12px';
        fieldLabel.style.fontWeight = '500';
        fieldLabel.textContent = label;

        const input = document.createElement( 'input' );
        input.type = 'text';
        input.placeholder = placeholder;
        input.value = this.data[ key ] || '';
        input.style.width = '100%';
        input.style.padding = '4px';
        input.style.border = '1px solid #ddd';
        input.style.borderRadius = '3px';
        input.style.fontSize = '12px';

        input.addEventListener( 'change', () => {
            this.data[ key ] = input.value;
            if ( key === 'caption' ) {
                this.renderEmbed();
            } else if ( this.data.videoId || this.data.playlist ) {
                this.renderEmbed();
            }
        } );

        container.appendChild( fieldLabel );
        container.appendChild( input );
        return container;
    }

    private createSectionTitle( title: string ): HTMLElement {
        const el = document.createElement( 'div' );
        el.style.fontSize = '12px';
        el.style.fontWeight = 'bold';
        el.style.padding = '8px';
        el.style.color = '#666';
        el.textContent = title;
        return el;
    }

    private createSeparator(): HTMLElement {
        const separator = document.createElement( 'div' );
        separator.style.height = '1px';
        separator.style.backgroundColor = '#e0e0e0';
        separator.style.margin = '8px 0';
        return separator;
    }

    renderSettings() {
        const wrapper = document.createElement( 'div' );
        wrapper.style.maxHeight = '360px';
        wrapper.style.overflowY = 'auto';
        wrapper.style.minWidth = '220px';

        // Alignment
        const alignments = [
            { name: __( 'Align Left' ), value: 'left', icon: '<i class="las la-align-left text-lg"></i>' },
            { name: __( 'Align Center' ), value: 'center', icon: '<i class="las la-align-center text-lg"></i>' },
            { name: __( 'Align Right' ), value: 'right', icon: '<i class="las la-align-right text-lg"></i>' },
        ];

        alignments.forEach( tune => {
            const button = document.createElement( 'div' );
            button.classList.add( 'cdx-settings-button' );
            if ( this.align === tune.value ) {
                button.classList.add( 'cdx-settings-button--active' );
            }
            button.innerHTML = tune.icon;
            button.title = tune.name;
            button.addEventListener( 'click', () => {
                this.align = tune.value;
                wrapper.querySelectorAll( '.cdx-settings-button' ).forEach( btn => {
                    btn.classList.remove( 'cdx-settings-button--active' );
                } );
                button.classList.add( 'cdx-settings-button--active' );
            } );
            wrapper.appendChild( button );
        } );

        wrapper.appendChild( this.createSeparator() );
        wrapper.appendChild( this.createSectionTitle( __( 'Playback' ) ) );
        wrapper.appendChild( this.createToggle( __( 'Autoplay' ), 'autoplay', '<i class="las la-play"></i>' ) );
        wrapper.appendChild( this.createToggle( __( 'Mute' ), 'mute', '<i class="las la-volume-mute"></i>' ) );
        wrapper.appendChild( this.createToggle( __( 'Show Controls' ), 'controls', '<i class="las la-sliders-h"></i>' ) );
        wrapper.appendChild( this.createToggle( __( 'Loop' ), 'loop', '<i class="las la-redo"></i>' ) );
        wrapper.appendChild( this.createToggle( __( 'Play Inline (iOS)' ), 'playsinline', '<i class="las la-mobile"></i>' ) );

        wrapper.appendChild( this.createSeparator() );
        wrapper.appendChild( this.createSectionTitle( __( 'Player' ) ) );
        wrapper.appendChild( this.createToggle( __( 'Modest Branding' ), 'modestBranding', '<i class="las la-youtube"></i>' ) );
        wrapper.appendChild( this.createToggle( __( 'Related Videos' ), 'rel', '<i class="las la-list"></i>' ) );
        wrapper.appendChild( this.createToggle( __( 'Fullscreen Button' ), 'fs', '<i class="las la-expand"></i>' ) );
        wrapper.appendChild( this.createToggle( __( 'Disable Keyboard' ), 'disablekb', '<i class="las la-keyboard"></i>' ) );
        wrapper.appendChild( this.createToggle( __( 'Force Captions' ), 'ccLoadPolicy', '<i class="las la-closed-captioning"></i>' ) );
        wrapper.appendChild( this.createToggle( __( 'Privacy Mode' ), 'privacyMode', '<i class="las la-user-secret"></i>' ) );

        wrapper.appendChild( this.createSelectField( __( 'Annotations' ), 'ivLoadPolicy', [
            { label: __( 'Show' ), value: 1 },
            { label: __( 'Hide' ), value: 3 },
        ] ) );
        wrapper.appendChild( this.createSelectField( __( 'Progress Color' ), 'color', [
            { label: __( 'Red' ), value: 'red' },
            { label: __( 'White' ), value: 'white' },
        ] ) );

        wrapper.appendChild( this.createSeparator() );
        wrapper.appendChild( this.createSectionTitle( __( 'Timing' ) ) );
        wrapper.appendChild( this.createNumberField( __( 'Start (seconds)' ), 'start', '0' ) );
        wrapper.appendChild( this.createNumberField( __( 'End (seconds)' ), 'end', 'auto' ) );

        wrapper.appendChild( this.createSeparator() );
        wrapper.appendChild( this.createSectionTitle( __( 'Layout' ) ) );
        wrapper.appendChild( this.createSelectField( __( 'Aspect Ratio' ), 'aspectRatio', [
            { label: '16:9', value: '16:9' },
            { label: '4:3', value: '4:3' },
            { label: '1:1', value: '1:1' },
            { label: '9:16', value: '9:16' },
        ] ) );
        wrapper.appendChild( this.createNumberField( __( 'Width (px)' ), 'width', 'auto' ) );
        wrapper.appendChild( this.createNumberField( __( 'Height (px)' ), 'height', 'auto' ) );

        wrapper.appendChild( this.createSeparator() );
        wrapper.appendChild( this.createSectionTitle( __( 'Advanced' ) ) );
        wrapper.appendChild( this.createTextField( __( 'Language (hl)' ), 'hl', 'en' ) );
        wrapper.appendChild( this.createTextField( __( 'Playlist' ), 'playlist', 'videoId1,videoId2' ) );
        wrapper.appendChild( this.createTextField( __( 'Caption' ), 'caption', __( 'Optional caption' ) ) );

        // Change URL
        const changeUrlBtn = document.createElement( 'button' );
        changeUrlBtn.type = 'button';
        changeUrlBtn.style.fontSize = '11px';
        changeUrlBtn.style.padding = '6px 8px';
        changeUrlBtn.style.margin = '8px';
        changeUrlBtn.style.border = '1px solid #ddd';
        changeUrlBtn.style.borderRadius = '3px';
        changeUrlBtn.style.backgroundColor = '#f5f5f5';
        changeUrlBtn.style.cursor = 'pointer';
        changeUrlBtn.style.width = 'calc(100% - 16px)';
        changeUrlBtn.innerHTML = '<i class="las la-link" style="margin-right: 4px;"></i>' + __( 'Change URL' );
        changeUrlBtn.addEventListener( 'click', () => {
            this.showFormOnly();
            if ( this.urlInput ) {
                this.urlInput.value = this.data.url || '';
                this.urlInput.focus();
            }
        } );
        wrapper.appendChild( changeUrlBtn );

        return wrapper;
    }

    render() {
        this.wrapper = document.createElement( 'div' );
        this.wrapper.classList.add( 'ns-editor-youtube' );

        this.formWrapper = document.createElement( 'div' );
        this.formWrapper.classList.add( 'ns-editor-youtube-form' );

        this.embedWrapper = document.createElement( 'div' );
        this.embedWrapper.classList.add( 'ns-editor-youtube-embed', 'hide' );

        // URL input row
        const inputRow = document.createElement( 'div' );
        inputRow.classList.add( 'ns-editor-youtube-input-row' );

        this.urlInput = document.createElement( 'input' );
        this.urlInput.type = 'url';
        this.urlInput.placeholder = __( 'Paste a YouTube URL or video ID…' );
        this.urlInput.value = this.data.url || '';
        this.urlInput.classList.add( 'ns-editor-youtube-url' );

        const embedButton = document.createElement( 'button' );
        embedButton.type = 'button';
        embedButton.classList.add( 'ns-editor-youtube-embed-btn' );
        embedButton.textContent = __( 'Embed' );

        const submit = () => this.applyUrl( this.urlInput.value );

        embedButton.addEventListener( 'click', submit );
        this.urlInput.addEventListener( 'keydown', ( e ) => {
            if ( e.key === 'Enter' ) {
                e.preventDefault();
                submit();
            }
        } );

        inputRow.appendChild( this.urlInput );
        inputRow.appendChild( embedButton );
        this.formWrapper.appendChild( inputRow );

        const hint = document.createElement( 'div' );
        hint.classList.add( 'ns-editor-youtube-hint' );
        hint.textContent = __( 'Supports youtube.com, youtu.be, shorts, live, embed links and privacy-enhanced URLs.' );
        this.formWrapper.appendChild( hint );

        this.wrapper.appendChild( this.formWrapper );
        this.wrapper.appendChild( this.embedWrapper );

        // Restore saved block
        if ( this.data.videoId || this.data.playlist || this.data.url ) {
            if ( this.data.videoId || this.data.playlist ) {
                this.renderEmbed();
            } else if ( this.data.url ) {
                this.applyUrl( this.data.url );
            }
        }

        return this.wrapper;
    }

    /**
     * Persist full guide data used to configure the embed output.
     */
    save() {
        const embedUrl = this.buildEmbedUrl();

        return {
            // Identity
            url: this.data.url || '',
            videoId: this.data.videoId || '',
            service: 'youtube' as const,
            // Layout
            align: this.align || this.data.align || 'center',
            width: this.data.width,
            height: this.data.height,
            aspectRatio: this.data.aspectRatio || '16:9',
            caption: this.data.caption || '',
            // Player parameters
            autoplay: !! this.data.autoplay,
            mute: !! this.data.mute,
            controls: this.data.controls !== false,
            loop: !! this.data.loop,
            modestBranding: this.data.modestBranding !== false,
            rel: !! this.data.rel,
            fs: this.data.fs !== false,
            disablekb: !! this.data.disablekb,
            ccLoadPolicy: !! this.data.ccLoadPolicy,
            ivLoadPolicy: this.data.ivLoadPolicy === 3 ? 3 : 1,
            playsinline: this.data.playsinline !== false,
            privacyMode: this.data.privacyMode !== false,
            color: this.data.color === 'white' ? 'white' : 'red',
            start: this.data.start,
            end: this.data.end,
            hl: this.data.hl || '',
            playlist: this.data.playlist || '',
            // Ready-to-use embed URL for output rendering
            embedUrl,
        };
    }

    validate( savedData ) {
        return !!( savedData && ( savedData.videoId || savedData.playlist || savedData.url ) );
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
            youtube: Youtube,
            code: Code,
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