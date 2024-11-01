<?php
/**
 * @package FitnessTracker 
 * @version 1.1
 */
/*
Plugin Name: Fitness Tracker
Plugin URI: http://www.jimburnett.net
Description: Fitness Tracker is a Wordpress blogging plugin which lets you track your calories, weight, running, walking, swimming and biking distances and durations. You can place sidebar widgets showing your avg for the day, week, month and year. 
Author: Jim Burnett
Version: 1.1
Author URI: http://www.jimburnett.net
*/


//run cr_install() when plugin is activated. This basically creates the database table.
register_activation_hook(__FILE__,'cr_install');
add_action('admin_menu', 'cr_create_menu');

add_action("widgets_init", array('fitnessWeeklyStats', 'register'));
add_action("widgets_init", array('fitnessMonthlyStats', 'register'));




$com = $_REQUEST['com'];

if ($com == "additem")
{
   $cr_table_name = $wpdb->prefix . "cr_tracker";  
   $cr_type = $_REQUEST['cr_type'];
   $cr_value =$_REQUEST['cr_value'];
   $rows_affected = $wpdb->insert( $cr_table_name, array( 'name' => $cr_type, 'value' => $cr_value ) );
   
}

if ($com == "removeitem")
{
 $cr_table_name = $wpdb->prefix . "cr_tracker";  
 $cr_id = $_REQUEST['cr_id'];
 $sql = "DELETE FROM " . $cr_table_name . " where id='" . $cr_id . "'";
 $wpdb->query($sql);
 
}

//function cr_init()
//{
//  register_sidebar_widget(__('Fitness Tracker Weekly'), 'widget_crWeeklyStats');
//}



function cr_getItems()
{
    global $wpdb;
 $table_name = $wpdb->prefix . "cr_tracker";     
 $items = $wpdb->get_results("SELECT id,time,name,value FROM " . $table_name  . " ORDER BY time DESC LIMIT 20");
 $out = "";
  foreach ($items as $item)
  { 
       $removefrm = '<form action="admin.php?page=fitnesstracker/fitnesstracker.php" method="post">
       <input type="hidden" name="cr_id" value="' . $item->id . '" /><input type="hidden" name="com" value="removeitem" />
       <input type="submit" class="button-primary" value="Remove" /></form>';
                                                                             
       $out .= '<tr><td>' . $item->time . '</td><td>' . $item->name . '</td><td> ' .   $item->value . '</td><td> ' . $removefrm . '</td></tr>';
  }
  return $out;
}
function cr_create_menu() {

	//create new top-level menu
	add_menu_page('CalRunner Plugin Settings', 'Fitness Tracker', 'administrator', __FILE__, 'cr_settings_page','');

	//call register settings function
	//add_action( 'admin_init', 'register_mysettings' );
}

function cr_settings_page() {
?>
<div class="wrap">
<h2>FitnessTrack</h2>
<a target = "_new" href="http://www.goingfitness.com/">by goingFitness.com</a><br />

<form method="post" action="admin.php?page=fitnesstracker/fitnesstracker.php">
    <?php //settings_fields( 'baw-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Add some data!</th>
        <td>
        <select name="cr_type">
        <option value="Calories">Calories</option>
        <option value="Run">Run</option>
        <option value="Bike">Bike</option>
        <option value="Swim">Swim</option>
        <option value="Walk">Walk</option>
        <option value="Workout">Workout</option>
        <option value="Weight">Weight</option>
        </select>
        <input type="text" name="cr_value" value="" />
        <input type="submit" class="button-primary" value="<?php _e('Add') ?>" />
        <input type="hidden" name="com" value="additem" />
        </td>
        </tr>
    </table>
</form>

<table>
<tr><td width="200"><strong>DateTime</strong></td><td width="90"><strong>Type</strong></td><td><strong>Value</strong></td><td></td></tr>
<?php echo cr_getItems(); ?>
</table>

</div>
<?php } 

function cr_install()
{
   global $wpdb;
   $table_name = $wpdb->prefix . "cr_tracker";
   
   
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time TIMESTAMP DEFAULT NOW(),
	  name tinytext NOT NULL,
	  value double(7,2) NOT NULL,
	  UNIQUE KEY id (id)
	 );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    //$welcome_name = "Mr. Wordpress";
    //$welcome_text = "Congratulations, you just completed the installation!";
    //$rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'name' => $welcome_name, 'text' => $welcome_text ) );
    add_option("cr_db_version", "1.0");
   }
}

class fitnessWeeklyStats extends WP_Widget {


 function fitnessWeeklyStats() {
// $widget_ops = array( 'classname' => 'fitnessWeeklyStats', 'description' => 'Shows your weekly fitness progress on the sidebar.' );
 //$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'fitnessWeeklyStats' );
 //$this->WP_Widget( 'fitnessWeeklyStats', 'Weekly Fitness Progress', $widget_ops, $control_ops );

 }

 function widget($args, $instance) {
       //Widget output
       global $wpdb;
 
 extract( $args );

 $table_name = $wpdb->prefix . "cr_tracker"; 
 //echo $sbargs['before_widget'];
 echo $before_widget;   
 echo $before_title . "<strong>Fitness Weekly Totals</strong>" .  $after_title;
 $sql = "SELECT WEEKOFYEAR(NOW()) as 'weekno'";
 $ret = $wpdb->get_row($sql);
 
 
 
$sql = "SELECT SUM(value) as 'val' FROM  " . $table_name . " WHERE name='Swim' AND WEEKOFYEAR(time)=" . $ret->weekno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Swim:</strong> " . $item['val'] . "<br />"; } 
 
 $sql = "SELECT SUM(value) as 'val' FROM  " . $table_name . " WHERE name='Bike' AND WEEKOFYEAR(time)=" . $ret->weekno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Bike:</strong> " . $item['val'] . "<br />"; } 
 
 $sql = "SELECT SUM(value) as 'val' FROM  " . $table_name . " WHERE name='Run' AND WEEKOFYEAR(time)=" . $ret->weekno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Run:</strong> " . $item['val'] . "<br />"; }

 
 $sql = "SELECT SUM(value) as 'val' FROM  " . $table_name . " WHERE name='Walk' AND WEEKOFYEAR(time)=" . $ret->weekno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Walk:</strong> " . $item['val'] . "<br />"; } 
 
 $sql = "SELECT SUM(value) as 'val' FROM  " . $table_name . " WHERE name='Calories' AND WEEKOFYEAR(time)=" . $ret->weekno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Calories:</strong> " . $item['val'] . "<br />"; } 
 
 
 $sql = "SELECT AVG(value) as 'val' FROM  " . $table_name . " WHERE name='Weight' AND WEEKOFYEAR(time)=" . $ret->weekno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Weight(avg):</strong> " . number_format($item['val'],2) . "<br />"; } 
 
  echo $after_widget;

 }

 function register(){
    register_sidebar_widget('Fitness Weekly Stats', array('fitnessWeeklyStats', 'widget'));
    register_widget_control('Fitness Weekly Stats', array('fitnessWeeklyStats', 'control'));
  }

 function update($new_instance, $old_instance) {
       //Save widget options
       $instance = $old_instance;
    return $new_instance;

 }

 function form($instance) {
       //Output admin widget options form
 }

 function prtWeeklyStat() {
 
}

}

class fitnessMonthlyStats extends WP_Widget {

 function fitnessMonthlyStats() {
// $widget_ops = array( 'classname' => 'fitnessWeeklyStats', 'description' => 'Shows your weekly fitness progress on the sidebar.' );
 //$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'fitnessWeeklyStats' );
 //$this->WP_Widget( 'fitnessWeeklyStats', 'Weekly Fitness Progress', $widget_ops, $control_ops );

 }

 function widget($args, $instance) {
       //Widget output
       global $wpdb;
 
 extract( $args );

 $table_name = $wpdb->prefix . "cr_tracker"; 
 //echo $sbargs['before_widget'];
 echo $before_widget;   
 echo $before_title . "<strong>Fitness Monthly Totals</strong>" .  $after_title;
 $sql = "SELECT MONTH(NOW()) as 'monthno'";
 $ret = $wpdb->get_row($sql);
 
 
 
$sql = "SELECT SUM(value) as 'val' FROM  " . $table_name . " WHERE name='Swim' AND MONTH(time)=" . $ret->monthno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Swim:</strong> " . $item['val'] . "<br />"; } 
 
 $sql = "SELECT SUM(value) as 'val' FROM  " . $table_name . " WHERE name='Bike' AND MONTH(time)=" . $ret->monthno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Bike:</strong> " . $item['val'] . "<br />"; } 
 
 $sql = "SELECT SUM(value) as 'val' FROM  " . $table_name . " WHERE name='Run' AND MONTH(time)=" . $ret->monthno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Run:</strong> " . $item['val'] . "<br />"; }

 
 $sql = "SELECT SUM(value) as 'val' FROM  " . $table_name . " WHERE name='Walk' AND MONTH(time)=" . $ret->monthno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Walk:</strong> " . $item['val'] . "<br />"; } 
 
 $sql = "SELECT SUM(value) as 'val' FROM  " . $table_name . " WHERE name='Calories' AND MONTH(time)=" . $ret->monthno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Calories:</strong> " . $item['val'] . "<br />"; } 
 
 
 $sql = "SELECT AVG(value) as 'val' FROM  " . $table_name . " WHERE name='Weight' AND MONTH(time)=" . $ret->monthno; //. $ret->weekno;
 $item = $wpdb->get_row($sql,ARRAY_A);
 if ( $item['val'] > 0 ) { echo "<strong>Weight(avg):</strong> " . number_format($item['val'],2) . "<br />"; } 
 
  echo $after_widget;

 }

 function register(){
    register_sidebar_widget('Fitness Monthly Stats', array('fitnessMonthlyStats', 'widget'));
    register_widget_control('Fitness Monthly Stats', array('fitnessMonthlyStats', 'control'));
  }

 function update($new_instance, $old_instance) {
       //Save widget options
       $instance = $old_instance;
    return $new_instance;

 }

 function form($instance) {
       //Output admin widget options form
 }

 function prtWeeklyStat() {
 
}

}
?>
