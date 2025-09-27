<?php if (!defined('ABSPATH')) exit; 

global $custom_download_id; 
$download_id = absint($custom_download_id); 
$download_links = get_post_meta(get_the_ID(), 'repeatable_download_link', true); 
$telegram_text = get_theme_mod('telegram_text', 'join telegram'); 
$telegram_url = get_theme_mod('telegram_url', 'https://telegram.me/apktemplates'); 
?>

<div class="foot">
    <?php if(function_exists('download_top_ad')) download_top_ad(); 
    
    if($download_links) : 
        foreach ($download_links as $index => $download) : 
            if($download_id === $index) : 
                $download_size = $download['download_size'] ?? ''; 
                $original_download_url = $download['download_url'] ?? ''; 
                
                // Use plugin function to get proxy URL (falls back to original if plugin not active)
                $final_download_url = function_exists('dfr_get_proxy_download_url') 
                    ? dfr_get_proxy_download_url($original_download_url) 
                    : $original_download_url;
                ?>
                
                <span id="waiting">
                    <span id="timer" class="timer">1</span>
                    <div id="pre_download" style="display: none; margin-top: 1em;">
                        <a href="<?php echo esc_url($final_download_url); ?>" 
                           style="display: block; margin-top: 1em;" 
                           class="btn btn-lg btn-green" 
                           role="button" 
                           rel="nofollow">
                            <span class="uppercase fbold">
                                <?php _e('DOWNLOAD', 'apktemplates'); ?> (<?php echo esc_html($download_size); ?>)
                            </span>
                        </a>
                    </div>
                </span>
                
            <?php endif; 
        endforeach; 
    endif; 
    
    if(function_exists('download_bottom_ad')) download_bottom_ad(); 
    
    if($telegram_url) : ?>
        <a class="btn btn-lg btn-telegram" 
           href="<?php echo esc_url($telegram_url); ?>" 
           target="_blank" 
           rel="nofollow" 
           style="margin-top:30px;margin-bottom:30px;background-color:#039be5;color:#fff">
            <span class="uppercase fbold">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="32" height="32">
                        <path fill="currentColor" d="M446.7 98.6l-67.6 318.8c-5.1 22.5-18.4 28.1-37.3 17.5l-103-75.9-49.7 47.8c-5.5 5.5-10.1 10.1-20.7 10.1l7.4-104.9 190.9-172.5c8.3-7.4-1.8-11.5-12.9-4.1L117.8 284 16.2 252.2c-22.1-6.9-22.5-22.1 4.6-32.7L418.2 66.4c18.4-6.9 34.5 4.1 28.5 32.2z"></path>
                    </svg>
                </i>
                <?php echo esc_html($telegram_text); ?>
            </span>
        </a>
        
        <a class="btn btn-lg btn-telegram" 
           href="https://whatsapp.com/channel/0029Vb5ubj4JuyAFZwAuI21M" 
           target="_blank" 
           rel="nofollow" 
           style="background-color:#25C942;color:#fff">
            <span class="uppercase fbold">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                    </svg>
                </i>
                Whatsapp Channel
            </span>
        </a>
        
    <?php endif; ?>
</div>