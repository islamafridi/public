<?php
/**
 * Displays the translations page for acf
 */

if ( ! defined( 'ABSPATH' ) ) {exit;} // Don't access directly
use Falang\Core\Falang_Core;
use Falang\Model\Falang_Model;
use Falang\Translator\TranslatorYandex;
use Falang\Factory\TranslatorFactory;

//order the fields by the menu_order value
//get_field_objects($original_post->ID); don't get the field orderd, what is the right method
//$acf is supposed to be an array tested before the include
$keys = array_column($acf_fields, 'menu_order');
array_multisort($keys, SORT_ASC, $acf_fields);

//ACF FIELDS are displayed with specific edit post page -->
//Loop on ACF Fields to keep the order  -->
foreach ($acf_fields as $key => $acf_fvalue) { ?>

    <!-- Only ACF Fields set are translatable must be shown  -->
    <?php if (!in_array($key,$falang_translated_metakeys )){continue;} ?>

    <?php $search_metakey = Falang_Core::get_prefix($falang_target_language_locale).$key?>
    <?php $previous_value = isset($falang_post->metakey[$search_metakey])?$falang_post->metakey[$search_metakey]:''; ?>
    <div class="row">
        <h2><?php echo $key ?></h2>
        <div class="col-source">

            <div id="original_value_<?php echo $key;?>" name="original_value_<?php echo $key;?>" style="display:none">
                <?php echo $original_post->{$key}; ?>
            </div>

                <?php
                    switch($acf_fields[$key]['type']){
                        case 'text' :
                            ?>
                            <input type="text" readonly name="<?php echo 'fake_original_value_'.$key; ?>" id="<?php echo 'fake_original_value_'.$key; ?>" value="<?php echo esc_attr($original_post->{$key}); ?>" class="falang">
                            <?php
                            break;
                        case 'textarea':
                            ?>
                            <textarea readonly name="<?php echo 'fake_original_value_'.$key; ?>" id="<?php echo 'fake_original_value_'.$key; ?>" class="falang"><?php echo esc_textarea($original_post->{$key}); ?></textarea>
                            <?php
                            break;
                        case 'wysiwyg':
                            echo wp_editor($original_post->{$key},'fake_original_value_'.$key,$editor_settings_source);
                            break;
                        default:
                            echo $original_post->{$key};
                    }//switch
                ?>
        </div><!-- col-source -->
        <div class="col-action">
            <button class="button-secondary button-copy" onclick="copyToTranslation('<?php echo $key;?>','copy');return false;" title="<?php  echo __( 'Copy', 'falang' ) ?>"><i class="icon-copy"></i></button>
            <!-- add yandex/azure button -->
            <?php if ($falang_model->get_option('enable_service') == '1') { ?>
                <?php if ($falang_model->get_option('service_name') == 'deepl') { ?>
                    <button class="button-secondary button-copy" onclick="copyToTranslation('<?php echo $key;?>','translate');return false;" title="<?php  echo __( 'Translate with DeepL', 'falang' ) ?>"><i class="icon-globe"></i></button>
                <?php } ?>
                <?php if ($falang_model->get_option('service_name') == 'google') { ?>
                    <button class="button-secondary button-copy" onclick="copyToTranslation('<?php echo $key;?>','translate');return false;" title="<?php  echo __( 'Translate with Google', 'falang' ) ?>"><i class="icon-google"></i></button>
                <?php } ?>
                <?php if ($falang_model->get_option('service_name') == 'yandex') { ?>
                    <button class="button-secondary button-copy" onclick="copyToTranslation('<?php echo $key;?>','translate');return false;" title="<?php  echo __( 'Translate with Yandex', 'falang' ) ?>"><i class="icon-yandex"></i></button>
                <?php } ?>
                <?php if ($falang_model->get_option('service_name') == 'azure') { ?>
                    <button class="button-secondary button-copy" onclick="copyToTranslation('<?php echo $key;?>','translate');return false;" title="<?php  echo __( 'Translate with Azure', 'falang' ) ?>"><i class="icon-windows"></i></button>
                <?php } ?>
                <?php if ($falang_model->get_option('service_name') == 'lingvanex') { ?>
                    <button class="button-secondary button-copy" onclick="copyToTranslation('<?php echo $key;?>','translate');return false;" title="<?php  echo __( 'Translate with Lingvanex', 'falang' ) ?>"><i class="icon-globe"></i></button>
                <?php } ?>
            <?php } else { ?>
                <button class="button-secondary button-copy" disabled title="<?php  echo __( 'No Translate Service enabled', 'falang' ) ?>"><i class="icon-language"></i></button>
            <?php }  ?>
        </div><!-- col-action -->
        <div class="col-target">
            <?php
            switch($acf_fields[$key]['type']){
                case 'text' :
                    ?>
                    <input type="text" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo isset($previous_value[0])?esc_attr($previous_value[0]):''; ?>" class="falang">
                    <?php
                    break;
                case 'textarea':
                    ?>
                    <textarea name="<?php echo $key; ?>" id="<?php echo $key; ?>" class="falang"><?php echo isset($previous_value[0])?esc_textarea($previous_value[0]):''; ?></textarea>
                    <?php
                    break;
                case 'wysiwyg':
                    echo wp_editor(isset($previous_value[0])?$previous_value[0]:'',$key,$editor_settings_dest);
                    break;
                default:
                    ?>
                    <input type="text" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo isset($previous_value[0])?esc_attr($previous_value[0]):''; ?>" class="falang">
                    <?php
                    break;
            }//switch
            ?>

        </div><!-- col-target -->
    </div><!-- row -->
<?php }//foreach metakeay ?>