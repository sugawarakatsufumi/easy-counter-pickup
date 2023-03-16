<?php
/*
Plugin Name: ka2 Easy Counter & Pick up
Description: 簡単なアクセスカウンター(tax&singularに対応)と記事にピックアップフラグを立てる、アンテナサイトなどに最適。カスタムフィールドにインサート、カウンターはデフォルト1ヶ月でリセットされます
Author: 菅原勝文
Version: 1.0
*/
class ka2EasyCounter {

  public $accessCounterName = 'ka2AccessCounter';
  public $accessCounterDate = 'ka2FirstAccessDate';
  public $removesecond = 2592000;

  public function __construct(){
    //add_filter( 'the_content', array($this,'debug'), 10, 1);
    add_filter( 'template_redirect', array($this,'access_counter'), 10, 1);
    add_action('rest_api_init', array($this, 'api_add_fields'));
  }
  public function debug($str){
    global $post;
    if(is_singular()){
      $key_1_value = get_post_meta( $post->ID, $this->accessCounterName, true );
      $key_2_value = get_post_meta( $post->ID, $this->accessCounterDate, true );
      return "アクセス数".$key_1_value." 初回アクセス".$key_2_value.$str;
    }
  }
  public function api_add_fields(){
    register_rest_field(array('post','pages','category','tag'),
      $this->accessCounterName,
      array(
        'get_callback' => function($post, $name){
          $counter = '';
          if(preg_match('/posts|pages/', $_SERVER['REQUEST_URI'])){
            $counter = get_post_meta($post['id'], $name, true);
          }elseif(preg_match('/categories|tags/', $_SERVER['REQUEST_URI'])){
            $counter = get_term_meta($post['id'], $name, true);
          }
          if($counter){
            return $counter;
          }else{
            return "0";
          }
        },
        'update_callback' => null,
        'schema' => null,
      )
    );
  }
  public function access_counter(){
    global $post;
    $removesecond = $this->removesecond;
    if(is_singular()){
      $accessCount = get_post_meta( $post->ID, $this->accessCounterName, true );
      $firstAccess = get_post_meta( $post->ID, $this->accessCounterDate, true );
      if(!$firstAccess){
        update_post_meta($post->ID, $this->accessCounterDate, date('Y-m-d H:i:s'));
      }
      if( strtotime($firstAccess)+$removesecond < strtotime(date('Y-m-d H:i:s')) ){
        $accessCount = 1;
        update_post_meta($post->ID, $this->accessCounterDate, date('Y-m-d H:i:s'));
      }else{
        if($accessCount){
          $accessCount = $accessCount+1;
        }else{
          $accessCount = 1;
        }
      }
      update_post_meta( $post->ID, $this->accessCounterName, $accessCount);
    }elseif(is_tax() || is_category() || is_tag()){
      $term_id = get_queried_object_id();
      $accessCount = get_term_meta( $term_id, $this->accessCounterName, true );
      $firstAccess = get_term_meta( $term_id, $this->accessCounterDate, true );
      if(!$firstAccess){
        update_term_meta($term_id, $this->accessCounterDate, date('Y-m-d H:i:s'));
      }
      if( strtotime($firstAccess)+$removesecond < strtotime(date('Y-m-d H:i:s')) ){
        $accessCount = 1;
        update_term_meta($term_id, $this->accessCounterDate, date('Y-m-d H:i:s'));
      }else{
        if($accessCount){
          $accessCount = $accessCount+1;
        }else{
          $accessCount = 1;
        }
      }
      update_term_meta( $term_id, $this->accessCounterName, $accessCount);
    }
  }
}
include 'pickup.php';
$ka2Pickup = new ka2Pickup();
$ka2EasyCounter = new ka2EasyCounter();
