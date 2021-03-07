<?php
/*
Template Name: historical
*/
$args = array(
    'post_type' => 'historical',
    'posts_per_page' => 5,
    'post_status' => 'publish'
);


$historical = new WP_Query($args);

get_header();
?>
<div id="main-historical">
<h1>Historical</h1>
<div class="circle-cont hidden"><div class="circle"></div></div>
<?php
if($historical->posts):?>


<div class="arrows-dist">
<div class="arrow arrow-left"><i class="fa fa-chevron-left"></i></div>
<div class="arrow arrow-right"><i class="fa fa-chevron-right"></i></div>
</div>
<?php
else:?>
<div>
No hay datos del clima
</div>
    <?php
endif;
?>
<div id="errores-ajax" class="hidden">
No hay resultados <span id="cst-error">anteriores</span>
</div>
<div class="historical-wrapper">
<?php
foreach($historical->posts as $weather):?>
<div class="historical-item">
<h2>
<?php if($weather->name){
     echo $weather->name;
} else{
    echo 'Name';
}?>
</h2>
<p>Description: <?php echo $weather->description; ?></p>
<p>Temp: <?php echo $weather->temp; ?></p>
<p>Temp min: <?php echo $weather->temp_min; ?></p>
<p>Temp max: <?php echo $weather->temp_max; ?></p>
<p>humidity: <?php echo $weather->humidity; ?></p>
</div>
<?php
endforeach;
?>
</div>
</div>
<?php
get_footer();
