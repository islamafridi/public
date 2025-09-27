<?php
function apkt_gp_importer()
{
    $is_advanced_options = at_options('is_advanced_options', false);
    $post_status = at_options('post_status', 'draft');
    $post_title_start = at_options('post_title_start');
    $post_title_end = at_options('post_title_end');
    $mod_feature = at_options('mod_feature');
    $post_thumbnail_format = at_options('post_thumbnail_format', 'png');
    $post_thumbnail_quality = at_options('post_thumbnail_quality', 'large');
    $import_screenshots = at_options('import_screenshots', false);
    $post_screenshots_format = at_options('post_screenshots_format', 'jpg');
    $post_language = at_options('post_language', 'en-US');
    ?>
<div id="at-importer">
    <div class="at-importer-container">
        <form method="POST" id="at-gp-importer-form">
            <div class="at-importer at-mb-3">
                <h2>Google Play Importer</h2>
                <div class="at-inline-ipt at-mt-2">
                    <input type="url" name="at_gp_url" id="at-gp-url" class="at-ipt-url" min="3"
                        placeholder="https://play.google.com/store/apps/details?id=com.whatsapp" required />
                    <button type="submit" class="at-btn at-btn-success">
                        Import Content
                    </button>
                </div>
            </div>
            <div class="at-importer-results at-mb-3" style="display: none">
                <h3 class="at-mb-1">Log</h3>
                <ul class="process-log at-mb-1" style="display: none">

                </ul>
                <ul class="process-error-log" style="display: none">

                </ul>
            </div>
            <div class="at-importer-advance at-mb-3 ">
                <div class="at-inline-container">
                    <h2>Advanced Options</h2>
                    <div>
                        <label class="at-switch-btn">
                            <input type="checkbox" name="at_advanced_options" id="at_advanced_options"
                                <?php checked($is_advanced_options);?>>
                            <span class="at-switch"></span>
                        </label>
                    </div>
                </div>
                <table class="at-import-table at-mt-2" <?php if (!$is_advanced_options) { echo 'style="display: none"'; } ?>>
                    <tbody>
                        <tr style="grid-template-columns: 1fr">
                            <td>
                                <p>
                                    <code><strong>Note: </strong> If you enabled advanced options, 
                                    it will take some extra time to import content. Because of 
                                    import screenshots to your server and import apk files to 
                                    the selected server.
                                    </code>
                                </p>
							</td>
						</tr>
                        <tr>
                            <td>
                                <h3>
                                    <?php esc_html_e('Post status', 'apktemplates');?>
                                </h3>
                                <select name="at_post_status" class="at-select">
                                    <option value="draft" <?php selected($post_status, 'draft'); ?>>Draft</option>
                                    <option value="publish" <?php selected($post_status, 'publish'); ?>>Publish</option>
                                </select>
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Post title starting text', 'apktemplates');?>
                                </h3>
                                <input type="text" name="at_post_title_start" class="at-ipt-text"
                                    value="<?php echo esc_attr($post_title_start); ?>"
                                    placeholder="Eg. [Start text] Spotify MOD APK" />
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Post title ending text', 'apktemplates');?>
                                </h3>
                                <input type="text" name="at_post_title_end" class="at-ipt-text"
                                    value="<?php echo esc_attr($post_title_end); ?>"
                                    placeholder="Eg. Spotify MOD APK [End text]" />
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('MOD Feature', 'apktemplates');?>
                                </h3>
                                <input type="text" name="at_mod_feature" class="at-ipt-text"
                                    value="<?php echo esc_attr($mod_feature); ?>"
                                    placeholder="Eg. Premium Unlocked">
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Thumbnail format', 'apktemplates');?>
                                </h3>
                                <select name="at_post_thumbnail_format" class="at-select">
                                    <option value="png" <?php selected($post_thumbnail_format, 'png'); ?>>PNG (.png)</option>
                                    <option value="webp" <?php selected($post_thumbnail_format, 'webp'); ?>>WEBP (.webp)</option>
                                </select>
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Thumbnail quality', 'apktemplates');?>
                                </h3>
                                <select name="at_post_thumbnail_quality" class="at-select">
                                    <option value="raw" <?php selected($post_thumbnail_quality, 'raw'); ?>>Original</option>
                                    <option value="512" <?php selected($post_thumbnail_quality, '512'); ?>>Large (512x512)</option>
                                    <option value="256" <?php selected($post_thumbnail_quality, '256'); ?>>Medium (256x256)</option>
                                    <option value="128" <?php selected($post_thumbnail_quality, '128'); ?>>Small (128x128) Recommeded</option>
                                </select>
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Import Screenshots', 'apktemplates');?>
                                </h3>
                                <div class="at-btn-container">
                                    <label class="at-switch-btn">
                                        <input type="checkbox" name="at_import_screenshots" id="at_import_screenshots"
                                            <?php checked($import_screenshots);?>>
                                        <span class="at-switch"></span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Screenshots format', 'apktemplates');?>
                                </h3>
                                <select name="at_post_screenshots_format" class="at-select">
                                    <option value="jpg" <?php selected($post_screenshots_format, 'jpg'); ?>>JPEG (.jpg)</option>
                                    <option value="webp" <?php selected($post_screenshots_format, 'webp'); ?>>WEBP (.webp)</option>
                                </select>
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Post language', 'apktemplates');?>
                                </h3>
                                <select name="at_post_language" class="at-select">
                                    <option value="af" <?php selected($post_language, 'af');?>>Afrikaans</option>
                                    <option value="am" <?php selected($post_language, 'am');?>>Amharic</option>
                                    <option value="bg" <?php selected($post_language, 'bg');?>>Bulgarian</option>
                                    <option value="ca" <?php selected($post_language, 'ca');?>>Catalan</option>
                                    <option value="zh-HK" <?php selected($post_language, 'zh-HK');?>>Chinese (Hong Kong)
                                    </option>
                                    <option value="zh-CN" <?php selected($post_language, 'zh-CN');?>>Chinese (PRC)</option>
                                    <option value="zh-TW" <?php selected($post_language, 'zh-TW');?>>Chinese (Taiwan)
                                    </option>
                                    <option value="hr" <?php selected($post_language, 'hr');?>>Croatian</option>
                                    <option value="cs" <?php selected($post_language, 'cs');?>>Czech</option>
                                    <option value="da" <?php selected($post_language, 'da');?>>Danish</option>
                                    <option value="nl" <?php selected($post_language, 'nl');?>>Dutch</option>
                                    <option value="en-GB" <?php selected($post_language, 'en-GB');?>>English (UK)</option>
                                    <option value="en-US" <?php selected($post_language, 'en-US');?>>English (US)</option>
                                    <option value="et" <?php selected($post_language, 'et');?>>Estonian</option>
                                    <option value="fil" <?php selected($post_language, 'fil');?>>Filipino</option>
                                    <option value="fi" <?php selected($post_language, 'fi');?>>Finnish</option>
                                    <option value="fr-CA" <?php selected($post_language, 'fr-CA');?>>French (Canada)
                                    </option>
                                    <option value="fr-FR" <?php selected($post_language, 'fr-FR');?>>French (France)
                                    </option>
                                    <option value="de" <?php selected($post_language, 'de');?>>German</option>
                                    <option value="el" <?php selected($post_language, 'el');?>>Greek</option>
                                    <option value="he" <?php selected($post_language, 'he');?>>Hebrew</option>
                                    <option value="hi" <?php selected($post_language, 'hi');?>>Hindi</option>
                                    <option value="hu" <?php selected($post_language, 'hu');?>>Hungarian</option>
                                    <option value="is" <?php selected($post_language, 'is');?>>Icelandic</option>
                                    <option value="id" <?php selected($post_language, 'id');?>>Indonesian</option>
                                    <option value="it" <?php selected($post_language, 'it');?>>Italian</option>
                                    <option value="ja" <?php selected($post_language, 'ja');?>>Japanese</option>
                                    <option value="ko" <?php selected($post_language, 'ko');?>>Korean</option>
                                    <option value="lv" <?php selected($post_language, 'lv');?>>Latvian</option>
                                    <option value="lt" <?php selected($post_language, 'lt');?>>Lithuanian</option>
                                    <option value="ms" <?php selected($post_language, 'ms');?>>Malay</option>
                                    <option value="no" <?php selected($post_language, 'no');?>>Norwegian</option>
                                    <option value="pl" <?php selected($post_language, 'pl');?>>Polish</option>
                                    <option value="pt-BR" <?php selected($post_language, 'pt-BR');?>>Portuguese (Brazil)
                                    </option>
                                    <option value="pt-PT" <?php selected($post_language, 'pt-PT');?>>Portuguese (Portugal)
                                    </option>
                                    <option value="ro" <?php selected($post_language, 'ro');?>>Romanian</option>
                                    <option value="ru" <?php selected($post_language, 'ru');?>>Russian</option>
                                    <option value="sr" <?php selected($post_language, 'sr');?>>Serbian</option>
                                    <option value="sk" <?php selected($post_language, 'sk');?>>Slovak</option>
                                    <option value="sl" <?php selected($post_language, 'sl');?>>Slovenian</option>
                                    <option value="es-419" <?php selected($post_language, 'es-419');?>>Spanish (Latin
                                        America)</option>
                                    <option value="es-ES" <?php selected($post_language, 'es-ES');?>>Spanish (Spain)
                                    </option>
                                    <option value="sw" <?php selected($post_language, 'sw');?>>Swahili</option>
                                    <option value="sv" <?php selected($post_language, 'sv');?>>Swedish</option>
                                    <option value="th" <?php selected($post_language, 'th');?>>Thai</option>
                                    <option value="tr" <?php selected($post_language, 'tr');?>>Turkish</option>
                                    <option value="uk" <?php selected($post_language, 'uk');?>>Ukrainian</option>
                                    <option value="vi" <?php selected($post_language, 'vi');?>>Vietnamese</option>
                                    <option value="zu" <?php selected($post_language, 'zu');?>>Zulu</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
        <div class="at-apk-search">
            <div class="at-search-title">
                <h2>Search</h2>
            </div>
            <div class="at-inline-ipt at-mt-2 at-mb-2">
                <input type="url" id="at-gp-search-query" class="at-ipt-url" min="3" placeholder="E.g Spotify" required />
                <button id="at-gp-search-submit" class="at-btn at-btn-success">
                    Search
                </button>
            </div>
            <div id="at-gp-results">
                <div class="at-apk-results-container">
                    <div class="at-apk-no-results">
                        <p>Search your favorite Games & Apps</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}