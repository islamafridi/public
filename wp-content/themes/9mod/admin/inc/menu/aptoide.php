<?php
function apkt_apt_importer()
{
    $apt_is_advanced_options = at_options('apt_is_advanced_options', false);
    $apt_post_status = at_options('apt_post_status', 'draft');
    $apt_post_title_start = at_options('apt_post_title_start');
    $apt_post_title_end = at_options('apt_post_title_end');
    $apt_mod_feature = at_options('apt_mod_feature');
    $apt_post_thumbnail_quality = at_options('apt_post_thumbnail_quality', '512');
    $apt_import_screenshots = at_options('apt_import_screenshots', false);
    $is_get_apk = at_options('is_get_apk', false);
    ?>
<div id="at-importer">
    <div class="at-importer-container">
        <form method="POST" id="at-apt-importer-form">
            <div class="at-importer at-mb-3">
                <h2>Aptoide Importer</h2>
                <div class="at-inline-ipt at-mt-2">
                    <input type="url" name="at_apt_url" id="at-apt-url" class="at-ipt-url" min="3"
                        placeholder="https://clash-of-clans.en.aptoide.com/app" required />
                    <button type="submit" class="at-btn at-btn-orange">
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
                            <input type="checkbox" name="at_apt_advanced_options" id="at_apt_advanced_options"
                                <?php checked($apt_is_advanced_options);?>>
                            <span class="at-switch at-apt-switch"></span>
                        </label>
                    </div>
                </div>
                <table class="at-import-table at-mt-2" <?php if (!$apt_is_advanced_options) {
        echo 'style="display: none"';
    }
    ?>>
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
                                <select name="at_apt_post_status" class="at-select">
                                    <option value="draft" <?php selected($apt_post_status, 'draft');?>>Draft</option>
                                    <option value="publish" <?php selected($apt_post_status, 'publish');?>>Publish
                                    </option>
                                </select>
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Post title starting text', 'apktemplates');?>
                                </h3>
                                <input type="text" name="at_apt_post_title_start" class="at-ipt-text"
                                    value="<?php echo esc_attr($apt_post_title_start); ?>"
                                    placeholder="Eg. [Start text] Spotify MOD APK" />
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Post title ending text', 'apktemplates');?>
                                </h3>
                                <input type="text" name="at_apt_post_title_end" class="at-ipt-text"
                                    value="<?php echo esc_attr($apt_post_title_end); ?>"
                                    placeholder="Eg. Spotify MOD APK [End text]" />
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('MOD Feature', 'apktemplates');?>
                                </h3>
                                <input type="text" name="at_apt_mod_feature" class="at-ipt-text"
                                    value="<?php echo esc_attr($apt_mod_feature); ?>"
                                    placeholder="Eg. Premium Unlocked">
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Import Screenshots', 'apktemplates');?>
                                </h3>
                                <div class="at-btn-container">
                                    <label class="at-switch-btn">
                                        <input type="checkbox" name="at_apt_import_screenshots"
                                            id="at_apt_import_screenshots" <?php checked($apt_import_screenshots);?>>
                                        <span class="at-switch at-apt-switch"></span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Thumbnail quality', 'apktemplates');?>
                                </h3>
                                <select name="at_apt_post_thumbnail_quality" class="at-select">
                                    <option value="raw" <?php selected($apt_post_thumbnail_quality, 'raw');?>>Original
                                    </option>
                                    <option value="512" <?php selected($apt_post_thumbnail_quality, '512');?>>Large
                                        (512x512)</option>
                                    <option value="256" <?php selected($apt_post_thumbnail_quality, '256');?>>Medium
                                        (256x256)</option>
                                    <option value="128" <?php selected($apt_post_thumbnail_quality, '128');?>>Small
                                        (128x128) Recommeded</option>
                                </select>
                            </td>
                            <td>
                                <h3>
                                    <?php esc_html_e('Import APK file', 'apktemplates');?>
                                </h3>
                                <div class="at-btn-container">
                                    <label class="at-switch-btn">
                                        <input type="checkbox" name="is_get_apk" id="is_get_apk"
                                            <?php checked($is_get_apk);?>>
                                        <span class="at-switch at-apt-switch"></span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
        <div class="at-apk-search">
            <div class="at-search-title">
                <h2>Search Games & Apps</h2>
            </div>
            <div class="at-inline-ipt at-mt-2 at-mb-2">
                <input type="url" id="at-apt-search-query" class="at-ipt-url" min="3" placeholder="E.g Spotify"
                    required />
                <button id="at-apt-search-submit" class="at-btn at-btn-orange">
                    Search
                </button>
            </div>
            <div id="at-apt-apk-results">
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