<?php
/**
 * Class Reset Thumbnail Helper
 * @author Kai
 */

if (!class_exists('Reset_Thumbnails_Helper')) {
    /**
     * Class Reset_Thumbnails_Helper
     */
    class Reset_Thumbnails_Helper
    {
        private $id;

        public function __construct()
        {
            // code
            add_action('kai_notice', array($this, 'checkImageUploaded'));
            // Set Default Image
            add_action('wp_ajax_set_default_image', array($this, 'set_default_image'));
        }

        /**
         * Check Image Exists
         */
        public function checkImageUploaded()
        {
            $output = get_option('kai_default_image');
            return $output;
        }

        /**
         * Get All Size Avaiable
         * @param string $size
         * @return array|bool
         */
        public function getAllSize($size = '')
        {
            global $_wp_additional_image_sizes;
            $sizes = array();
            $get_intermediate_image_sizes = get_intermediate_image_sizes();
            // Create the full array with sizes and crop info
            foreach ($get_intermediate_image_sizes as $_size) {

                if (in_array($_size, array('thumbnail', 'medium', 'large'))) {

                    $sizes[$_size]['width'] = get_option($_size . '_size_w');
                    $sizes[$_size]['height'] = get_option($_size . '_size_h');
                    $sizes[$_size]['crop'] = (bool)get_option($_size . '_crop');

                } elseif (isset($_wp_additional_image_sizes[$_size])) {

                    $sizes[$_size] = array(
                        'width' => $_wp_additional_image_sizes[$_size]['width'],
                        'height' => $_wp_additional_image_sizes[$_size]['height'],
                        'crop' => $_wp_additional_image_sizes[$_size]['crop']
                    );
                }

            }

            // Get only 1 size if found
            if ($size) {

                if (isset($sizes[$size])) {
                    return $sizes[$size];
                } else {
                    return false;
                }

            }
            return $sizes;
        }

        public function get_attached_image($id = "")
        {
            $id = $this->id;
            $output = array();
            global $wpdb;
            // Get Set Thumbnail Attached File
            $output['id'] = $id;
            $src = get_post_meta($id, '_wp_attached_file', true);
            $output['src'] = $src;
            // Get Thumbnail Attached Full Url
            $upload_dir = content_url();
            $full_src = $upload_dir . '/uploads/' . $src;
            $output['full_src'] = $full_src;
            $metadata = get_post_meta($id, '_wp_attachment_metadata', true);
            $output['metadata'] = $metadata;
            $output['product_gallery'] = $id.','.$id.','.$id.','.$id;
            return $output;
        }

        /**
         * Set Image for all attachments
         */
        public function set_all_media_thumbnails()
        {
            global $wpdb;
            $image = $this->get_attached_image();
            $posts = $wpdb->get_results($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = '%s'",'attachment'));
            foreach ($posts as $attachment_post) {
                update_post_meta($attachment_post->ID, '_wp_attached_file', $image['src']);
                update_post_meta($attachment_post->ID, '_wp_attachment_metadata', $image['metadata']);
                $wpdb->update(
                    $wpdb->posts, array(
                    'guid' => $image['full_src'], // string
                ), array('ID' => $attachment_post->ID), array(
                    '%s',// Set value type
                ), array('%d')
                );
            }
            ob_start();
            // Get Item Title
            echo '<p>All Attachment Have Set</p>';
            $output = ob_get_contents();
            ob_end_clean();
            echo json_encode($output);
        }

        /**
         * Set Product Gallery
         * using by Woocommerce
         * @param $post_id
         */
        public function set_product_gallery($post_id)
        {
            $image = $this->get_attached_image();
            $gallery = get_post_meta($post_id, '_product_image_gallery', true);
            if ($gallery != "") :
                        update_post_meta($post_id, '_product_image_gallery', $image['product_gallery']);
                endif;
            return $gallery_ids;
        }

        /**
         * Set Post Type Gallery
         * using by Orenmode Themes
         * @param $post_id
         */
        public function set_post_type_gallery($post_id)
        {
            $image = $this->get_attached_image();
            $post_gallery = get_post_meta($post_id, 'gallery', true);
            if (!empty($post_gallery)) :
                foreach ($post_gallery as &$post_gallery_item) {
                    $post_gallery_item = $image['full_src'];
                }
                update_post_meta($post_id, 'gallery', $post_gallery);
            endif;
        }

        /**
         * Set Post Thumbnail
         * @param string $id
         */
        public function set_post_type_thumbnails($post_type = '', $change_id)
        {
            if ($post_type == '' || $post_type == 'attachment') {
                $this->set_all_media_thumbnails();
                return;
            }
            global $wpdb;
            // Set for other post type
            // get_attached_image_src
            $image = $this->get_attached_image();
            // Reset Post Thumbnail
            $posts = $wpdb->get_results($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = '%s' AND post_status = '%s'",$post_type,'publish'));
            ob_start();
            foreach ($posts as $post_item) {
                $thumb_id = get_post_meta($post_item->ID, '_thumbnail_id', true);
                // UPDATE gallery
                if ($post_type == 'product') {
                    $this->set_product_gallery($post_item->ID);
                } else {
                    $this->set_post_type_gallery($post_item->ID);
                }
                // Set if thumbnail exist
                if ($thumb_id != "") {
                    // Change ID Option
                    if ($change_id == 'true') {
                    update_post_meta($post_item->ID, '_thumbnail_id', $image['id']);
                    }else{
                    // UPDATE Thumbnail
                    update_post_meta($thumb_id, '_wp_attached_file', $image['src']);
                    update_post_meta($thumb_id, '_wp_attachment_metadata', $image['metadata']);
                    }
                    $status = "Done!";
                    $class = ' class="note"';
                } else {
                    $status = "No Thumbnail";
                    $class = ' class="error"';
                }
                // UPDATE All Attached By POST ID
                $attachments_posts = $wpdb->get_results($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = '%s' AND post_parent = '%d'",'attachment',$post_item->ID ));
                foreach ($attachments_posts as $attachment_post) {
                    $wpdb->update(
                        $wpdb->posts, array(
                        'guid' => $image['full_src'], // string
                    ), array('ID' => $attachment_post->ID), array(
                        '%s', // value1
                    ), array('%d')
                    );
                }
                // Get Item Title
                echo '<p><a target="_blank" href="' . get_the_permalink($post_item->ID) . '">' . get_the_title($post_item->ID) . '</a><span' . $class . '</span></p>';
            }
            // Print List Item Passed
            $output = ob_get_contents();
            ob_end_clean();
            echo json_encode($output);
            exit();
        }

        /**
         * Set_default_image
         * @param string $size
         * @return array|bool
         */
        public function set_default_image()
        {
            if (isset($_POST['img_id']) && $_POST['img_id'] != "") {
                if ( isset($_POST['change_id']) ) {
                    $change_id = $_POST['change_id'];
                }else{
                    $change_id = "false";
                }
                $this->id = $_POST['img_id'];
                $type = "";
                if (isset($_POST['type'])) {
                    $type = $_POST['type'];
                    $this->set_post_type_thumbnails($type, $change_id);
                }
            }
            exit();
        }


    }
}



?>