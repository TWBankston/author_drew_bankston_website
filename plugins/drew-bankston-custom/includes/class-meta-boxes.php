<?php
/**
 * Meta Boxes for Books and Events
 */

defined( 'ABSPATH' ) || exit;

class DBC_Meta_Boxes {
    
    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_book_meta' ) );
        add_action( 'save_post', array( __CLASS__, 'save_event_meta' ) );
    }
    
    public static function add_meta_boxes() {
        // Book meta boxes
        add_meta_box(
            'dbc_book_details',
            'Book Details',
            array( __CLASS__, 'render_book_details' ),
            'book',
            'normal',
            'high'
        );
        
        add_meta_box(
            'dbc_book_retailers',
            'Retailer Links',
            array( __CLASS__, 'render_book_retailers' ),
            'book',
            'normal',
            'default'
        );
        
        add_meta_box(
            'dbc_book_reviews',
            'Reviews & Awards',
            array( __CLASS__, 'render_book_reviews' ),
            'book',
            'normal',
            'default'
        );
        
        add_meta_box(
            'dbc_book_purchase',
            'Purchase Options (Direct Sales)',
            array( __CLASS__, 'render_book_purchase' ),
            'book',
            'normal',
            'default'
        );
        
        // Event meta boxes
        add_meta_box(
            'dbc_event_details',
            'Event Details',
            array( __CLASS__, 'render_event_details' ),
            'event',
            'normal',
            'high'
        );
    }
    
    public static function render_book_details( $post ) {
        wp_nonce_field( 'dbc_book_meta', 'dbc_book_meta_nonce' );
        
        $tagline      = get_post_meta( $post->ID, '_dbc_book_tagline', true );
        $subtitle     = get_post_meta( $post->ID, '_dbc_book_subtitle', true );
        $page_count   = get_post_meta( $post->ID, '_dbc_book_page_count', true );
        $pub_date     = get_post_meta( $post->ID, '_dbc_book_pub_date', true );
        $isbn_print   = get_post_meta( $post->ID, '_dbc_book_isbn_print', true );
        $isbn_ebook   = get_post_meta( $post->ID, '_dbc_book_isbn_ebook', true );
        $isbn_audio   = get_post_meta( $post->ID, '_dbc_book_isbn_audio', true );
        $audience     = get_post_meta( $post->ID, '_dbc_book_audience', true );
        $series_order = get_post_meta( $post->ID, '_dbc_book_series_order', true );
        $featured     = get_post_meta( $post->ID, '_dbc_book_featured', true );
        $formats      = get_post_meta( $post->ID, '_dbc_book_formats', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="dbc_book_tagline">Tagline</label></th>
                <td><input type="text" id="dbc_book_tagline" name="dbc_book_tagline" value="<?php echo esc_attr( $tagline ); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_subtitle">Subtitle</label></th>
                <td><input type="text" id="dbc_book_subtitle" name="dbc_book_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_page_count">Page Count</label></th>
                <td><input type="number" id="dbc_book_page_count" name="dbc_book_page_count" value="<?php echo esc_attr( $page_count ); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_pub_date">Publication Date</label></th>
                <td><input type="date" id="dbc_book_pub_date" name="dbc_book_pub_date" value="<?php echo esc_attr( $pub_date ); ?>"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_isbn_print">ISBN (Print)</label></th>
                <td><input type="text" id="dbc_book_isbn_print" name="dbc_book_isbn_print" value="<?php echo esc_attr( $isbn_print ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_isbn_ebook">ISBN (eBook)</label></th>
                <td><input type="text" id="dbc_book_isbn_ebook" name="dbc_book_isbn_ebook" value="<?php echo esc_attr( $isbn_ebook ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_isbn_audio">ISBN (Audiobook)</label></th>
                <td><input type="text" id="dbc_book_isbn_audio" name="dbc_book_isbn_audio" value="<?php echo esc_attr( $isbn_audio ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_audience">Target Audience</label></th>
                <td>
                    <select id="dbc_book_audience" name="dbc_book_audience">
                        <option value="">Select...</option>
                        <option value="adult" <?php selected( $audience, 'adult' ); ?>>Adult</option>
                        <option value="young-adult" <?php selected( $audience, 'young-adult' ); ?>>Young Adult</option>
                        <option value="middle-grade" <?php selected( $audience, 'middle-grade' ); ?>>Middle Grade</option>
                        <option value="all-ages" <?php selected( $audience, 'all-ages' ); ?>>All Ages</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="dbc_book_formats">Available Formats</label></th>
                <td><input type="text" id="dbc_book_formats" name="dbc_book_formats" value="<?php echo esc_attr( $formats ); ?>" class="regular-text" placeholder="e.g., Paperback, eBook, Audiobook"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_series_order">Series Order</label></th>
                <td><input type="number" id="dbc_book_series_order" name="dbc_book_series_order" value="<?php echo esc_attr( $series_order ); ?>" class="small-text" min="1"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_featured">Featured Book</label></th>
                <td><input type="checkbox" id="dbc_book_featured" name="dbc_book_featured" value="1" <?php checked( $featured, '1' ); ?>> Display in featured sections</td>
            </tr>
        </table>
        
        <h4 style="margin-top: 20px;">Free Chapter</h4>
        <table class="form-table">
            <?php $free_chapter = get_post_meta( $post->ID, '_dbc_book_free_chapter', true ); ?>
            <tr>
                <th><label for="dbc_book_free_chapter">Free Chapter Filename</label></th>
                <td>
                    <input type="text" id="dbc_book_free_chapter" name="dbc_book_free_chapter" value="<?php echo esc_attr( $free_chapter ); ?>" class="regular-text" placeholder="e.g., Free Chapter - Khizara.pdf">
                    <p class="description">Enter the filename of the PDF in <code>/theme/assets/free chapters/</code></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public static function render_book_retailers( $post ) {
        $amazon      = get_post_meta( $post->ID, '_dbc_book_amazon_url', true );
        $barnes      = get_post_meta( $post->ID, '_dbc_book_barnes_url', true );
        $bookshop    = get_post_meta( $post->ID, '_dbc_book_bookshop_url', true );
        $indiebound  = get_post_meta( $post->ID, '_dbc_book_indiebound_url', true );
        $kobo        = get_post_meta( $post->ID, '_dbc_book_kobo_url', true );
        $apple       = get_post_meta( $post->ID, '_dbc_book_apple_url', true );
        $audible     = get_post_meta( $post->ID, '_dbc_book_audible_url', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="dbc_book_amazon_url">Amazon</label></th>
                <td><input type="url" id="dbc_book_amazon_url" name="dbc_book_amazon_url" value="<?php echo esc_url( $amazon ); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_barnes_url">Barnes & Noble</label></th>
                <td><input type="url" id="dbc_book_barnes_url" name="dbc_book_barnes_url" value="<?php echo esc_url( $barnes ); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_bookshop_url">Bookshop.org</label></th>
                <td><input type="url" id="dbc_book_bookshop_url" name="dbc_book_bookshop_url" value="<?php echo esc_url( $bookshop ); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_indiebound_url">IndieBound</label></th>
                <td><input type="url" id="dbc_book_indiebound_url" name="dbc_book_indiebound_url" value="<?php echo esc_url( $indiebound ); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_kobo_url">Kobo</label></th>
                <td><input type="url" id="dbc_book_kobo_url" name="dbc_book_kobo_url" value="<?php echo esc_url( $kobo ); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_apple_url">Apple Books</label></th>
                <td><input type="url" id="dbc_book_apple_url" name="dbc_book_apple_url" value="<?php echo esc_url( $apple ); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_audible_url">Audible</label></th>
                <td><input type="url" id="dbc_book_audible_url" name="dbc_book_audible_url" value="<?php echo esc_url( $audible ); ?>" class="large-text"></td>
            </tr>
        </table>
        <?php
    }
    
    public static function render_book_reviews( $post ) {
        $reviews = get_post_meta( $post->ID, '_dbc_book_reviews', true );
        $awards  = get_post_meta( $post->ID, '_dbc_book_awards', true );
        if ( ! is_array( $reviews ) ) $reviews = array();
        if ( ! is_array( $awards ) ) $awards = array();
        ?>
        <h4>Review Quotes</h4>
        <p class="description">Add review excerpts to display on the book page.</p>
        <div id="dbc-reviews-container">
            <?php foreach ( $reviews as $i => $review ) : ?>
            <div class="dbc-review-row" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-left: 3px solid #0073aa;">
                <p><label>Quote:</label><br>
                <textarea name="dbc_book_reviews[<?php echo $i; ?>][quote]" rows="2" class="large-text"><?php echo esc_textarea( $review['quote'] ?? '' ); ?></textarea></p>
                <p><label>Source:</label><br>
                <input type="text" name="dbc_book_reviews[<?php echo $i; ?>][source]" value="<?php echo esc_attr( $review['source'] ?? '' ); ?>" class="regular-text" placeholder="e.g., Publisher's Weekly, Reader Name"></p>
                <p><label>Link (optional):</label><br>
                <input type="url" name="dbc_book_reviews[<?php echo $i; ?>][url]" value="<?php echo esc_url( $review['url'] ?? '' ); ?>" class="regular-text" placeholder="https://..."></p>
            </div>
            <?php endforeach; ?>
            <div class="dbc-review-row" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-left: 3px solid #0073aa;">
                <p><label>Quote:</label><br>
                <textarea name="dbc_book_reviews[new][quote]" rows="2" class="large-text"></textarea></p>
                <p><label>Source:</label><br>
                <input type="text" name="dbc_book_reviews[new][source]" value="" class="regular-text" placeholder="e.g., Publisher's Weekly, Reader Name"></p>
                <p><label>Link (optional):</label><br>
                <input type="url" name="dbc_book_reviews[new][url]" value="" class="regular-text" placeholder="https://..."></p>
            </div>
        </div>
        
        <hr style="margin: 20px 0;">
        
        <h4>Awards & Recognition</h4>
        <p class="description">Add awards, badges, or recognition the book has received.</p>
        <div id="dbc-awards-container">
            <?php foreach ( $awards as $i => $award ) : ?>
            <div class="dbc-award-row" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-left: 3px solid #dba617;">
                <p><label>Award Name:</label><br>
                <input type="text" name="dbc_book_awards[<?php echo $i; ?>][name]" value="<?php echo esc_attr( $award['name'] ?? '' ); ?>" class="regular-text" placeholder="e.g., Firebird Award Winner"></p>
                <p><label>Year:</label><br>
                <input type="text" name="dbc_book_awards[<?php echo $i; ?>][year]" value="<?php echo esc_attr( $award['year'] ?? '' ); ?>" class="small-text" placeholder="e.g., 2024"></p>
                <p><label>Badge Image URL (optional):</label><br>
                <input type="url" name="dbc_book_awards[<?php echo $i; ?>][badge_url]" value="<?php echo esc_url( $award['badge_url'] ?? '' ); ?>" class="large-text"></p>
                <p><label>Link (optional):</label><br>
                <input type="url" name="dbc_book_awards[<?php echo $i; ?>][url]" value="<?php echo esc_url( $award['url'] ?? '' ); ?>" class="regular-text"></p>
            </div>
            <?php endforeach; ?>
            <div class="dbc-award-row" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-left: 3px solid #dba617;">
                <p><label>Award Name:</label><br>
                <input type="text" name="dbc_book_awards[new][name]" value="" class="regular-text" placeholder="e.g., Firebird Award Winner"></p>
                <p><label>Year:</label><br>
                <input type="text" name="dbc_book_awards[new][year]" value="" class="small-text" placeholder="e.g., 2024"></p>
                <p><label>Badge Image URL (optional):</label><br>
                <input type="url" name="dbc_book_awards[new][badge_url]" value="" class="large-text"></p>
                <p><label>Link (optional):</label><br>
                <input type="url" name="dbc_book_awards[new][url]" value="" class="regular-text"></p>
            </div>
        </div>
        <?php
    }
    
    public static function render_book_purchase( $post ) {
        $signed_enabled = get_post_meta( $post->ID, '_dbc_book_signed_enabled', true );
        $signed_price   = get_post_meta( $post->ID, '_dbc_book_signed_price', true );
        $digital_enabled = get_post_meta( $post->ID, '_dbc_book_digital_enabled', true );
        $digital_price  = get_post_meta( $post->ID, '_dbc_book_digital_price', true );
        $digital_file   = get_post_meta( $post->ID, '_dbc_book_digital_file', true );
        ?>
        <p class="description">Configure direct sales options. These connect to Square payment processing.</p>
        
        <h4 style="margin-top: 15px;">Signed Paperback Copy</h4>
        <table class="form-table">
            <tr>
                <th><label for="dbc_book_signed_enabled">Enable Signed Copy Sales</label></th>
                <td><input type="checkbox" id="dbc_book_signed_enabled" name="dbc_book_signed_enabled" value="1" <?php checked( $signed_enabled, '1' ); ?>> Allow customers to purchase signed copies</td>
            </tr>
            <tr>
                <th><label for="dbc_book_signed_price">Price (USD)</label></th>
                <td>
                    <input type="number" id="dbc_book_signed_price" name="dbc_book_signed_price" value="<?php echo esc_attr( $signed_price ); ?>" class="small-text" step="0.01" min="0">
                    <span class="description">+ Shipping & Handling</span>
                </td>
            </tr>
        </table>
        
        <h4 style="margin-top: 20px;">Digital Copy (eBook/PDF)</h4>
        <table class="form-table">
            <tr>
                <th><label for="dbc_book_digital_enabled">Enable Digital Sales</label></th>
                <td><input type="checkbox" id="dbc_book_digital_enabled" name="dbc_book_digital_enabled" value="1" <?php checked( $digital_enabled, '1' ); ?>> Allow customers to purchase digital copies</td>
            </tr>
            <tr>
                <th><label for="dbc_book_digital_price">Price (USD)</label></th>
                <td><input type="number" id="dbc_book_digital_price" name="dbc_book_digital_price" value="<?php echo esc_attr( $digital_price ); ?>" class="small-text" step="0.01" min="0"></td>
            </tr>
            <tr>
                <th><label for="dbc_book_digital_file">Digital File</label></th>
                <td>
                    <input type="text" id="dbc_book_digital_file" name="dbc_book_digital_file" value="<?php echo esc_attr( $digital_file ); ?>" class="regular-text" placeholder="e.g., imagination-stone-ebook.pdf">
                    <p class="description">Filename in <code>/theme/assets/digital-books/</code> - available after purchase in My Account</p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public static function render_event_details( $post ) {
        wp_nonce_field( 'dbc_event_meta', 'dbc_event_meta_nonce' );
        
        $start_datetime = get_post_meta( $post->ID, '_dbc_event_start_datetime', true );
        $end_datetime   = get_post_meta( $post->ID, '_dbc_event_end_datetime', true );
        $location_name  = get_post_meta( $post->ID, '_dbc_event_location_name', true );
        $location_addr  = get_post_meta( $post->ID, '_dbc_event_location_address', true );
        $event_type     = get_post_meta( $post->ID, '_dbc_event_type', true );
        $event_url      = get_post_meta( $post->ID, '_dbc_event_url', true );
        $is_virtual     = get_post_meta( $post->ID, '_dbc_event_is_virtual', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="dbc_event_start_datetime">Start Date/Time</label></th>
                <td><input type="datetime-local" id="dbc_event_start_datetime" name="dbc_event_start_datetime" value="<?php echo esc_attr( $start_datetime ); ?>"></td>
            </tr>
            <tr>
                <th><label for="dbc_event_end_datetime">End Date/Time</label></th>
                <td><input type="datetime-local" id="dbc_event_end_datetime" name="dbc_event_end_datetime" value="<?php echo esc_attr( $end_datetime ); ?>"></td>
            </tr>
            <tr>
                <th><label for="dbc_event_type">Event Type</label></th>
                <td>
                    <select id="dbc_event_type" name="dbc_event_type">
                        <option value="">Select...</option>
                        <option value="signing" <?php selected( $event_type, 'signing' ); ?>>Book Signing</option>
                        <option value="reading" <?php selected( $event_type, 'reading' ); ?>>Reading</option>
                        <option value="panel" <?php selected( $event_type, 'panel' ); ?>>Panel Discussion</option>
                        <option value="convention" <?php selected( $event_type, 'convention' ); ?>>Convention</option>
                        <option value="workshop" <?php selected( $event_type, 'workshop' ); ?>>Workshop</option>
                        <option value="virtual" <?php selected( $event_type, 'virtual' ); ?>>Virtual Event</option>
                        <option value="other" <?php selected( $event_type, 'other' ); ?>>Other</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="dbc_event_is_virtual">Virtual Event</label></th>
                <td><input type="checkbox" id="dbc_event_is_virtual" name="dbc_event_is_virtual" value="1" <?php checked( $is_virtual, '1' ); ?>> This is a virtual/online event</td>
            </tr>
            <tr>
                <th><label for="dbc_event_location_name">Location Name</label></th>
                <td><input type="text" id="dbc_event_location_name" name="dbc_event_location_name" value="<?php echo esc_attr( $location_name ); ?>" class="large-text" placeholder="e.g., Denver Convention Center"></td>
            </tr>
            <tr>
                <th><label for="dbc_event_location_address">Location Address</label></th>
                <td><textarea id="dbc_event_location_address" name="dbc_event_location_address" class="large-text" rows="2" placeholder="Full address or virtual event link"><?php echo esc_textarea( $location_addr ); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="dbc_event_url">Event URL</label></th>
                <td><input type="url" id="dbc_event_url" name="dbc_event_url" value="<?php echo esc_url( $event_url ); ?>" class="large-text" placeholder="Link to event page, tickets, etc."></td>
            </tr>
        </table>
        <?php
    }
    
    public static function save_book_meta( $post_id ) {
        if ( ! isset( $_POST['dbc_book_meta_nonce'] ) || ! wp_verify_nonce( $_POST['dbc_book_meta_nonce'], 'dbc_book_meta' ) ) {
            return;
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        // Save book details
        $fields = array(
            'dbc_book_tagline'      => '_dbc_book_tagline',
            'dbc_book_subtitle'     => '_dbc_book_subtitle',
            'dbc_book_page_count'   => '_dbc_book_page_count',
            'dbc_book_pub_date'     => '_dbc_book_pub_date',
            'dbc_book_isbn_print'   => '_dbc_book_isbn_print',
            'dbc_book_isbn_ebook'   => '_dbc_book_isbn_ebook',
            'dbc_book_isbn_audio'   => '_dbc_book_isbn_audio',
            'dbc_book_audience'     => '_dbc_book_audience',
            'dbc_book_series_order' => '_dbc_book_series_order',
            'dbc_book_formats'      => '_dbc_book_formats',
        );
        
        foreach ( $fields as $post_key => $meta_key ) {
            if ( isset( $_POST[ $post_key ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $post_key ] ) );
            }
        }
        
        // Featured checkbox
        $featured = isset( $_POST['dbc_book_featured'] ) ? '1' : '';
        update_post_meta( $post_id, '_dbc_book_featured', $featured );
        
        // Free chapter filename
        if ( isset( $_POST['dbc_book_free_chapter'] ) ) {
            update_post_meta( $post_id, '_dbc_book_free_chapter', sanitize_file_name( $_POST['dbc_book_free_chapter'] ) );
        }
        
        // Retailer URLs
        $url_fields = array(
            'dbc_book_amazon_url'    => '_dbc_book_amazon_url',
            'dbc_book_barnes_url'    => '_dbc_book_barnes_url',
            'dbc_book_bookshop_url'  => '_dbc_book_bookshop_url',
            'dbc_book_indiebound_url' => '_dbc_book_indiebound_url',
            'dbc_book_kobo_url'      => '_dbc_book_kobo_url',
            'dbc_book_apple_url'     => '_dbc_book_apple_url',
            'dbc_book_audible_url'   => '_dbc_book_audible_url',
        );
        
        foreach ( $url_fields as $post_key => $meta_key ) {
            if ( isset( $_POST[ $post_key ] ) ) {
                update_post_meta( $post_id, $meta_key, esc_url_raw( $_POST[ $post_key ] ) );
            }
        }
        
        // Purchase options
        $signed_enabled = isset( $_POST['dbc_book_signed_enabled'] ) ? '1' : '';
        update_post_meta( $post_id, '_dbc_book_signed_enabled', $signed_enabled );
        
        if ( isset( $_POST['dbc_book_signed_price'] ) ) {
            update_post_meta( $post_id, '_dbc_book_signed_price', sanitize_text_field( $_POST['dbc_book_signed_price'] ) );
        }
        
        $digital_enabled = isset( $_POST['dbc_book_digital_enabled'] ) ? '1' : '';
        update_post_meta( $post_id, '_dbc_book_digital_enabled', $digital_enabled );
        
        if ( isset( $_POST['dbc_book_digital_price'] ) ) {
            update_post_meta( $post_id, '_dbc_book_digital_price', sanitize_text_field( $_POST['dbc_book_digital_price'] ) );
        }
        
        if ( isset( $_POST['dbc_book_digital_file'] ) ) {
            update_post_meta( $post_id, '_dbc_book_digital_file', sanitize_file_name( $_POST['dbc_book_digital_file'] ) );
        }
        
        // Reviews
        if ( isset( $_POST['dbc_book_reviews'] ) ) {
            $reviews = array();
            foreach ( $_POST['dbc_book_reviews'] as $review ) {
                if ( ! empty( $review['quote'] ) ) {
                    $reviews[] = array(
                        'quote'  => sanitize_textarea_field( $review['quote'] ),
                        'source' => sanitize_text_field( $review['source'] ?? '' ),
                        'url'    => esc_url_raw( $review['url'] ?? '' ),
                    );
                }
            }
            update_post_meta( $post_id, '_dbc_book_reviews', $reviews );
        }
        
        // Awards
        if ( isset( $_POST['dbc_book_awards'] ) ) {
            $awards = array();
            foreach ( $_POST['dbc_book_awards'] as $award ) {
                if ( ! empty( $award['name'] ) ) {
                    $awards[] = array(
                        'name'      => sanitize_text_field( $award['name'] ),
                        'year'      => sanitize_text_field( $award['year'] ?? '' ),
                        'badge_url' => esc_url_raw( $award['badge_url'] ?? '' ),
                        'url'       => esc_url_raw( $award['url'] ?? '' ),
                    );
                }
            }
            update_post_meta( $post_id, '_dbc_book_awards', $awards );
        }
    }
    
    public static function save_event_meta( $post_id ) {
        if ( ! isset( $_POST['dbc_event_meta_nonce'] ) || ! wp_verify_nonce( $_POST['dbc_event_meta_nonce'], 'dbc_event_meta' ) ) {
            return;
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        $fields = array(
            'dbc_event_start_datetime'    => '_dbc_event_start_datetime',
            'dbc_event_end_datetime'      => '_dbc_event_end_datetime',
            'dbc_event_location_name'     => '_dbc_event_location_name',
            'dbc_event_location_address'  => '_dbc_event_location_address',
            'dbc_event_type'              => '_dbc_event_type',
            'dbc_event_url'               => '_dbc_event_url',
        );
        
        foreach ( $fields as $post_key => $meta_key ) {
            if ( isset( $_POST[ $post_key ] ) ) {
                if ( strpos( $meta_key, '_url' ) !== false ) {
                    update_post_meta( $post_id, $meta_key, esc_url_raw( $_POST[ $post_key ] ) );
                } else {
                    update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $post_key ] ) );
                }
            }
        }
        
        $is_virtual = isset( $_POST['dbc_event_is_virtual'] ) ? '1' : '';
        update_post_meta( $post_id, '_dbc_event_is_virtual', $is_virtual );
    }
}


