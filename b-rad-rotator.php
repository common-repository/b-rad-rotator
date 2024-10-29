<?php
/*
 
    Copyright 2013 Brandon Ferrara
  
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    
    Plugin Name: B Rad Rotator
    Description: This plugin will generate rotators with custom thumbnails and actions
    Author: Brandon Ferrara
    Author URI: http://bferrara.ca/
    Version: 1.0.1

*/
    

    class b_rad_rotator {
        
        public $pluginName = "b_rad_rotator";
        private $itemsVisible;
        private $leftArrow;
        private $rightArrow;
        private $skinColour;
        
        function __construct(){
            add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
            add_action( 'save_post', array( $this, 'save' ) );
            add_action( 'wp_enqueue_scripts', array($this, 'addStyleSheet'));
                
            $this->itemsVisible = get_option($this->pluginName."_items_visible");
            $this->leftArrow = get_option($this->pluginName."_left_arrow");
            $this->rightArrow = get_option($this->pluginName."_right_arrow");
            $this->skinColour = get_option($this->pluginName."_skin_colour");
        }

        function addStyleSheet(){
            wp_register_style( 'bRadRotator', plugins_url('b-rad-rotator.css', __FILE__) );
            wp_enqueue_style('bRadRotator');
       }
        
        function b_rad_rotator_menu() {
            add_options_page( 'B Rad Rotator Options', 'B Rad Rotator', 'manage_options', 'b-rad-rotator', array($this, $this->pluginName.'_options' ));
        }
        

        function b_rad_rotator_options() {
            if ( !current_user_can( 'manage_options' ) )  {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }
        }
        
        /**
        * Save the meta when the post is saved.
        *
        * @param int $post_id The ID of the post being saved.
        */
        public function save( $post_id ) {
           /*
            * We need to verify this came from the our screen and with proper authorization,
            * because save_post can be triggered at other times.
            */
           
           // Check if our nonce is set.
           if ( ! isset( $_POST[$this->pluginName.'_nonce'] ) )
               return $post_id;
   
           $nonce = $_POST[$this->pluginName.'_nonce'];
   
           // Verify that the nonce is valid.
           if ( ! wp_verify_nonce( $nonce, $this->pluginName ) )
               return $post_id;
   
           // If this is an autosave, our form has not been submitted,
                   //     so we don't want to do anything.
           if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
               return $post_id;
   
           // Check the user's permissions.
           if ( ! current_user_can( 'edit_page', $post_id ) )
                   return $post_id;
           if ( ! current_user_can( 'edit_post', $post_id ) )
                   return $post_id;
   
           /* OK, its safe for us to save the data now. */
   
           // Sanitize the user input.
           $rotatorId = sanitize_text_field( $_POST[$this->pluginName.'_rotatorId'] );
           $thumbUrl = sanitize_text_field( $_POST[$this->pluginName.'_thumbUrl'] );
           $actionUrl = sanitize_text_field( $_POST[$this->pluginName.'_actionUrl'] );
           if(strlen($actionUrl)<1){
             $actionUrl = get_permalink( $post_id );
           }
   
           // Update the meta field.
           update_post_meta( $post_id, $this->pluginName.'_rotatorId', $rotatorId );
           update_post_meta( $post_id, $this->pluginName.'_thumbUrl', $thumbUrl );
           update_post_meta( $post_id, $this->pluginName.'_actionUrl', $actionUrl );
        }
        
        function get_post_meta_all($id){
            global $wpdb;
            $data   =   array();
            $wpdb->query("
                SELECT post_id, meta_key, meta_value
                FROM ".$wpdb->postmeta."
                WHERE meta_key LIKE '".$this->pluginName."_%'
                AND post_id IN(select post_id from ".$wpdb->postmeta." WHERE meta_key = '".$this->pluginName."_rotatorId'
                                                                        AND meta_value = '$id')
            ");
            foreach($wpdb->last_result as $k => $v){
                    $key = $v->meta_key;
                if(!$data[$v->post_id] instanceof stdClass){
                    $object = new stdClass();
                    $object->$key = $v->meta_value;
                    $object->post_id = $v->post_id;
                    $data[$v->post_id] =   $object;
                }
                else{
                    $data[$v->post_id]->$key = $v->meta_value;
                }
                
            }; 
            return json_encode($data);
        }
        
        function bRadRotatorGenerate( $atts ) {
            global $wpdb;
            $x=0;
            $width = 100/$this->itemsVisible;
            $return="<script type='text/javascript'>(function($) {
                        $(document).ready(function(){
                            $('div.".$this->pluginName."_left').click(function(){
                                $('div.".$this->pluginName."_items').children(':last').prependTo('div.".$this->pluginName."_items');
                            });
                            $('div.".$this->pluginName."_right').click(function(){
                                $('div.".$this->pluginName."_items').children(':first').appendTo('div.".$this->pluginName."_items');
                            });
                            var height=$('div.".$this->pluginName."').height()-2;
                            var width=$('div.".$this->pluginName."_viewPane').width()/".$this->itemsVisible.";
                            var margin=($('div.".$this->pluginName."_viewPane').width()%".$this->itemsVisible.")/".($this->itemsVisible ).";
                            if(margin<5){
                                width=width-7;
                                margin=5;
                            }
                            var padding=margin%".$this->itemsVisible.";
                            $('div.".$this->pluginName."_item').css({'width':width,'margin-right':margin});
                            $('a.".$this->pluginName."_anchor').css({'height':height});
                            $('div.".$this->pluginName."_viewPane').css('padding-right',padding);
                            $('span.".$this->pluginName."_title').each(function(){
                                //$(this).css({'top':'-'+($(this).height())+'px'});
                            });
                        });
                    })(jQuery);
                    </script>";
            $thumbKey = $this->pluginName."_thumbUrl";
            $actionKey = $this->pluginName."_actionUrl";
            if(!json_decode($atts)){
              extract( shortcode_atts( array(
                  'json' => array(),
                  'ajaxUrl' => null,
                  'id' => null,
              ), $atts ) );
            }
            else{
                $objects = $atts;
            }
            if($json){
                $objects = $json;
            }
            if($ajaxUrl){
                $objects = file_get_contents($ajaxUrl);
            }
            if($id){
                $objects = $this->get_post_meta_all($id);
            }
            if($total = count($objects)){
                $return .= "<div class='".$this->pluginName."'><div class='".$this->pluginName."_left' style=\"background-image:url('".$this->leftArrow."');\"></div>
                        <div class='".$this->pluginName."_viewPane'><div class='".$this->pluginName."_items' style='width:100%;'>";
            }
            foreach(json_decode($objects) as $object){
                $x++;
                
                if(isset($object->thumbUrl)&&isset($object->actionUrl)){
                    $return .="<div class='".$this->pluginName."_item' ><a class='".$this->pluginName."_anchor' href='". $object->actionUrl . "' style=\"border-color: ".$this->skinColour.";background-image: url('". $object->thumbUrl ."');width:98%;height:100%;background-size:100% 100%;\">&nbsp;</a></div>";
                }
                elseif(isset($object->$thumbKey)&&isset($object->$actionKey)){
                    $return .="<div class='".$this->pluginName."_item' ><a class='".$this->pluginName."_anchor' href='". $object->$actionKey . "' style=\"border-color: ".$this->skinColour.";background-image: url('".$object->$thumbKey. "');width:98%;height:100%;background-size:100% 100%;\">&nbsp;</a>
                                <span class='".$this->pluginName."_title' style='color:".$this->skinColour.";'> ".get_the_title($object->post_id)."</span></div>";
                }                    
            }
            $return.="</div></div><div class='".$this->pluginName."_right' style=\"background-image:url('".$this->rightArrow."');\"></div></div>";

            return str_ireplace("~PLACEHOLDER~", (($x*250)>1000 ? ($x*250) : 1000), $return);
        }
    
        public function admin_init()
        {
           // add_meta_box( 'bRad_Rotator', _('Rotator Attributes', 'rotator_atts'), array('b_rad_rotator', 'setupMetas'), 'post', 'normal' );
           // add_meta_box( 'bRad_Rotator', _('Rotator Attributes', 'rotator_atts'), array('b_rad_rotator', 'setupMetas'), 'page', 'normal' );
        }
          
        /**
        * Adds the meta box container.
        */
        public function add_meta_box( $post_type ) {
               $post_types = array('post', 'page');     //limit meta box to certain post types
               //if ( in_array( $post_type, $post_types )) {
           add_meta_box($this->pluginName, 'B Rad Rotator Attributes', array( $this, 'setupMetas' ),  $post_type,  'normal'  );
              // }
        }
          
        function setupMetas(){
            global $post; 
            // Add an nonce field so we can check for it later.
            wp_nonce_field( $this->pluginName, $this->pluginName.'_nonce' );
    
            // Use get_post_meta to retrieve an existing values from the database.
            $rotatorId = get_post_meta( $post->ID, $this->pluginName.'_rotatorId', true );
            $thumbUrl = get_post_meta( $post->ID, $this->pluginName.'_thumbUrl', true );
            $actionUrl = get_post_meta( $post->ID, $this->pluginName.'_actionUrl', true );
    
            // Display the form, using the current values.
            echo '<label for="b_rad_rotator_rotatorId">';
            echo 'Rotator Id';
            echo '</label> <br />';
            echo '<input type="text" id="b_rad_rotator_rotatorId" name="b_rad_rotator_rotatorId"';
            echo ' value="' . esc_attr( $rotatorId ) . '" style="position:relative;width:90%;" />';
            echo '<br />';
            echo '<label for="b_rad_rotator_thumbUrl">';
            echo 'Thumbnail Url';
            echo '</label> <br />';
            echo '<input type="text" id="b_rad_rotator_thumbUrl" name="b_rad_rotator_thumbUrl"';
            echo ' value="' . esc_attr( $thumbUrl ) . '" style="position:relative;width:90%;" />';            echo '<br />';
            echo '<label for="b_rad_rotator_actionUrl">';
            echo 'Action Url (Optional: <i>default is this post\'s URL</i>)';
            echo '</label> <br />';
            echo '<input type="text" id="b_rad_rotator_actionUrl" name="b_rad_rotator_actionUrl"';
            echo ' value="' . esc_attr( $actionUrl ) . '" style="position:relative;width:90%;" />';
          }
            
    }



    $b_rad_rotator = new b_rad_rotator();
   // add_action( 'admin_menu',  array($b_rad_rotator, 'b_rad_rotator_menu') );
   
    add_option($b_rad_rotator->pluginName."_skin_colour", "#297535" );
    add_option($b_rad_rotator->pluginName."_items_visible", "5" );
    add_option($b_rad_rotator->pluginName."_left_arrow", plugins_url('img\green_left.png', __FILE__) );
    add_option($b_rad_rotator->pluginName."_right_arrow", plugins_url('img\green_right.png', __FILE__) );
    add_shortcode( 'bRadRotator',  array($b_rad_rotator, 'bRadRotatorGenerate') );

?>