(function($, window, document) {
    'use strict';

    var imagesWrapper = $('#meta-box-image-cbpw'),
        inputImages = $('#cbp_project_images'),
        jsonInput = (inputImages.val()) ? inputImages.val() : '[]',
        imagesArr = JSON.parse(jsonInput);

    var media = {
        image: function(item) {
            var html = '<img src="' + item.url + '" alt="" width="200">';
            return media.buildWrapper(item.id, html);
        },

        youtube: function(item) {
            var html = '<iframe src="' + item.url + '" frameborder="0" allowfullscreen scrolling="no" width="200"></iframe>';
            return media.buildWrapper(item.id, html);
        },

        vimeo: function(item) {
            var html = '<iframe src="' + item.url + '" frameborder="0" allowfullscreen scrolling="no" width="200"></iframe>';
            return media.buildWrapper(item.id, html);
        },

        ted: function(item) {
            var html = '<iframe src="' + item.url + '" frameborder="0" allowfullscreen scrolling="no" width="200"></iframe>';
            return media.buildWrapper(item.id, html);
        },

        soundcloud: function(item) {
            var html = '<iframe src="' + item.url + '" frameborder="0" allowfullscreen scrolling="no" width="200"></iframe>';
            return media.buildWrapper(item.id, html);
        },

        selfhostedvideo: function(item) {
            var html = '<video controls="controls" height="auto" style="width: 200px">';
            var url = item.url;

            for (var i = 0; i < url.length; i++) {
                if (/(\.mp4)/i.test(url[i])) {
                    html += '<source src="' + url[i] + '" type="video/mp4">';
                } else if (/(\.ogg)|(\.ogv)/i.test(url[i])) {
                    html += '<source src="' + url[i] + '" type="video/ogg">';
                } else if (/(\.webm)/i.test(url[i])) {
                    html += '<source src="' + url[i] + '" type="video/webm">';
                }
            }

            html += 'Your browser does not support the video tag.' +
                '</video>';

            return media.buildWrapper(item.id, html);
        },

        selfhostedaudio: function(item) {
            var html = '<audio controls="controls" height="auto" style="width: 200px">' +
                '<source src="' + item.url + '" type="audio/mpeg">' +
                'Your browser does not support the audio tag.' +
                '</audio>';

            return media.buildWrapper(item.id, html);
        },

        buildWrapper: function(id, html) {
            return '<div class="meta-box-image-cbpw-item cbpw-controls-action-trigger" data-cbpw-id="' + id + '"><div class="cbpw-controls-action"> <div class="cbpw-controls-action-wrap"> <div class="cbpw-controls-action-edit" title="edit this item" data-cbpw-action="edit"></div> <div class="cbpw-controls-action-delete" title="delete" data-cbpw-action="delete"></div> <div class="cbpw-controls-action-drag" title="drag this item" data-cbpw-drag></div> </div> </div>' + html + '</div>';
        },
    };

    printGallery();

    // edit & delete actions
    imagesWrapper.on('click', '[data-cbpw-action]', function(e) {
        e.preventDefault();

        var obj = getObjectAndIndex($(this).closest('[data-cbpw-id]').attr('data-cbpw-id'));

        if (this.getAttribute('data-cbpw-action') === 'edit') {
            if (obj.item.type === 'image') {
                editImage(obj.item, obj.index);
            } else {
                $info.find('input').val(obj.item.id);
                $info.dialog('open');
            }
        } else if (this.getAttribute('data-cbpw-action') === 'delete') {
            removeItem(obj.index);
            refreshGallery();
        }
    });

    // add new image
    $('.meta-box-image-add-button-cbpw').on('click', function(e) {
        e.preventDefault();

        // create new media frame
        // You have to create new frame every time to control the Library state as well as selected images
        var wp_media_frame = wp.media.frames.wp_media_frame = wp.media({
            title: 'My Gallery', // it has no effect but I really want to change the title
            frame: "post",
            toolbar: 'main-gallery',
            state: 'gallery-library',
            library: {
                type: 'image'
            },
            multiple: true
        });

        // when click Insert Gallery, run callback
        wp_media_frame.on('update', function() {
            wp_media_frame.state().get('library').each(function(image) {
                var json = image.toJSON();
                imagesArr.push({
                    type: 'image',
                    url: json.url,
                    id: json.id
                });
            });

            refreshGallery();
        });

        wp_media_frame.open();
    });


    var $info = $("#modal-content");
    var $input = $("#modal-content").find('input');
    $info.dialog({
        dialogClass: 'wp-dialog',
        modal: true,
        autoOpen: false,
        closeOnEscape: true,
        draggable: false,
        resizable: false,
        width: 700,
        open: function(event, ui) {
            $(this).data('id', $input.val());
        },
        close: function(event, ui) {
            $input.val('');
        },
        buttons: [{
            text: 'Save',
            click: function() {
                var t = $(this);
                var obj = getObjectAndIndex(t.data('id'));

                var newId = $input.val();
                var newObj = getURLTypeForVideo(newId);

                if (obj !== null) {
                    imagesArr[obj.index] = {
                        type: newObj.type,
                        id: newId,
                        url: newObj.url,
                    };
                } else {
                    imagesArr.push({
                        type: newObj.type,
                        id: newId,
                        url: newObj.url,
                    });
                }

                refreshGallery();

                t.dialog('close');
            }
        }, {
            text: 'Close',
            click: function() {
                $(this).dialog('close');
            }
        }],
    });

    // add new video/sound
    $('.meta-box-image-add-video-cbpw').on('click', function(e) {
        e.preventDefault();

        $info.dialog('open');
    });

    function printGallery() {
        if (imagesWrapper.sortable('instance')) {
            imagesWrapper.sortable('destroy');
        }

        imagesWrapper.html('');

        $.each(imagesArr, function(index, item) {
            var html = media[item.type](item);
            imagesWrapper.append(html);
        });

        imagesWrapper.sortable({
            containment: 'parent',
            cursor: 'move',
            handle: '[data-cbpw-drag]',
            opacity: .7,
            cursorAt: { top: 0},
            revert: true,
            stop: function(event, ui) {
                var newArr = [];

                imagesWrapper.children().each(function(index, el) {
                    var $el = $(el);

                    var obj = getObjectAndIndex(el.getAttribute('data-cbpw-id'));

                    newArr.push(obj.item);
                });

                imagesArr = newArr;

                inputImages.val(JSON.stringify(imagesArr));
            },
        });
    }

    function refreshGallery() {
        printGallery();

        inputImages.val(JSON.stringify(imagesArr));
    }

    function getObjectAndIndex(id) {
        for (var i = 0; i < imagesArr.length; i++) {
            if (imagesArr[i].id == id) {
                return {
                    item: imagesArr[i],
                    index: i
                };
            }
        }

        return null;
    }

    function removeItem(index) {
        imagesArr.splice(index, 1);
    }

    function editImage(item, index) {
        // create new media frame
        // You have to create new frame every time to control the Library state as well as selected images
        var wp_media_frame = wp.media.frames.wp_media_frame = wp.media({
            title: 'My Gallery', // it has no effect but I really want to change the title
            frame: "post",
            toolbar: 'main-gallery',
            state: 'gallery-edit',
            library: {
                type: 'image'
            },
            multiple: true
        });

        // when open media frame, add the selected image to Gallery
        wp_media_frame.on('open', function() {
            var attachment = wp.media.attachment(item.id);
            attachment.fetch();
            wp_media_frame.state().get('library').add(attachment ? [attachment] : []);
        });

        // when click Insert Gallery, run callback
        wp_media_frame.on('update', function() {

            removeItem(index);

            wp_media_frame.state().get('library').each(function(image, i) {
                var json = image.toJSON();

                var obj = {
                    type: 'image',
                    url: json.url,
                    id: json.id
                };

                imagesArr.splice(index + i, 0, obj);
            });

            refreshGallery();
        });

        wp_media_frame.open();
    }

    function getURLTypeForVideo(url) {
        var videoLink;
        var type;

        if (/youtube/i.test(url)) {
            videoLink = url.substring(url.lastIndexOf('v=') + 2);

            videoLink = videoLink.replace(/\?|&/, '?');

            // create new url
            url = '//www.youtube.com/embed/' + videoLink;

            type = 'youtube';
        } else if (/vimeo\.com/i.test(url)) {
            videoLink = url.substring(url.lastIndexOf('/') + 1);

            videoLink = videoLink.replace(/\?|&/, '?');

            // create new url
            url = '//player.vimeo.com/video/' + videoLink;

            type = 'vimeo';
        } else if (/www\.ted\.com/i.test(url)) {
            // create new url
            url = 'http://embed.ted.com/talks/' + url.substring(url.lastIndexOf('/') + 1) + '.html';

            type = 'ted';
        } else if (/soundcloud\.com/i.test(url)) {
            // create new url
            url = url;

            type = 'soundcloud';
        } else if (/(\.mp4)|(\.ogg)|(\.ogv)|(\.webm)/i.test(url)) {
            if (url.indexOf('|') !== -1) {
                // create new url
                url = url.split('|');
            } else {
                // create new url
                url = url.split('%7C');
            }
            type = 'selfhostedvideo';
        } else if (/\.mp3$/i.test(url)) {
            url = url;
            type = 'selfhostedaudio';
        }

        return {
            url: url,
            type: type,
        }
    }
})(jQuery, window, document, undefined);
