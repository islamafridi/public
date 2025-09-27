<?php
if (!defined('ABSPATH')) exit;
function apktemplates_schema() { ?>
<script type="application/ld+json">
{
"@context": "https://schema.org",
"@type": "WebSite",
"url": "<?php echo get_site_url(); ?>",
"potentialAction": {
	"@type": "SearchAction",
		"target": {
			"@type": "EntryPoint",
			"urlTemplate": "<?php echo get_site_url(); ?>/?s={search_term_string}"
		},
	"query-input": "required name=search_term_string"
	}
}
</script>
<?php }
add_action('wp_head', 'apktemplates_schema', 1);
?>