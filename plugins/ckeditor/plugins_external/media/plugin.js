/**
 * Media Widget
 *
 * @author Ayhan Akilli
 */
'use strict';

(function (window, document, CKEDITOR) {
    /**
     * Defaults
     */
    var defaults = {
        align: {left: 'left', right: 'right'},
        attr: ['alt', 'height', 'src', 'width']
    };

    /**
     * Plugin
     */
    CKEDITOR.plugins.add('media', {
        requires: 'api,dialog,widget',
        icons: 'media',
        hidpi: true,
        lang: 'de,en,uk,ru',
        init: function (editor) {
            var placeholder = this.path  + 'styles/placeholder.svg';

            /**
             * Widget
             */
            editor.widgets.add('media', {
                button: editor.lang.media.title,
                dialog: 'media',
                template: '<figure class="image"><img /><figcaption></figcaption></figure>',
                editables: {
                    caption: {
                        selector: 'figcaption',
                        allowedContent: {
                            a: {
                                attributes: {href: true},
                                requiredAttributes: {href: true}
                            },
                            br: true,
                            em: true,
                            strong: true
                        }
                    }
                },
                allowedContent: {
                    a: {
                        attributes: {href: true},
                        requiredAttributes: {href: true}
                    },
                    audio: {
                        attributes: {controls: true, src: true},
                        requiredAttributes: {controls: true, src: true}
                    },
                    figcaption: true,
                    figure: {
                        classes: {audio: true, iframe: true, image: true, left: true, right: true, video: true}
                    },
                    iframe: {
                        attributes: {allowfullscreen: true, height: true, src: true, width: true},
                        requiredAttributes: {src: true}
                    },
                    img: {
                        attributes: {alt: true, height: true, src: true, width: true},
                        requiredAttributes: {src: true}
                    },
                    video: {
                        attributes: {controls: true, height: true, src: true, width: true},
                        requiredAttributes: {src: true}
                    }
                },
                requiredContent: 'figure',
                defaults: {
                    align: '',
                    alt: '',
                    height: '',
                    link: '',
                    src: '',
                    type: '',
                    width: ''
                },
                upcast: function (el) {
                    var type = CKEDITOR.api.parser.isMedia(el);

                    if (!type) {
                        return false;
                    }

                    // Figure + Media
                    var fig = el;

                    if (el.name !== 'figure') {
                        fig = new CKEDITOR.htmlParser.element('figure', {'class' : type});
                        el.wrapWith(fig);
                    } else if (fig.children.length < 1 || !CKEDITOR.api.parser.isMediaElement(fig.children[0]) && !CKEDITOR.api.parser.isMediaLink(fig.children[0])) {
                        fig.attributes.class = 'image';
                        fig.add(new CKEDITOR.htmlParser.element('img', {'src' : placeholder}), 0);
                    }

                    // Caption
                    if (fig.children.length < 2 || fig.children[1].name !== 'figcaption') {
                        fig.add(new CKEDITOR.htmlParser.element('figcaption'), 1);
                    }

                    fig.children = fig.children.slice(0, 2);

                    return fig;
                },
                downcast: function (el) {
                    if (this.data.link && el.children[0].name === 'img') {
                        el.children[0].wrapWith(new CKEDITOR.htmlParser.element('a', {'href': this.data.link}));
                    }

                    if (!el.children[1].getHtml().trim()) {
                        el.children[1].remove();
                    } else {
                        el.children[1].attributes = [];
                    }
                },
                init: function () {
                    var widget = this;
                    var el = widget.element;
                    var media = el.getFirst();

                    // Figure
                    if (el.hasClass(defaults.align.left)) {
                        widget.setData('align', 'left');
                    } else if (el.hasClass(defaults.align.right)) {
                        widget.setData('align', 'right');
                    }

                    // Link
                    if (media.getName() === 'a') {
                        widget.setData('link', media.getAttribute('href'));
                        media.getChild(0).move(el, true);
                        media.remove();
                        media = el.getFirst();
                    }

                    // Media
                    if (media.hasAttribute('src')) {
                        media.setAttribute('src', CKEDITOR.api.url.root(media.getAttribute('src')));
                        widget.setData('type', CKEDITOR.api.media.fromElement(media.getName()));
                    }

                    defaults.attr.forEach(function (item) {
                        if (media.hasAttribute(item)) {
                            widget.setData(item, media.getAttribute(item));
                        }
                    });
                },
                data: function () {
                    var widget = this;
                    var el = widget.element;
                    var media = el.getChild(0);
                    var type = widget.data.type;
                    var name;

                    if (!widget.data.src || !type || !(name = CKEDITOR.api.media.element(type))) {
                        return;
                    }

                    // Figure
                    CKEDITOR.api.media.all().concat([defaults.align.left, defaults.align.right]).forEach(function (item) {
                        el.removeClass(item);
                    });
                    el.addClass(type);

                    if (widget.data.align && defaults.align.hasOwnProperty(widget.data.align)) {
                        el.addClass(defaults.align[widget.data.align]);
                    }

                    // Media
                    if (media.getName() !== name) {
                        media.renameNode(name);
                    }

                    defaults.attr.forEach(function (item) {
                        if (!!widget.data[item] && (item !== 'alt' || type === 'image') && (['height', 'width'].indexOf(item) < 0 || type !== 'audio')) {
                            media.setAttribute(item, widget.data[item]);
                        } else {
                            media.removeAttribute(item);
                        }
                    });
                    ['allowfullscreen', 'controls'].forEach(function (item) {
                        if (item === 'allowfullscreen' && type === 'iframe' || item === 'controls' && ['audio', 'video'].indexOf(type) >= 0) {
                            media.setAttribute(item, item);
                        } else {
                            media.removeAttribute(item);
                        }
                    });
                }
            });

            /**
             * Dialog
             */
            CKEDITOR.dialog.add('media', this.path + 'dialogs/media.js');
        },
        onLoad: function () {
            CKEDITOR.addCss(
                'figure.audio, figure.iframe, figure.image, figure.video {line-height: 1.5rem;text-align: center;}' +
                'figure.audio.left, figure.iframe.left, figure.image.left, figure.video.left {float: left;margin-right: 0.75rem;text-align: left;}' +
                'figure.audio.right, figure.iframe.right, figure.image.right, figure.video.right {float: right;margin-left: 0.75rem;text-align: left;}' +
                'figure.audio audio, figure.iframe iframe, figure.image img, figure.video video {display: block;margin: 0 auto 0.75rem;}' +
                'figure.audio audio, figure.iframe iframe, figure.video video {pointer-events: none;}' +
                'figure.audio figcaption, figure.iframe figcaption, figure.image figcaption, figure.video figcaption {font-size: 0.875rem;background: #eee;}' +
                'figure.audio .cke_widget_editable, figure.iframe .cke_widget_editable, figure.image .cke_widget_editable, figure.video .cke_widget_editable {outline: none !important;}'
            );
        }
    });

    /**
     * Dialog definition
     */
    CKEDITOR.on('dialogDefinition', function (ev) {
        if (ev.data.name !== 'media') {
            return;
        }

        /**
         * Type select
         */
        var type = ev.data.definition.contents[0].elements[0];
        type.items = [[ev.editor.lang.common.notSet, '']].concat(CKEDITOR.api.media.all().map(function (item) {
            return [ev.editor.lang.media[item], item];
        }).sort(function (a, b) {
            if (a[0] < b[0]) {
                return -1;
            }

            if (a[0] > b[0]) {
                return 1;
            }

            return 0;
        }));

        /**
         * Source input
         */
        var src = ev.data.definition.contents[0].elements[1];
        src.onChange = function () {
            var type = '';

            if (this.getValue()) {
                type = CKEDITOR.api.media.fromUrl(this.getValue()) || '';
            }

            this.getDialog().getContentElement('info', 'type').setValue(type);
        };

        /**
         * Browse button
         */
        var browse = ev.data.definition.contents[0].elements[2];
        var call = function (data) {
            if (data.src) {
                var dialog = this.getDialog();

                ['src', 'type', 'alt', 'width', 'height'].forEach(function (item) {
                    if (!!data[item]) {
                        dialog.getContentElement('info', item).setValue(data[item]);
                    }
                });
            }
        };

        // Supported APIs sorted by preference
        if (!!ev.editor.plugins.browser && typeof ev.editor.config.mediaBrowser === 'string' && !!ev.editor.config.mediaBrowser) {
            browse.browser = call;
            browse.browserUrl = ev.editor.config.mediaBrowser;
        } else if (!!ev.editor.plugins.mediabrowser) {
            browse.mediabrowser = call;
        } else if (!!ev.editor.plugins.filebrowser) {
            browse.filebrowser = 'info:src';
        }
    }, null, null, 1);
})(window, document, CKEDITOR);
