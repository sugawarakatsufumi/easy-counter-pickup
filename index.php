<?php
/*
Plugin Name: ka2 Easy Counter & Pick up
Description: 簡単なアクセスカウンター(tax&singularに対応)と記事にピックアップフラグを立てる、アンテナサイトなどに最適
Author: 菅原勝文
Version: 1.0
*/
class easy_counter_pickup_plugin {
  public function __construct(){
    add_filter( 'the_content', array($this,'debug'), 10, 1);
    add_filter( 'template_redirect', array($this,'accessCounter'), 10, 1);
    add_action('rest_api_init', array($this, 'api_add_fields'));
    add_action('rest_api_init', array($this, 'api_add_tax_fields'));
  }
  public function debug($str){
    global $post;
    if(is_singular()){
      $key_1_value = get_post_meta( $post->ID, 'ka2AccessCounter', true );
      $key_2_value = get_post_meta( $post->ID, 'ka2FirstAccessDate', true );
      return "アクセス数".$key_1_value." 初回アクセス".$key_2_value.$str;
    }
  }
  function api_add_tax_fields(){
    register_rest_field('categories',
      'ka2AccessCounter',
       array(
        'get_callback' => function($post, $name){
          $counter = get_term_meta($post['id'], 'ka2AccessCounter', true);
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
  function api_add_fields(){
    register_rest_field('post',
      'ka2AccessCounter',
       array(
        'get_callback' => function($post, $name){
          $counter = get_post_meta($post['id'], 'ka2AccessCounter', true);
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
  public function accessCounter(){
    global $post;
    $removesecond = 2592000;
    if(is_singular()){
      $accessCount = get_post_meta( $post->ID, 'ka2AccessCounter', true );
      $firstAccess = get_post_meta( $post->ID, 'ka2FirstAccessDate', true );
      if(!$firstAccess){
        update_post_meta($post->ID, 'ka2FirstAccessDate', date('Y-m-d H:i:s'));
      }
      if( strtotime($firstAccess)+$removesecond < strtotime(date('Y-m-d H:i:s')) ){
        $accessCount = 1;
        update_post_meta($post->ID, 'ka2FirstAccessDate', date('Y-m-d H:i:s'));
      }else{
        if($accessCount){
          $accessCount = $accessCount+1;
        }else{
          $accessCount = 1;
        }
      }
      update_post_meta( $post->ID, 'ka2AccessCounter', $accessCount);
    }elseif(is_tax() || is_category() || is_tag()){
      $term_id = get_queried_object_id();
      $accessCount = get_term_meta( $term_id, 'ka2AccessCounter', true );
      $firstAccess = get_term_meta( $term_id, 'ka2FirstAccessDate', true );
      if(!$firstAccess){
        update_term_meta($term_id, 'ka2FirstAccessDate', date('Y-m-d H:i:s'));
      }
      if( strtotime($firstAccess)+$removesecond < strtotime(date('Y-m-d H:i:s')) ){
        $accessCount = 1;
        update_term_meta($term_id, 'ka2FirstAccessDate', date('Y-m-d H:i:s'));
      }else{
        if($accessCount){
          $accessCount = $accessCount+1;
        }else{
          $accessCount = 1;
        }
      }
      update_term_meta( $term_id, 'ka2AccessCounter', $accessCount);
    }
  }
}

$easy_counter_pickup_plugin = new easy_counter_pickup_plugin();
