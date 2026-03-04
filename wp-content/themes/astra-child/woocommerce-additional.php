<?php 
add_action('wp_footer', 'custom_modal_html');
function custom_modal_html() {
    ?>
    <!-- Modal Structure -->
    <div class="custom-modal" style="display:none;">
        <div class="custom-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5);"></div>
        <div class="custom-modal-content" style="">
            <h2 class="text-bn">কিভাবে টি বাজার বিডিতে অর্ডার করবেন জানতে সম্পূর্ণ ভিডিও দেখুন</h2>
            <div>
				<iframe src="https://www.facebook.com/plugins/video.php?height=476&href=https%3A%2F%2Fwww.facebook.com%2Fteabazarbdofficial%2Fvideos%2F549366317640812%2F&show_text=false&width=476&t=0" width="476" height="476" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe>
			</div>
            <button type="button" class="custom-modal-close" style="background-color: #ff4e4e; color: white; border: none; padding: 10px 20px; cursor: pointer;">Close</button>
        </div>
    </div>
    <?php
}