<?php

function custom_astra_content_top() {
    if (is_singular('post') || is_post_type_archive('post') || is_home()) { 
        echo '<div class="custom-google-ads ast-container">
            <ins class="adsbygoogle"
                style="display:block"
                data-ad-client="ca-pub-1287393639924070"
                data-ad-slot="3984894349"
                data-ad-format="auto"
                data-full-width-responsive="true">
            </ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>';
    }
}
// add_action('astra_content_before', 'custom_astra_content_top');

function custom_astra_entry_content_after() {
    if (is_singular('post')) { 
        echo '<div class="custom-google-ads">
				<ins class="adsbygoogle"
					 style="display:block; text-align:center;"
					 data-ad-layout="in-article"
					 data-ad-format="fluid"
					 data-ad-client="ca-pub-1287393639924070"
					 data-ad-slot="7035172472">
				</ins>
				<script>
					 (adsbygoogle = window.adsbygoogle || []).push({});
				</script>
        	</div>';
    }
}
// add_action('astra_entry_bottom', 'custom_astra_entry_content_after');
