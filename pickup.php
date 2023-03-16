<?php

class ka2Pickup {

  public $ka2PickupName = 'ka2PickupFlag';
  public $nonceCsrfAction = 'ka2Pickup-action';
  public $nonceCsrfField = 'ka2Pickup-field';
  public $ka2pickupLabel = array('ポータルに表示する');
  public $ka2pickupTitle = 'ポータルサイト表示';

  public function __construct(){
    add_action('admin_menu', array($this, 'add_ka2Pickup_meta_box'));
    add_action('save_post', array($this, 'save_pickup_fields'));
    add_action('rest_api_init', array($this, 'api_add_fields'));
  }

  public function add_ka2Pickup_meta_box() {
    add_meta_box( 'Id_ka2Pickup', $this->ka2pickupTitle , array($this,'pickup_fields'), 'post', 'advanced');
  }

  public function pickup_fields(){
    global $post;
    $get_tools = get_post_meta( $post->ID, $this->ka2PickupName, true );
    $tools = $get_tools ? $get_tools : array();
    $data = $this->ka2pickupLabel;
    wp_nonce_field($this->nonceCsrfAction, $this->nonceCsrfField);
    foreach ( $data as $d ) {
      if ( in_array($d, $tools) ) {
        $check = 'checked'; 
      }else {
         $check = '';
      }
      echo '<label><input type="checkbox" name="'.$this->ka2PickupName.'[]" value="' . esc_attr($d) . '" ' . $check . '>' . esc_html($d) . '</label><br>';
    }
  }

  public function save_pickup_fields($post_id) {
    echo '保存成功';
    if ( isset($_POST[$this->nonceCsrfField]) && $_POST[$this->nonceCsrfField] ) {
      if ( check_admin_referer($this->nonceCsrfAction, $this->nonceCsrfField) ) {
        if ( isset($_POST[$this->ka2PickupName]) && $_POST[$this->ka2PickupName] ) {
          update_post_meta( $post_id, $this->ka2PickupName, $_POST[$this->ka2PickupName] );
        } else {
          delete_post_meta( $post_id, $this->ka2PickupName, get_post_meta($post_id, $this->ka2PickupName, true) );
        }
      }
    }
  }
  //APIにアサイン
  public function api_add_fields(){
    register_rest_field(array('post'),
      $this->ka2PickupName,
      array(
        'get_callback' => function($post, $name){
          $counter = '';
          if(preg_match('/posts/', $_SERVER['REQUEST_URI'])){
            $counter = get_post_meta($post['id'], $name, true);
            $counter = $counter? $counter: array();
            if( in_array($this->ka2pickupLabel[0], $counter) ){
              return '1';
            }else{
              return '0';
            }
          }else{
            return "0";
          }
        },
        'update_callback' => null,
        'schema' => null,
      )
    );
  }

}