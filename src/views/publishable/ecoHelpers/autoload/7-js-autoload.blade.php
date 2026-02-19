{{--
 * The autoload file associated with the eco-helpers config file array
 * entry for 'auto_loaders' => [7 => 'name']
 *
 *  Usage (available in the controller as):
 *     ehLayout::setAutoload('name'); or ehLayout::setAutoload(7);
 *
 --}}

{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #7
    ///////////////////////////////////////////////////////////////////////////////////////////
    TinyMCE rich text editor. --}}

    <script type="text/javascript" src="{{ config('path.TINYMCE')}}/tinymce.js"></script>
    <script type="text/javascript" class="init">

        // Calling page can set any of these vars ahead of time to specify the toolbar to use
        if (typeof toolbarsetup === "undefined") {
            toolbarsetup = "undo redo | bold italic | bullist link";
        }
        if (typeof menubarsetup === "undefined") {
            menubarsetup = false;
        }
        if (typeof contentcsssetup === "undefined") {
            contentcsssetup = false;
        }
        if (typeof pluginssetup === "undefined") {
            pluginssetup = 'link paste';
        }
        if (typeof content_height === "undefined") {
            content_height = '280';
        }

        tinymce.init({
            selector: '#texteditor',
            //content_style: ".mce-content-body {font-size:.8em;color:darkgrey;}",
            content_style: ".mce-content-body {font-size:.8em;}",
            theme: "silver",
            toolbar: toolbarsetup,
            menubar: menubarsetup,
            contentcsssetup: contentcsssetup,
            plugins: pluginssetup,
            height: content_height,
            default_link_target: '_blank',
            paste_data_images: true,
            importcss_append: true,
            branding: false,
            relative_urls: false
        });
    </script>

