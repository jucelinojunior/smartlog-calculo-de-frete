<?php
defined('ABSPATH') or die('Erro Interno');

# Função Base para Cálculo
function smartlog_shipping_method() {
   if ( ! class_exists( 'Smartlog_Shipping_Method' ) ) {
      class Smartlog_Shipping_Method extends WC_Shipping_Method {
         # Criação do método de entrega
         public function __construct() {

         $this->id                  = 'smartlog_frete'; 
         $this->method_title        = __( 'Gollog', 'smartlog_frete' );  
         $this->method_description  = __( 'Cálculo de frete da empresa Smartlog', 'smartlog_frete' ); 
         $this->availability        = 'including';
         $this->countries           = array('BR');

         $this->init();

         $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'no';
         $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Gollog', 'smartlog_frete' );
         }

         # Carregar e inserir dados da API no Woo
         function init() {
            $this->init_form_fields(); 
            $this->init_settings(); 
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
         }

         # Formulário de configuração do plugin
         function init_form_fields() { 
            $this->form_fields = array(
               'enabled' => array(
                  'title' => __( 'Ativar', 'smartlog_frete' ),
                  'type' => 'checkbox',
                  'description' => __( 'Selecione para habilitar a integração', 'smartlog_frete' ),
                  'default' => 'no'
               ),

               'cnpj' => array(
                  'title' => __( 'CNPJ', 'smartlog_frete' ),
                  'type' => 'text',
                  'description' => __( 'O CNPJ da empresa cadastrada no Smartlog', 'smartlog_frete' ),
                  'placeholder' => __( '00.000.000/0000-00', 'smartlog_frete' )
               ),
                
               'unidade' => array(
                  'title' => __( 'Unidade', 'smartlog_frete' ),
                  'type' => 'text',
                  'description' => __( 'A Unidade da Gollog', 'smartlog_frete' ),
                  'placeholder' => __( 'XXX', 'smartlog_frete' )
               ),

               'token' => array(
                  'title' => __( 'Token', 'smartlog_frete' ),
                  'type' => 'text',
                  'description' => __( 'O Token de acesso a API', 'smartlog_frete' ),
                  'placeholder' => __( 'XXXXXXXXXXXXXXXXXXX', 'smartlog_frete' )
               ),

               'origem' => array(
                  'title' => __( 'CEP de Origem', 'smartlog_frete' ),
                  'type' => 'text',
                  'description' => __( 'O CEP da empresa', 'smartlog_frete' ),
                  'placeholder' => __( '00000-000', 'smartlog_frete' )
               )
            );
         }

         # Função para cálculo do frete
         public function calculate_shipping( $package = array() ) {
            
            global $woocommerce;

            # Confirma se tem um cep cadastrado pelo cliente
            if($package[ 'destination' ][ 'postcode' ]){

               $Smartlog_Shipping_Method = new Smartlog_Shipping_Method();
               
               # Dados da Empresa ( baseado na configuração do plugin )
               $tk = $Smartlog_Shipping_Method->settings['token'];
               $empresa = $Smartlog_Shipping_Method->settings['cnpj'];
                  $empresa = preg_replace("/[^0-9]/", "", $empresa);
               $empresa = str_pad($empresa, 14, '0', STR_PAD_LEFT);
                  $origem = $Smartlog_Shipping_Method->settings['origem'];  
               $origem = preg_replace("/[^0-9]/", "", $origem);
                  $origem = str_pad($origem, 8, '0', STR_PAD_LEFT);
                  
               $unidade = strtoupper($Smartlog_Shipping_Method->settings['unidade']); 
               $unidade = substr($unidade,0,3); 
               # Dados do Cliente   
               $destino = $package[ 'destination' ][ 'postcode' ];
                  $destino = preg_replace("/[^0-9]/", "", $destino);
                  $destino = str_pad($destino, 8, '0', STR_PAD_LEFT);
               $peso = $woocommerce->cart->cart_contents_weight;
               if(get_option('woocommerce_weight_unit') == "g"){
                  $peso = $peso / 1000;
               }
               $peso = number_format($peso, 0);
               $valor = $woocommerce->cart->cart_contents_total;
                  $valor = number_format($valor, 0);

               # Dados da API
               $server = "http://api.sistemasmartlog.com.br/WooCommerce.php?token=$tk&unid=$unidade&cnpj=$empresa&cep_origem=$origem&cep_destino=$destino&peso=$peso&valor=$valor";

               # Início da API
               $curl = curl_init();   
                  curl_setopt($curl, CURLOPT_URL, $server);
                  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
               $result = curl_exec($curl);
               curl_close($curl);

               $result = json_decode($result, true);

               if($result['success']){
                   $cost = (float)$result['dados'];
                  $rate = array(
                     'id' => $this->id,
                     'label' => $result['message'].$result['prazo'],
                     'cost' => $cost
                  );
                  $this->add_rate( $rate );
               }
            }       
         }
      }
   }
}
add_action( 'woocommerce_shipping_init', 'smartlog_shipping_method' );

# Função para adicionar a opção no carrinho
function add_smartlog_frete_method( $methods ) {
   $methods[] = 'Smartlog_Shipping_Method';
   return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'add_smartlog_frete_method' );
