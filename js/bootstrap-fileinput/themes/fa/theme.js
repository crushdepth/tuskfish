/*!
 * bootstrap-fileinput v4.4.7
 * http://plugins.krajee.com/file-input
 *
 * Font Awesome icon theme configuration for bootstrap-fileinput. Requires font awesome assets to be loaded.
 *
 * Author: Kartik Visweswaran
 * Copyright: 2014 - 2018, Kartik Visweswaran, Krajee.com
 *
 * Licensed under the BSD 3-Clause
 * https://github.com/kartik-v/bootstrap-fileinput/blob/master/LICENSE.md
 */
(function ($) {
    "use strict";

    $.fn.fileinputThemes.fa = {
        fileActionSettings: {
            removeIcon: '<i class="fas fa-trash-alt"></i>',
            uploadIcon: '<i class="fas fa-upload"></i>',
            uploadRetryIcon: '<i class="fas fa-repeat"></i>',
            zoomIcon: '<i class="fas fa-search-plus"></i>',
            dragIcon: '<i class="fas fa-bars"></i>',
            indicatorNew: '<i class="fas fa-plus-circle text-warning"></i>',
            indicatorSuccess: '<i class="fas fa-check-circle text-success"></i>',
            indicatorError: '<i class="fas fa-exclamation-circle text-danger"></i>',
            indicatorLoading: '<i class="fas fa-hourglass text-muted"></i>'
        },
        layoutTemplates: {
            fileIcon: '<i class="fas fa-file"></i> '
        },
        previewZoomButtonIcons: {
            prev: '<i class="fas fa-caret-left fa-lg"></i>',
            next: '<i class="fas fa-caret-right fa-lg"></i>',
            toggleheader: '<i class="fas fa-arrows-v"></i>',
            fullscreen: '<i class="fas fa-arrows-alt"></i>',
            borderless: '<i class="fas fa-external-link"></i>',
            close: '<i class="fas fa-times"></i>'
        },
        previewFileIcon: '<i class="fas fa-file"></i>',
        browseIcon: '<i class="fas fa-folder-open"></i>',
        removeIcon: '<i class="fas fa-trash-alt"></i>',
        cancelIcon: '<i class="fas fa-ban"></i>',
        uploadIcon: '<i class="fas fa-upload"></i>',
        msgValidationErrorIcon: '<i class="fas fa-exclamation-circle"></i> '
    };
})(window.jQuery);
