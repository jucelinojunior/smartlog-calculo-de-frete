<?php
/**
 * Plugin Name: Cálculo de frete Smartlog
 * Plugin URI: https://smartlog.tec.br
 * Description: Este plugin realiza a integração entre o sistema Smartlog e o Woocommerce. Para que ele funciona é necessário que o cliente tenha uma conta na unidade.
 * Version: 1.0
 * Author: Jucelino Junior
 **/

  # Integração
  if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    include_once("integration-smartlog.php");
    register_activation_hook( __FILE__, 'smartlog_frete_activation_hook' );
    function smartlog_frete_activation_hook() {
        set_transient( 'smartlog-frete-alert-success', true, 5 );
    }
  } else {
    register_activation_hook( __FILE__, 'smartlog_frete_activation_hook' );
    function smartlog_frete_activation_hook() {
        set_transient( 'smartlog-frete-alert-error', true, 5 );
    }
    add_action( 'admin_init', 'deactivate_smartlog' );
    function deactivate_smartlog(){
      deactivate_plugins(__FILE__);
    }
  }

  # JS ( Front )
  function add_js_front() {
    wp_enqueue_script( 'smartlog_frete', plugin_dir_url(__FILE__) . 'scripts.js', array('jquery') );
    wp_enqueue_script( 'smartlog_frete-mask', plugin_dir_url(__FILE__) . 'jquery.mask.min.js', array('jquery') );
  }
  add_action( 'wp_enqueue_scripts', 'add_js_front' );

  # JS ( Admin )
  function add_js_admin(){
    global $pagenow;
      if ($pagenow != 'admin.php') {
        return;
      }
      wp_enqueue_script( 'smartlog_frete-admin' , plugin_dir_url(__FILE__) .'/scripts.js', array('jquery'));
      wp_enqueue_script( 'smartlog_frete-mask' , plugin_dir_url(__FILE__) .'/jquery.mask.min.js', array('jquery'));
  }
  add_action( 'admin_enqueue_scripts', 'add_js_admin' );

  # Alerta
  function smartlog_frete_notice(){
    if( get_transient( 'smartlog-frete-alert-success' ) ){
      $url = get_admin_url( );
      echo "
        <div class='updated notice is-dismissible'>
          <p><strong>Falta pouco para sua integração funcionar!</strong></p>
          <p>Agora você precisa informar os dados da sua empresa e token para usar a API Smartlog!</p>
          <p><a href='{$url}admin.php?page=wc-settings&tab=shipping&section=smartlog_frete'>Clique aqui para preencher as configurações necessárias</a></p>
        </div>
      ";
      delete_transient( 'smartlog-frete-alert-success' );
    } elseif ( get_transient( 'smartlog-frete-alert-error' ) ) {
      echo "
        <div class='error notice is-dismissible'>
          <p><strong>Ops... Ainda não podemos fazer a integração!</strong>.</p>
          <p>Antes de ativar o plugin de integração da Smartlog, você precisa instalar o WooCommerce em seu site.</p>
        </div>
      ";
      delete_transient( 'smartlog-frete-alert-error' );
    }
  }
  add_action( 'admin_notices', 'smartlog_frete_notice' );
