jQuery(document).ready(function ($) {
   // Front
   $("#calc_shipping_postcode, #billing_postcode, #shipping_postcode").mask("00000-000", {clearIfNotMatch: true, placeholder: "00000-000"});
   
   $( document ).ajaxComplete(function() {
      $("#calc_shipping_postcode, #billing_postcode, #shipping_postcode").mask("00000-000", {clearIfNotMatch: true, placeholder: "00000-000"});
   });

   // Admin
   $("#woocommerce_smartlog_frete_cnpj").mask("00.000.000/0000-00", {clearIfNotMatch: true, placeholder: "00.000.000/0000-00" });
   $("#woocommerce_smartlog_frete_origem").mask("00000-000", {clearIfNotMatch: true, placeholder: "00000-000" });
});
