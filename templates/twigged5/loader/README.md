## Replacing templates for epub-loader

If you want to override the standard templates of epub-loader, you can place
them here (or anywhere else really), and update $gConfig['template_dir'] in
`config/loader.php`

Every action typically has its own template that extends [index.html](index.html)
and defines its own 'content' block.

Check the [epub-loader templates](https://github.com/mikespub-org/epub-loader/tree/main/templates)
folder for specific action templates you want to re-use. Unlike the 'twigged' templates in COPS
itself, you don't need to use it.* for every variable.

Note: they're also here under vendor/mikespub/epub-loader/templates if you installed the package.
