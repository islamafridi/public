<?php if (!defined('ABSPATH')) exit; ?>
<div class="share">
    <div class="share-box">
        <a class="share-btn share-fb" href="https://www.facebook.com/sharer.php?src=sp&amp;u=<?php the_permalink(); ?>&amp;title=<?php the_title(); ?>" target="_blank" rel="noopener nofollow" title="Share on Facebook" aria-label="Share on Facebook" onclick="minWin(this.href); return false;">
            <svg width="24" height="24">
                <use xlink:href="#i__share-fb"></use>
            </svg>
        </a>
        <a class="share-btn share-tw" href="https://twitter.com/intent/tweet?text=<?php the_title(); ?>&amp;url=<?php the_permalink(); ?>" target="_blank" rel="noopener nofollow" title="Share on Twitter" aria-label="Share on Twitter" onclick="minWin(this.href); return false;">
            <svg width="24" height="24">
                <use xlink:href="#i__share-tw"></use>
            </svg>
        </a>
        <a class="share-btn share-tg" href="https://t.me/share/url?url=<?php the_permalink(); ?>&amp;text=<?php the_title(); ?>" target="_blank" rel="noopener nofollow" title="Share on Telegram" aria-label="Share on Telegram" onclick="minWin(this.href); return false;">
            <svg width="20" height="20">
                <use xlink:href="#i__share-tg"></use>
            </svg>
        </a>
        <a class="share-btn share-wa" href="https://api.whatsapp.com/send?text=<?php the_title(); ?>&nbsp;<?php the_permalink(); ?>" target="_blank" rel="noopener nofollow" title="Share on WhatsApp" aria-label="Share on WhatsApp" onclick="minWin(this.href); return false;">
            <svg width="20" height="20">
                <use xlink:href="#i__share-wa"></use>
            </svg>
        </a>
    </div>
</div>