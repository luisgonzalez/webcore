<?php
/**
 * Samples Page template example.
 * Use the WebPage->getCurrent() to get the currently active instance of the WebPage
 * sually, you want to extend the WebPage class for more complex scnarios.
 * @version 1.0.0
 * @author Mario Di Vece <mario@unosquare.com>
 */
HttpResponse::write(MarkupWriter::DTD_XHTML_STRICT . "\r\n");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <?php
            HtmlViewManager::render();
        ?>
        <style type="text/css">
            body
            {
                padding: 0;
                margin: 0;
                font-family: verdana, arial, sans-serif;
                font-size: 12px;
            }
            table
            {
                width: 100%;
            }

            table, table td
            {
                padding: 0;
                margin: 0;
                border-style: none;
                border-spacing: 0;
                border-collapse: collapse;
                text-align: left;
                vertical-align: top;
            }

            div.filename-caption, div.author-caption, div.tutorial-caption
            {
                float: none;
                white-space: normal;
                clear: none;
                text-align: left;
            }

            div.filename-value, div.author-value, div.tutorial-value
            {
                float: none;
                white-space: normal;
                clear: none;
            }
        </style>
        <title><?php echo WebPage::getCurrent()->getTitle(); ?></title>
    </head>
    <body>
        <table summary="">
            <tbody>
                <tr>
                    <td style="width: 250px; padding: 4px;">
<?php
if (WebPage::getCurrent()->getPlaceholders()->keyExists('left'))
{
    WebPage::getCurrent()->getPlaceholder('left')->render();
}
?>
                    </td>
                    <td style="padding: 4px;">
<?php
if (WebPage::getCurrent()->getPlaceholders()->keyExists('right'))
{
    WebPage::getCurrent()->getPlaceholder('right')->render();
}
?>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
